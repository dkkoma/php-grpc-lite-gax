<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend\GrpcLite;

use Grpc\Call;
use Grpc\Channel;
use Grpc\Timeval;
use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\ServerStreamingCall;

/**
 * @internal
 */
final class GrpcLiteNativeServerStreamingCall implements ServerStreamingCall
{
    private Call $call;

    /** @var array<string, list<string>> */
    private array $metadata = [];

    /** @var array<string, list<string>> */
    private array $trailingMetadata = [];

    private ?GrpcStatusCode $statusCode = null;

    private string $statusMessage = '';

    /**
     * @param array<string, list<string>> $metadata
     */
    public function __construct(
        Channel $channel,
        string $path,
        string $payload,
        array $metadata,
        Timeval $deadline,
    ) {
        $this->call = new Call($channel, $path, $deadline);
        $this->call->startBatch([
            \Grpc\OP_SEND_INITIAL_METADATA => $metadata,
            \Grpc\OP_SEND_MESSAGE => ['message' => $payload],
            \Grpc\OP_SEND_CLOSE_FROM_CLIENT => true,
        ]);
    }

    /**
     * @return iterable<string>
     */
    #[\Override]
    public function responses(): iterable
    {
        $event = $this->call->startBatch([
            \Grpc\OP_RECV_INITIAL_METADATA => true,
            \Grpc\OP_RECV_MESSAGE => true,
        ]);
        $this->metadata = $this->metadataFrom($this->eventProperty($event, 'metadata'));

        $message = $this->eventProperty($event, 'message');
        while (is_string($message)) {
            yield $message;
            $event = $this->call->startBatch([\Grpc\OP_RECV_MESSAGE => true]);
            $message = $this->eventProperty($event, 'message');
        }
    }

    #[\Override]
    public function statusCode(): GrpcStatusCode
    {
        $this->readStatus();

        return $this->statusCode ?? GrpcStatusCode::UNKNOWN;
    }

    #[\Override]
    public function statusMessage(): string
    {
        $this->readStatus();

        return $this->statusMessage;
    }

    /**
     * @return array<string, list<string>>
     */
    #[\Override]
    public function metadata(): array
    {
        if ($this->metadata === []) {
            $event = $this->call->startBatch([\Grpc\OP_RECV_INITIAL_METADATA => true]);
            $this->metadata = $this->metadataFrom($this->eventProperty($event, 'metadata'));
        }

        return $this->metadata;
    }

    /**
     * @return array<string, list<string>>
     */
    #[\Override]
    public function trailingMetadata(): array
    {
        $this->readStatus();

        return $this->trailingMetadata;
    }

    #[\Override]
    public function getPeer(): string
    {
        return $this->call->getPeer();
    }

    #[\Override]
    public function cancel(): void
    {
        $this->call->cancel();
    }

    private function readStatus(): void
    {
        if ($this->statusCode !== null) {
            return;
        }

        $event = $this->call->startBatch([\Grpc\OP_RECV_STATUS_ON_CLIENT => true]);
        $status = $this->eventProperty($event, 'status');
        $code = $this->eventProperty($status, 'code');
        if (!is_int($code)) {
            throw new \RuntimeException('php-grpc-lite server streaming event is missing an integer gRPC status code.');
        }

        $this->statusCode = GrpcStatusCode::tryFrom($code) ?? GrpcStatusCode::UNKNOWN;
        $details = $this->eventProperty($status, 'details');
        $this->statusMessage = is_string($details) ? $details : '';
        $this->trailingMetadata = $this->metadataFrom($this->eventProperty($status, 'metadata'));
    }

    /**
     * @return array<string, list<string>>
     */
    private function metadataFrom(mixed $metadata): array
    {
        if (!is_array($metadata)) {
            return [];
        }

        $normalized = [];
        foreach ($metadata as $name => $values) {
            if (!is_string($name)) {
                continue;
            }

            if (is_string($values)) {
                $values = [$values];
            }

            if (!is_array($values) || !array_is_list($values)) {
                continue;
            }

            $strings = [];
            foreach ($values as $value) {
                if (is_string($value)) {
                    $strings[] = $value;
                }
            }

            if ($strings !== []) {
                $normalized[$name] = $strings;
            }
        }

        return $normalized;
    }

    private function eventProperty(mixed $event, string $property): mixed
    {
        if (!is_object($event) || !property_exists($event, $property)) {
            return null;
        }

        return $event->{$property};
    }
}
