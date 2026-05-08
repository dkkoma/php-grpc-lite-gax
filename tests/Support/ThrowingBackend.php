<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Support;

use GrpcLiteGax\Backend\UnaryBackend;
use GrpcLiteGax\Backend\UnaryRequest;
use GrpcLiteGax\Backend\UnaryResponse;

final class ThrowingBackend implements UnaryBackend
{
    public function __construct(
        private readonly \Throwable $exception,
    ) {
    }

    #[\Override]
    public function call(UnaryRequest $request): UnaryResponse
    {
        throw $this->exception;
    }

    #[\Override]
    public function close(): void
    {
    }
}
