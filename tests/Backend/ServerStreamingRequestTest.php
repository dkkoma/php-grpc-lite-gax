<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend;

use GrpcLiteGax\Backend\ServerStreamingRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServerStreamingRequestTest extends TestCase
{
    public function testBuildsGrpcPath(): void
    {
        $request = new ServerStreamingRequest(
            service: 'service.v1.Service',
            method: 'List',
            payload: 'payload',
            metadata: ['request-header' => ['value']],
            timeoutSeconds: 1.5,
        );

        self::assertSame('/service.v1.Service/List', $request->path());
    }

    #[DataProvider('invalidServiceAndMethodProvider')]
    public function testRejectsEmptyServiceOrMethod(string $service, string $method): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('server streaming service and method must be non-empty.');

        new ServerStreamingRequest($service, $method, 'payload');
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidServiceAndMethodProvider(): iterable
    {
        yield 'empty service' => ['', 'List'];
        yield 'empty method' => ['service.v1.Service', ''];
    }

    /**
     * @param array<string, mixed> $metadata
     */
    #[DataProvider('invalidMetadataProvider')]
    public function testRejectsInvalidMetadata(array $metadata): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // @phpstan-ignore argument.type (invalid metadata shape is the behavior under test)
        new ServerStreamingRequest('service.v1.Service', 'List', 'payload', $metadata);
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalidMetadataProvider(): iterable
    {
        yield 'non-list metadata values' => [['request-header' => ['nested' => 'value']]];
        yield 'non-string metadata value' => [['request-header' => [1]]];
    }

    #[DataProvider('invalidTimeoutProvider')]
    public function testRejectsInvalidTimeout(float $timeoutSeconds): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('server streaming timeout must be finite and positive.');

        new ServerStreamingRequest('service.v1.Service', 'List', 'payload', timeoutSeconds: $timeoutSeconds);
    }

    /**
     * @return iterable<string, array{float}>
     */
    public static function invalidTimeoutProvider(): iterable
    {
        yield 'zero' => [0.0];
        yield 'negative' => [-1.0];
        yield 'infinite' => [INF];
        yield 'not a number' => [NAN];
    }
}
