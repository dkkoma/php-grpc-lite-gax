<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Integration\Franken;

use FrankenGrpc\Channel;
use FrankenGrpc\ServerStreamingCall;
use FrankenGrpc\Status;
use FrankenGrpc\UnaryCall;
use FrankenGrpc\UnaryResult;
use PHPUnit\Framework\TestCase;

final class FrankenGrpcNativeSurfaceTest extends TestCase
{
    public function testRealFrankenGrpcExtensionSurfaceIsLoaded(): void
    {
        if (defined(Channel::class . '::CODEX_TEST_STUB')) {
            self::fail('The FrankenGrpc test stub is loaded; run franken-smoke with the real extension preloaded.');
        }

        self::assertTrue(class_exists(Channel::class));
        self::assertTrue(class_exists(UnaryCall::class));
        self::assertTrue(class_exists(ServerStreamingCall::class));
        self::assertTrue(class_exists(UnaryResult::class));
        self::assertTrue(class_exists(Status::class));
    }
}
