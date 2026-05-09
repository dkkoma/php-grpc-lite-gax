<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Support;

use GrpcLiteGax\Backend\GrpcLite\GrpcLiteBridge;
use GrpcLiteGax\Backend\GrpcLite\GrpcLiteResponse;
use GrpcLiteGax\Backend\ServerStreamingCall;

final class FakeGrpcLiteBridge implements GrpcLiteBridge
{
    /** @var list<array{path: string, payload: string, metadata: array<string, list<string>>, timeoutSeconds: ?float}> */
    private array $calls = [];

    /** @var list<GrpcLiteResponse> */
    private array $responses = [];

    /** @var list<ServerStreamingCall> */
    private array $serverStreamingCalls = [];

    private bool $closed = false;

    private int $closeCallCount = 0;

    public function enqueueResponse(GrpcLiteResponse $response): void
    {
        $this->responses[] = $response;
    }

    public function enqueueServerStreamingCall(ServerStreamingCall $call): void
    {
        $this->serverStreamingCalls[] = $call;
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
        $this->calls[] = [
            'path' => $path,
            'payload' => $payload,
            'metadata' => $metadata,
            'timeoutSeconds' => $timeoutSeconds,
        ];

        if ($this->responses === []) {
            throw new \UnderflowException('FakeGrpcLiteBridge has no queued unary response.');
        }

        return array_shift($this->responses);
    }

    /**
     * @param array<string, list<string>> $metadata
     */
    #[\Override]
    public function serverStreamingCall(
        string $path,
        string $payload,
        array $metadata,
        ?float $timeoutSeconds,
    ): ServerStreamingCall {
        $this->calls[] = [
            'path' => $path,
            'payload' => $payload,
            'metadata' => $metadata,
            'timeoutSeconds' => $timeoutSeconds,
        ];

        if ($this->serverStreamingCalls === []) {
            throw new \UnderflowException('FakeGrpcLiteBridge has no queued server streaming call.');
        }

        return array_shift($this->serverStreamingCalls);
    }

    #[\Override]
    public function close(): void
    {
        $this->closeCallCount++;
        $this->closed = true;
    }

    /**
     * @return array{path: string, payload: string, metadata: array<string, list<string>>, timeoutSeconds: ?float}
     */
    public function lastCall(): array
    {
        if ($this->calls === []) {
            throw new \UnderflowException('FakeGrpcLiteBridge has not received a call.');
        }

        return $this->calls[array_key_last($this->calls)];
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function closeCallCount(): int
    {
        return $this->closeCallCount;
    }
}
