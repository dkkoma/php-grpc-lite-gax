<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend;

/**
 * @internal
 */
final readonly class ServerStreamingRequest
{
    /**
     * @param array<string, list<string>> $metadata
     */
    public function __construct(
        public string $service,
        public string $method,
        public string $payload,
        public array $metadata = [],
        public ?float $timeoutSeconds = null,
    ) {
        if ($this->service === '' || $this->method === '') {
            throw new \InvalidArgumentException('server streaming service and method must be non-empty.');
        }

        MetadataValidator::assertRequestMetadata($this->metadata);

        if ($this->timeoutSeconds !== null && (!is_finite($this->timeoutSeconds) || $this->timeoutSeconds <= 0)) {
            throw new \InvalidArgumentException('server streaming timeout must be finite and positive.');
        }
    }

    public function path(): string
    {
        return sprintf('/%s/%s', $this->service, $this->method);
    }
}
