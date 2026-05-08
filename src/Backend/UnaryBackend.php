<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend;

interface UnaryBackend
{
    public function call(UnaryRequest $request): UnaryResponse;
}
