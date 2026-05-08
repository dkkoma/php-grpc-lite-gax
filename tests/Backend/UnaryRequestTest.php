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
        $this->expectExceptionMessage('timeoutSeconds must be positive when provided.');

        new UnaryRequest('service.v1.Service', 'Method', 'payload', timeoutSeconds: 0.0);
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
}
