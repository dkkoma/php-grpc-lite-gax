<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend;

use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\UnaryResponse;
use PHPUnit\Framework\TestCase;

final class UnaryResponseTest extends TestCase
{
    public function testReportsOkStatus(): void
    {
        self::assertTrue(new UnaryResponse('payload')->isOk());
        self::assertFalse(new UnaryResponse('', GrpcStatusCode::UNAVAILABLE, 'unavailable')->isOk());
    }
}
