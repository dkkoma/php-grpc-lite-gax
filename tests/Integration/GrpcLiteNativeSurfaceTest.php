<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Integration;

use Grpc\Channel;
use Grpc\ChannelCredentials;
use GrpcLiteGax\Transport\GrpcLiteTransport;
use PHPUnit\Framework\TestCase;

final class GrpcLiteNativeSurfaceTest extends TestCase
{
    public function testBuildsTransportAgainstRealGrpcLiteExtensionSurface(): void
    {
        if (!extension_loaded('grpc')) {
            self::markTestSkipped('The grpc extension is not loaded.');
        }

        if (property_exists(Channel::class, 'instances')) {
            self::markTestSkipped('The test stub Grpc\\Channel is loaded instead of the native extension class.');
        }

        $transport = GrpcLiteTransport::build('localhost:50051', [
            'credentials' => ChannelCredentials::createSsl(),
        ]);

        $transport->close();

        $this->addToAssertionCount(1);
    }
}
