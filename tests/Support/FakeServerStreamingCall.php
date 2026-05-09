<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Support;

use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\ServerStreamingCall;

final class FakeServerStreamingCall implements ServerStreamingCall
{
    private bool $cancelled = false;

    /**
     * @param list<string> $responses
     * @param array<string, list<string>> $metadata
     * @param array<string, list<string>> $trailingMetadata
     */
    public function __construct(
        private readonly array $responses,
        private readonly GrpcStatusCode $statusCode = GrpcStatusCode::OK,
        private readonly string $statusMessage = '',
        private readonly array $metadata = [],
        private readonly array $trailingMetadata = [],
        private readonly string $peer = 'fake-peer',
    ) {
    }

    /**
     * @return iterable<string>
     */
    #[\Override]
    public function responses(): iterable
    {
        foreach ($this->responses as $response) {
            yield $response;
        }
    }

    #[\Override]
    public function statusCode(): GrpcStatusCode
    {
        return $this->cancelled ? GrpcStatusCode::CANCELLED : $this->statusCode;
    }

    #[\Override]
    public function statusMessage(): string
    {
        return $this->cancelled ? 'cancelled' : $this->statusMessage;
    }

    /**
     * @return array<string, list<string>>
     */
    #[\Override]
    public function metadata(): array
    {
        return $this->metadata;
    }

    /**
     * @return array<string, list<string>>
     */
    #[\Override]
    public function trailingMetadata(): array
    {
        return $this->trailingMetadata;
    }

    #[\Override]
    public function getPeer(): string
    {
        return $this->peer;
    }

    #[\Override]
    public function cancel(): void
    {
        $this->cancelled = true;
    }
}
