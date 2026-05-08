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
        if (property_exists(Channel::class, 'instances')) {
            self::fail('The test stub Grpc\\Channel is loaded instead of the native extension class.');
        }

        if (!extension_loaded('grpc')) {
            self::fail('The grpc extension is not loaded.');
        }

        if (!defined('Grpc\\VERSION')) {
            self::fail('The php-grpc-lite runtime provider did not define Grpc\\VERSION.');
        }

        $transport = GrpcLiteTransport::build('localhost:50051', [
            'credentials' => ChannelCredentials::createSsl(),
        ]);

        $transport->close();

        $this->addToAssertionCount(1);
    }
}
