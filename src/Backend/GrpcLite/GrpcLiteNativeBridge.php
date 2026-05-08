<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend\GrpcLite;

use Grpc\Channel;
use Grpc\ChannelCredentials;
use Grpc\Timeval;
use GrpcLiteGax\Backend\GrpcStatusCode;

/**
 * @internal
 */
final class GrpcLiteNativeBridge implements GrpcLiteBridge
{
    private const GRPC_CHANNEL_CLASS = 'Grpc\\Channel';
    private const GRPC_CHANNEL_CREDENTIALS_CLASS = 'Grpc\\ChannelCredentials';
    private const GRPC_CALL_CLASS = 'Grpc\\Call';
    private const GRPC_TIMEVAL_CLASS = 'Grpc\\Timeval';

    private Channel $channel;

    /**
     * @param array<string, mixed> $channelOptions
     */
    public function __construct(
        string $endpoint,
        array $channelOptions = [],
    ) {
        $this->assertNativeSurfaceAvailable();

        if (!array_key_exists('credentials', $channelOptions)) {
            $channelOptions['credentials'] = $this->defaultCredentials();
        }

        $channelClass = self::GRPC_CHANNEL_CLASS;
        $this->channel = new $channelClass($endpoint, $channelOptions);
    }

    /**
     * @param array<string, list<string>> $metadata
     */
    #[\Override]
    public function unaryCall(
        string $path,
        string $payload,
        array $metadata,
        ?float $timeoutSeconds,
    ): GrpcLiteResponse {
        $callClass = self::GRPC_CALL_CLASS;
        $call = new $callClass($this->channel, $path, $this->deadline($timeoutSeconds));

        $call->startBatch([
            $this->grpcConstant('OP_SEND_INITIAL_METADATA') => $metadata,
            $this->grpcConstant('OP_SEND_MESSAGE') => ['message' => $payload],
            $this->grpcConstant('OP_SEND_CLOSE_FROM_CLIENT') => true,
        ]);

        $event = $call->startBatch([
            $this->grpcConstant('OP_RECV_INITIAL_METADATA') => true,
            $this->grpcConstant('OP_RECV_MESSAGE') => true,
            $this->grpcConstant('OP_RECV_STATUS_ON_CLIENT') => true,
        ]);

        $status = $this->eventProperty($event, 'status');
        $code = $this->statusCode($status);

        return new GrpcLiteResponse(
            payload: $this->eventMessage($event),
            statusCode: $code,
            statusMessage: $this->statusMessage($status),
            metadata: $this->metadataFrom($this->eventProperty($event, 'metadata')),
            trailingMetadata: $this->metadataFrom($this->eventProperty($status, 'metadata')),
        );
    }

    #[\Override]
    public function close(): void
    {
        $this->channel->close();
    }

    private function assertNativeSurfaceAvailable(): void
    {
        foreach ([self::GRPC_CHANNEL_CLASS, self::GRPC_CALL_CLASS, self::GRPC_TIMEVAL_CLASS] as $className) {
            if (!class_exists($className)) {
                // @codeCoverageIgnoreStart
                throw new \RuntimeException(sprintf('%s is required for GrpcLiteNativeBridge.', $className));
                // @codeCoverageIgnoreEnd
            }
        }
    }

    private function defaultCredentials(): ChannelCredentials
    {
        $credentialsClass = self::GRPC_CHANNEL_CREDENTIALS_CLASS;
        if (!class_exists($credentialsClass)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Grpc\\ChannelCredentials::createSsl() is required.');
            // @codeCoverageIgnoreEnd
        }

        return $credentialsClass::createSsl();
    }

    private function deadline(?float $timeoutSeconds): Timeval
    {
        $timevalClass = self::GRPC_TIMEVAL_CLASS;

        if ($timeoutSeconds === null) {
            return $timevalClass::infFuture();
        }

        $relative = new $timevalClass((int) ceil($timeoutSeconds * 1_000_000));
        $now = $timevalClass::now();

        return $now->add($relative);
    }

    private function grpcConstant(string $name): int
    {
        $constantName = 'Grpc\\' . $name;
        if (!defined($constantName)) {
            throw new \RuntimeException(sprintf('%s is required for GrpcLiteNativeBridge.', $constantName));
        }

        $value = constant($constantName);
        if (!is_int($value)) {
            throw new \RuntimeException(sprintf('%s must be an integer.', $constantName));
        }

        return $value;
    }

    private function eventMessage(object $event): string
    {
        $message = $this->eventProperty($event, 'message');

        return is_string($message) ? $message : '';
    }

    private function statusCode(mixed $status): GrpcStatusCode
    {
        $code = $this->eventProperty($status, 'code');
        if (!is_int($code)) {
            throw new \RuntimeException('php-grpc-lite unary event is missing an integer gRPC status code.');
        }

        return GrpcStatusCode::tryFrom($code) ?? GrpcStatusCode::UNKNOWN;
    }

    private function statusMessage(mixed $status): string
    {
        $details = $this->eventProperty($status, 'details');

        return is_string($details) ? $details : '';
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
