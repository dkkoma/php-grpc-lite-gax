<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Transport;

use FrankenGrpc\Channel as FrankenChannel;
use Grpc\Channel as GrpcChannel;
use Grpc\ChannelCredentials;
use GrpcLiteGax\GaxTransportFactory;
use GrpcLiteGax\Transport\FrankenGrpcTransport;
use GrpcLiteGax\Transport\GrpcLiteTransport;
use PHPUnit\Framework\TestCase;

final class GaxTransportFactoryTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        GrpcChannel::$instances = [];
        FrankenChannel::$instances = [];
    }

    public function testDefaultBackendKeepsGaxTransportUnchanged(): void
    {
        self::assertNull(GaxTransportFactory::forBackend(GaxTransportFactory::BACKEND_DEFAULT));
    }

    public function testGrpcLiteFactoryUsesResolvedGaxEndpointAndGrpcStubOptions(): void
    {
        $credentials = ChannelCredentials::createInsecure();
        $factory = GaxTransportFactory::grpcLite(['grpc.primary_user_agent' => 'override-agent']);

        $transport = $factory('grpc', 'pubsub-emulator:8085', [
            'stubOpts' => [
                'credentials' => $credentials,
                'grpc.primary_user_agent' => 'gax-agent',
            ],
        ], [
            'hasEmulator' => true,
        ]);

        self::assertInstanceOf(GrpcLiteTransport::class, $transport);
        self::assertSame('pubsub-emulator:8085', GrpcChannel::$instances[0]->target);
        self::assertSame($credentials, GrpcChannel::$instances[0]->opts['credentials']);
        self::assertSame('override-agent', GrpcChannel::$instances[0]->opts['grpc.primary_user_agent']);
    }

    public function testGrpcLiteFactoryNormalizesEndpointDefaultPort(): void
    {
        $factory = GaxTransportFactory::grpcLite();

        $factory('grpc', 'spanner.googleapis.com', [], []);

        self::assertSame('spanner.googleapis.com:443', GrpcChannel::$instances[0]->target);
    }

    public function testGrpcLiteFactoryBuildsMtlsCredentialsFromClientCertSource(): void
    {
        $factory = GaxTransportFactory::grpcLite();

        $factory('grpc', 'spanner.googleapis.com:443', [], [
            'clientCertSource' => static fn (): array => ['cert-chain', 'private-key'],
        ]);

        $credentials = GrpcChannel::$instances[0]->opts['credentials'];
        self::assertInstanceOf(ChannelCredentials::class, $credentials);
        self::assertSame('ssl', $credentials->type);
        self::assertSame('private-key', $credentials->privateKey);
        self::assertSame('cert-chain', $credentials->certChain);
    }

    public function testGrpcLiteFactoryKeepsExplicitCredentialsOverClientCertSource(): void
    {
        $explicitCredentials = ChannelCredentials::createInsecure();
        $factory = GaxTransportFactory::grpcLite();

        $factory('grpc', 'spanner.googleapis.com:443', [
            'stubOpts' => ['credentials' => $explicitCredentials],
        ], [
            'clientCertSource' => static fn (): array => ['cert-chain', 'private-key'],
        ]);

        self::assertSame($explicitCredentials, GrpcChannel::$instances[0]->opts['credentials']);
    }

    public function testFrankenFactoryUsesResolvedEndpointAndEmulatorPlaintext(): void
    {
        $factory = GaxTransportFactory::franken();

        $transport = $factory('grpc', 'spanner-emulator:9010', [], [
            'hasEmulator' => true,
        ]);

        self::assertInstanceOf(FrankenGrpcTransport::class, $transport);
        self::assertSame('spanner-emulator:9010', FrankenChannel::$instances[0]->target);
        self::assertSame(['plaintext' => true], FrankenChannel::$instances[0]->options);
    }

    public function testFrankenFactoryUsesInsecureCredentialsContextAsPlaintext(): void
    {
        $factory = GaxTransportFactory::franken();

        $factory('grpc', 'localhost:9010', [], [
            'hasInsecureCredentials' => true,
        ]);

        self::assertSame('localhost:9010', FrankenChannel::$instances[0]->target);
        self::assertSame(['plaintext' => true], FrankenChannel::$instances[0]->options);
    }

    public function testFactoriesRejectNonGrpcGaxTransportSelection(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('requires GAX transport "grpc"; got "rest".');

        GaxTransportFactory::grpcLite()('rest', 'example.googleapis.com:443', [], []);
    }

    public function testFrankenFactoryRejectsNonGrpcGaxTransportSelection(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('requires GAX transport "grpc"; got "grpc-fallback".');

        GaxTransportFactory::franken()('grpc-fallback', 'example.googleapis.com:443', [], []);
    }

    public function testUnknownBackendFailsClearly(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported gRPC backend "unknown".');

        GaxTransportFactory::forBackend('unknown');
    }
}
