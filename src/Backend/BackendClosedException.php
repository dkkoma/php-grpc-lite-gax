<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend;

/**
 * @internal
 */
final class BackendClosedException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Unary backend is closed.');
    }
}
