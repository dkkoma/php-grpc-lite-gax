<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend;

use GrpcLiteGax\Backend\UnaryRequest;
use PHPUnit\Framework\TestCase;

final class UnaryRequestTest extends TestCase
{
    public function testBuildsCanonicalPath(): void
    {
        $request = new UnaryRequest('service.v1.Service', 'Method', 'payload');

        self::assertSame('/service.v1.Service/Method', $request->path());
    }

    public function testRejectsInvalidTimeout(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('timeoutSeconds must be finite and positive when provided.');

        new UnaryRequest('service.v1.Service', 'Method', 'payload', timeoutSeconds: 0.0);
    }

    public function testRejectsEmptyService(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('service must not be empty.');

        new UnaryRequest('', 'Method', 'payload');
    }

    public function testRejectsInvalidService(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('service must be a canonical protobuf service name.');

        new UnaryRequest('1bad.Service', 'Method', 'payload');
    }

    public function testRejectsEmptyMethod(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('method must not be empty.');

        new UnaryRequest('service.v1.Service', '', 'payload');
    }

    public function testRejectsNonFiniteTimeout(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('timeoutSeconds must be finite and positive when provided.');

        new UnaryRequest('service.v1.Service', 'Method', 'payload', timeoutSeconds: INF);
    }

    public function testRejectsAssociativeMetadataValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('metadata values must be lists of strings.');

        $metadata = json_decode($this->invalidAssociativeMetadataJson(), true);
        self::assertIsArray($metadata);

        /** @var array<string, list<string>> $metadata */

        new UnaryRequest('service.v1.Service', 'Method', 'payload', $metadata);
    }

    public function testRejectsInvalidMetadataName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('metadata names must use lowercase gRPC metadata characters.');

        new UnaryRequest('service.v1.Service', 'Method', 'payload', ['Invalid' => ['value']]);
    }

    public function testRejectsEmptyMetadataName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('metadata names must be non-empty strings.');

        new UnaryRequest('service.v1.Service', 'Method', 'payload', ['' => ['value']]);
    }

    public function testRejectsNonArrayMetadataValues(): void
    {
        $metadata = json_decode($this->invalidNonArrayMetadataJson(), true);
        self::assertIsArray($metadata);

        /** @var array<string, list<string>> $metadata */

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('metadata values must be lists of strings.');

        new UnaryRequest('service.v1.Service', 'Method', 'payload', $metadata);
    }

    public function testRejectsNonStringMetadataListValue(): void
    {
        $metadata = json_decode($this->invalidNonStringMetadataValueJson(), true);
        self::assertIsArray($metadata);

        /** @var array<string, list<string>> $metadata */

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('metadata values must be lists of strings.');

        new UnaryRequest('service.v1.Service', 'Method', 'payload', $metadata);
    }

    public function testRejectsReservedMetadataName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('metadata names starting with grpc- are reserved.');

        new UnaryRequest('service.v1.Service', 'Method', 'payload', ['grpc-timeout' => ['1S']]);
    }

    public function testRejectsInvalidMethodName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('method must be a protobuf method name.');

        new UnaryRequest('service.v1.Service', 'Bad/Method', 'payload');
    }

    private function invalidAssociativeMetadataJson(): string
    {
        return '{"metadata":{"key":"value"}}';
    }

    private function invalidNonArrayMetadataJson(): string
    {
        return '{"metadata":"value"}';
    }

    private function invalidNonStringMetadataValueJson(): string
    {
        return '{"metadata":[1]}';
    }
}
