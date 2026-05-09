<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Support;

use GrpcLiteGax\Backend\BackendClosedException;
use GrpcLiteGax\Backend\ServerStreamingBackend;
use GrpcLiteGax\Backend\ServerStreamingCall;
use GrpcLiteGax\Backend\ServerStreamingRequest;
use GrpcLiteGax\Backend\UnaryBackend;
use GrpcLiteGax\Backend\UnaryRequest;
use GrpcLiteGax\Backend\UnaryResponse;

final class FakeBackend implements UnaryBackend, ServerStreamingBackend
{
    /** @var list<UnaryRequest> */
    private array $requests = [];

    /** @var list<UnaryResponse> */
    private array $responses = [];

    /** @var list<ServerStreamingRequest> */
    private array $serverStreamingRequests = [];

    /** @var list<ServerStreamingCall> */
    private array $serverStreamingCalls = [];

    private bool $closed = false;

    public function enqueueResponse(UnaryResponse $response): void
    {
        $this->responses[] = $response;
    }

    public function enqueueServerStreamingCall(ServerStreamingCall $call): void
    {
        $this->serverStreamingCalls[] = $call;
    }

    #[\Override]
    public function call(UnaryRequest $request): UnaryResponse
    {
        if ($this->closed) {
            throw new BackendClosedException();
        }

        $this->requests[] = $request;

        if ($this->responses === []) {
            throw new \UnderflowException('FakeBackend has no queued unary response.');
        }

        return array_shift($this->responses);
    }

    #[\Override]
    public function start(ServerStreamingRequest $request): ServerStreamingCall
    {
        if ($this->closed) {
            throw new BackendClosedException();
        }

        $this->serverStreamingRequests[] = $request;

        if ($this->serverStreamingCalls === []) {
            throw new \UnderflowException('FakeBackend has no queued server streaming call.');
        }

        return array_shift($this->serverStreamingCalls);
    }

    #[\Override]
    public function close(): void
    {
        $this->closed = true;
    }

    /**
     * @return list<UnaryRequest>
     */
    public function requests(): array
    {
        return $this->requests;
    }

    public function lastRequest(): UnaryRequest
    {
        if ($this->requests === []) {
            throw new \UnderflowException('FakeBackend has not received a request.');
        }

        return $this->requests[array_key_last($this->requests)];
    }

    public function lastServerStreamingRequest(): ServerStreamingRequest
    {
        if ($this->serverStreamingRequests === []) {
            throw new \UnderflowException('FakeBackend has not received a server streaming request.');
        }

        return $this->serverStreamingRequests[array_key_last($this->serverStreamingRequests)];
    }

    public function pendingResponseCount(): int
    {
        return count($this->responses);
    }

    public function assertNoPendingResponses(): void
    {
        if ($this->responses !== []) {
            throw new \RuntimeException('FakeBackend has pending unary responses.');
        }
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }
}
