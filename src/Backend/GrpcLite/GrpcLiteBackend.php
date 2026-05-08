<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend\GrpcLite;

use GrpcLiteGax\Backend\BackendClosedException;
use GrpcLiteGax\Backend\UnaryBackend;
use GrpcLiteGax\Backend\UnaryRequest;
use GrpcLiteGax\Backend\UnaryResponse;

/**
 * @internal
 */
final class GrpcLiteBackend implements UnaryBackend
{
    private bool $closed = false;

    public function __construct(
        private readonly GrpcLiteBridge $bridge,
    ) {
    }

    #[\Override]
    public function call(UnaryRequest $request): UnaryResponse
    {
        if ($this->closed) {
            throw new BackendClosedException();
        }

        $response = $this->bridge->unaryCall(
            path: $request->path(),
            payload: $request->payload,
            metadata: $request->metadata,
            timeoutSeconds: $request->timeoutSeconds,
        );

        return new UnaryResponse(
            payload: $response->payload,
            grpcStatusCode: $response->statusCode,
            statusMessage: $response->statusMessage,
            metadata: $response->metadata,
            trailingMetadata: $response->trailingMetadata,
        );
    }

    #[\Override]
    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->bridge->close();
        $this->closed = true;
    }
}
