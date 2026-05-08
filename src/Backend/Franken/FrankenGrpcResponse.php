<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend\Franken;

use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\MetadataValidator;

/**
 * @internal
 */
final readonly class FrankenGrpcResponse
{
    /**
     * @param array<string, list<string>> $metadata
     */
    public function __construct(
        public string $payload,
        public GrpcStatusCode $statusCode = GrpcStatusCode::OK,
        public string $statusMessage = '',
        public array $metadata = [],
    ) {
        MetadataValidator::assertMetadata($this->metadata);
    }
}
