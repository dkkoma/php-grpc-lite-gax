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

    public function testRejectsInvalidMetadata(): void
    {
        $metadata = json_decode($this->invalidAssociativeMetadataJson(), true);
        self::assertIsArray($metadata);

        /** @var array<string, list<string>> $metadata */

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('metadata values must be lists of strings.');

        new UnaryResponse('payload', metadata: $metadata);
    }

    public function testAllowsGrpcStatusDetailsMetadata(): void
    {
        $response = new UnaryResponse('payload', metadata: ['grpc-status-details-bin' => ['raw']]);

        self::assertSame(['grpc-status-details-bin' => ['raw']], $response->metadata);
    }

    private function invalidAssociativeMetadataJson(): string
    {
        return '{"metadata":{"key":"value"}}';
    }
}
