<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Transport;

use Google\ApiCore\InsecureCredentialsWrapper;
use Google\ApiCore\Transport\TransportInterface;
use Google\Cloud\Spanner\V1\Client\SpannerClient;
use Grpc\ChannelCredentials;
use GrpcLiteGax\Tests\Support\FakeBackend;
use GrpcLiteGax\Tests\Support\TestGrpcTransport;
use PHPUnit\Framework\TestCase;

final class GaxTransportFactoryPatchTest extends TestCase
{
    public function testPatchedGaxFactoryReceivesResolvedSpannerEmulatorEndpoint(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());
        $seen = null;

        putenv('SPANNER_EMULATOR_HOST=patched-spanner:9010');

        try {
            $client = new SpannerClient([
                'transport' => 'grpc',
                'transportFactory' => static function (
                    string $transportName,
                    string $apiEndpoint,
                    array $transportConfig,
                    array $context,
                ) use (
                    &$seen,
                    $transport,
                ): TransportInterface {
                    $seen = [
                        'transportName' => $transportName,
                        'apiEndpoint' => $apiEndpoint,
                        'transportConfig' => $transportConfig,
                        'context' => $context,
                    ];

                    return $transport;
                },
            ]);
        } finally {
            putenv('SPANNER_EMULATOR_HOST');
        }

        $client->close();

        self::assertIsArray($seen);
        self::assertSame('grpc', $seen['transportName']);
        self::assertSame('patched-spanner:9010', $seen['apiEndpoint']);
        self::assertIsArray($seen['transportConfig']['stubOpts']);
        self::assertArrayHasKey('credentials', $seen['transportConfig']['stubOpts']);
        self::assertTrue($seen['context']['hasInsecureCredentials']);
    }

    public function testPatchedGaxFactoryMarksExplicitInsecureCredentials(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());
        $seen = null;

        $client = new SpannerClient([
            'credentials' => new InsecureCredentialsWrapper(),
            'transport' => 'grpc',
            'transportConfig' => [
                'grpc' => [
                    'stubOpts' => [
                        'credentials' => ChannelCredentials::createInsecure(),
                    ],
                ],
            ],
            'transportFactory' => static function (
                string $transportName,
                string $apiEndpoint,
                array $transportConfig,
                array $context,
            ) use (
                &$seen,
                $transport,
            ): TransportInterface {
                unset($transportName, $apiEndpoint, $transportConfig);
                $seen = $context;

                return $transport;
            },
        ]);

        $client->close();

        self::assertIsArray($seen);
        self::assertTrue($seen['hasInsecureCredentials']);
    }

    public function testPatchedGaxFactoryMarksInsecureCredentialsWrapper(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());
        $seen = null;

        $client = new SpannerClient([
            'credentials' => new InsecureCredentialsWrapper(),
            'transport' => 'grpc',
            'transportFactory' => static function (
                string $transportName,
                string $apiEndpoint,
                array $transportConfig,
                array $context,
            ) use (
                &$seen,
                $transport,
            ): TransportInterface {
                unset($transportName, $apiEndpoint, $transportConfig);
                $seen = $context;

                return $transport;
            },
        ]);

        $client->close();

        self::assertIsArray($seen);
        self::assertTrue($seen['hasInsecureCredentials']);
    }
}
