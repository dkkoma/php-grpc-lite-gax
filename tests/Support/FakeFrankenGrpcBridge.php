<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Support;

use GrpcLiteGax\Backend\Franken\FrankenGrpcBridge;
use GrpcLiteGax\Backend\Franken\FrankenGrpcResponse;

final class FakeFrankenGrpcBridge implements FrankenGrpcBridge
{
    /** @var list<array{path: string, payload: string, metadata: array<string, list<string>>, timeoutSeconds: ?float}> */
    private array $calls = [];

    /** @var list<FrankenGrpcResponse> */
    private array $responses = [];

    private bool $closed = false;

    private int $closeCallCount = 0;

    public function enqueueResponse(FrankenGrpcResponse $response): void
    {
        $this->responses[] = $response;
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
