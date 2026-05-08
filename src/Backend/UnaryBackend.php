<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend;

/**
 * @internal
 */
interface UnaryBackend
{
    /**
     * Executes one unary request.
     *
     * Implementations return non-OK gRPC outcomes as `UnaryResponse` values.
     * Transport failures without a gRPC status may throw; `AbstractGrpcTransport`
     * maps those failures to GAX `ApiException`.
     *
     * @throws BackendClosedException when called after close.
     */
    public function call(UnaryRequest $request): UnaryResponse;

    /**
     * Releases backend resources.
     *
     * Close is idempotent. Calls after close must fail with `BackendClosedException`.
     */
    public function close(): void;
}
