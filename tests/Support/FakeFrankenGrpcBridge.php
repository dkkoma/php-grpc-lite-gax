<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Support;

use GrpcLiteGax\Backend\Franken\FrankenGrpcBridge;
use GrpcLiteGax\Backend\Franken\FrankenGrpcResponse;
use GrpcLiteGax\Backend\ServerStreamingCall;

final class FakeFrankenGrpcBridge implements FrankenGrpcBridge
{
    /** @var list<array{path: string, payload: string, metadata: array<string, list<string>>, timeoutSeconds: ?float}> */
    private array $calls = [];

    /** @var list<FrankenGrpcResponse> */
    private array $responses = [];

    /** @var list<ServerStreamingCall> */
    private array $serverStreamingCalls = [];

    private bool $closed = false;

    private int $closeCallCount = 0;

    public function enqueueResponse(FrankenGrpcResponse $response): void
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
    ): FrankenGrpcResponse {
        $this->calls[] = [
            'path' => $path,
            'payload' => $payload,
            'metadata' => $metadata,
            'timeoutSeconds' => $timeoutSeconds,
        ];

        if ($this->responses === []) {
            throw new \UnderflowException('FakeFrankenGrpcBridge has no queued unary response.');
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
            throw new \UnderflowException('FakeFrankenGrpcBridge has no queued server streaming call.');
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
     * @return list<array{path: string, payload: string, metadata: array<string, list<string>>, timeoutSeconds: ?float}>
     */
    public function calls(): array
    {
        return $this->calls;
    }

    /**
     * @return array{path: string, payload: string, metadata: array<string, list<string>>, timeoutSeconds: ?float}
     */
    public function lastCall(): array
    {
        if ($this->calls === []) {
            throw new \UnderflowException('FakeFrankenGrpcBridge has not received a call.');
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
