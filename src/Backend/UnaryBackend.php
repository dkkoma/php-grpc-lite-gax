<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend;

/**
 * @internal
 */
interface UnaryBackend
{
    public function call(UnaryRequest $request): UnaryResponse;

    public function close(): void;
}
