<?php

declare(strict_types=1);

namespace GrpcLiteGax;

use Google\ApiCore\Transport\TransportInterface;
use Grpc\ChannelCredentials;
use GrpcLiteGax\Transport\FrankenGrpcTransport;
use GrpcLiteGax\Transport\GrpcLiteTransport;

final class GaxTransportFactory
{
    public const BACKEND_DEFAULT = 'default';
    public const BACKEND_FRANKEN = 'frankenphp-grpc-go';
    public const BACKEND_GRPC_LITE = 'grpc-lite';

    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $channelOptions
     * @return null|callable(string, string, array<string, mixed>, array<string, mixed>): TransportInterface
     */
    public static function forBackend(string $backend, array $channelOptions = []): ?callable
    {
        return match ($backend) {
            self::BACKEND_DEFAULT => null,
            self::BACKEND_FRANKEN => self::franken($channelOptions),
            self::BACKEND_GRPC_LITE => self::grpcLite($channelOptions),
            default => throw new \InvalidArgumentException(sprintf('Unsupported gRPC backend "%s".', $backend)),
        };
    }

    /**
     * @param array<string, mixed> $channelOptions
     * @return callable(string, string, array<string, mixed>, array<string, mixed>): TransportInterface
     */
    public static function grpcLite(array $channelOptions = []): callable
    {
        return static function (
            string $transport,
            string $apiEndpoint,
            array $transportConfig,
            array $context,
        ) use ($channelOptions): TransportInterface {
            self::assertGrpcTransport($transport);
            /** @var array<string, mixed> $resolvedTransportConfig */
            $resolvedTransportConfig = $transportConfig;
            /** @var array<string, mixed> $resolvedContext */
            $resolvedContext = $context;

            return GrpcLiteTransport::build(
                self::normalizeEndpoint($apiEndpoint),
                self::grpcLiteChannelOptions($resolvedTransportConfig, $resolvedContext, $channelOptions),
            );
        };
    }

    /**
     * @param array<string, mixed> $channelOptions
     * @return callable(string, string, array<string, mixed>, array<string, mixed>): TransportInterface
     */
    public static function franken(array $channelOptions = []): callable
    {
        return static function (
            string $transport,
            string $apiEndpoint,
            array $transportConfig,
            array $context,
        ) use ($channelOptions): TransportInterface {
            self::assertGrpcTransport($transport);
            unset($transportConfig);

            $resolvedOptions = (bool) ($context['hasEmulator'] ?? false)
                || (bool) ($context['hasInsecureCredentials'] ?? false)
                ? ['plaintext' => true]
                : [];

            return FrankenGrpcTransport::build(
                self::normalizeEndpoint($apiEndpoint),
                array_replace($resolvedOptions, $channelOptions),
            );
        };
    }

    private static function assertGrpcTransport(string $transport): void
    {
        if ($transport !== 'grpc') {
            throw new \InvalidArgumentException(sprintf(
                'The %s factory requires GAX transport "grpc"; got "%s".',
                self::class,
                $transport,
            ));
        }
    }

    private static function normalizeEndpoint(string $apiEndpoint): string
    {
        $components = explode(':', $apiEndpoint);
        if (count($components) === 1) {
            return $apiEndpoint . ':443';
        }

        if (count($components) === 2) {
            return $apiEndpoint;
        }

        throw new \InvalidArgumentException(sprintf('Invalid apiEndpoint "%s".', $apiEndpoint));
    }

    /**
     * @param array<string, mixed> $transportConfig
     * @param array<string, mixed> $context
     * @param array<string, mixed> $channelOptions
     * @return array<string, mixed>
     */
    private static function grpcLiteChannelOptions(
        array $transportConfig,
        array $context,
        array $channelOptions,
    ): array {
        $resolvedOptions = self::grpcStubOptions($transportConfig);
        if (!array_key_exists('credentials', $resolvedOptions) && is_callable($context['clientCertSource'] ?? null)) {
            /** @var callable(): array{0: string, 1: string} $clientCertSource */
            $clientCertSource = $context['clientCertSource'];
            [$cert, $key] = $clientCertSource();
            $resolvedOptions['credentials'] = ChannelCredentials::createSsl(null, $key, $cert);
        }

        return array_replace($resolvedOptions, $channelOptions);
    }

    /**
     * @param array<string, mixed> $transportConfig
     * @return array<string, mixed>
     */
    private static function grpcStubOptions(array $transportConfig): array
    {
        $stubOptions = $transportConfig['stubOpts'] ?? [];

        if (!is_array($stubOptions)) {
            return [];
        }

        $resolvedOptions = [];
        foreach ($stubOptions as $name => $value) {
            if (is_string($name)) {
                $resolvedOptions[$name] = $value;
            }
        }

        return $resolvedOptions;
    }
}
