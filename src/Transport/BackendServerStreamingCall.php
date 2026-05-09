<?php

declare(strict_types=1);

namespace GrpcLiteGax\Transport;

use Google\ApiCore\ServerStreamingCallInterface;
use Google\ApiCore\ValidationException;
use Google\Protobuf\Internal\Message;
use GrpcLiteGax\Backend\ServerStreamingCall;

/**
 * @internal
 */
final class BackendServerStreamingCall implements ServerStreamingCallInterface
{
    /**
     * @param class-string<Message> $decodeType
     */
    public function __construct(
        private readonly ServerStreamingCall $call,
        private readonly string $decodeType,
    ) {
    }

    /**
     * @param mixed $data
     * @param array<mixed> $metadata
     * @param array<mixed> $options
     */
    #[\Override]
    public function start($data, array $metadata = [], array $options = []): void
    {
    }

    /**
     * @return iterable<Message>
     */
    #[\Override]
    public function responses(): iterable
    {
        foreach ($this->call->responses() as $payload) {
            $message = new $this->decodeType();
            $message->mergeFromString($payload);

            yield $message;
        }
    }

    #[\Override]
    public function getStatus(): \stdClass
    {
        return (object) [
            'code' => $this->call->statusCode()->value,
            'details' => $this->call->statusMessage(),
            'metadata' => $this->call->trailingMetadata(),
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    #[\Override]
    public function getMetadata(): array
    {
        return $this->call->metadata();
    }

    /**
     * @return array<string, list<string>>
     */
    #[\Override]
    public function getTrailingMetadata(): array
    {
        return $this->call->trailingMetadata();
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

    /**
     * @param mixed $call_credentials
     */
    #[\Override]
    public function setCallCredentials($call_credentials): void
    {
        throw new ValidationException('Call credentials must be provided through GAX call options.');
    }
}
