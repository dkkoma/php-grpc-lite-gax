<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend;

final readonly class UnaryResponse
{
    /**
     * @param array<string, list<string>> $metadata
     */
    public function __construct(
        public string $payload,
        public GrpcStatusCode $grpcStatusCode = GrpcStatusCode::OK,
        public string $statusMessage = '',
        public array $metadata = [],
    ) {
        MetadataValidator::assertMetadata($this->metadata);
    }

    public function isOk(): bool
    {
        return $this->grpcStatusCode === GrpcStatusCode::OK;
    }
}
