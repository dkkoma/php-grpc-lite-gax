<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Support;

use GrpcLiteGax\Backend\UnaryBackend;
use GrpcLiteGax\Transport\AbstractGrpcTransport;

final class TestGrpcTransport extends AbstractGrpcTransport
{
    public function __construct(UnaryBackend $backend)
    {
        parent::__construct($backend);
    }
}
