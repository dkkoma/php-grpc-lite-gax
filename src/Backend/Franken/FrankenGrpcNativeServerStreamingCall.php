<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend\Franken;

use FrankenGrpc\ServerStreamingCall as NativeServerStreamingCall;
use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\ServerStreamingCall;

/**
 * @internal
 */
final class FrankenGrpcNativeServerStreamingCall implements ServerStreamingCall
{
    private ?GrpcStatusCode $statusCode = null;

    private string $statusMessage = '';

    /** @var array<string, list<string>>|null */
    private ?array $trailingMetadata = null;

    public function __construct(
        private readonly NativeServerStreamingCall $call,
    ) {
    }

    /**
     * @return iterable<string>
     */
    #[\Override]
    public function responses(): iterable
    {
        while (($payload = $this->call->read()) !== null) {
            yield $payload;
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
        return $this->call->getInitialMetadata();
    }

    /**
     * @return array<string, list<string>>
     */
    #[\Override]
    public function trailingMetadata(): array
    {
        $this->readStatus();

        return $this->trailingMetadata ?? [];
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

        $status = $this->call->getStatus();
        $this->statusCode = GrpcStatusCode::tryFrom($status->code) ?? GrpcStatusCode::UNKNOWN;
        $this->statusMessage = $status->details;
        $this->trailingMetadata = $this->call->getTrailingMetadata() !== []
            ? $this->call->getTrailingMetadata()
            : $status->metadata;
    }
}
