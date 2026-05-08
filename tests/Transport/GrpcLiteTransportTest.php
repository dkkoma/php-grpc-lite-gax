<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Transport;

use GrpcLiteGax\Transport\GrpcLiteTransport;
use PHPUnit\Framework\TestCase;

final class GrpcLiteTransportTest extends TestCase
{
    public function testBuildCreatesTransport(): void
    {
        $transport = GrpcLiteTransport::build('service.googleapis.com:443');

        $transport->close();

        $this->addToAssertionCount(1);
    }
}
