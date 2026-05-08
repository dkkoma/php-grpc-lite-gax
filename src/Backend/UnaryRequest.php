<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend;

final readonly class UnaryRequest
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
        if ($this->service === '') {
            throw new \InvalidArgumentException('service must not be empty.');
        }

        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_.]*$/', $this->service)) {
            throw new \InvalidArgumentException('service must be a canonical protobuf service name.');
        }

        if ($this->method === '') {
            throw new \InvalidArgumentException('method must not be empty.');
        }

        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $this->method)) {
            throw new \InvalidArgumentException('method must be a protobuf method name.');
        }

        MetadataValidator::assertMetadata($this->metadata);

        if ($this->timeoutSeconds !== null && $this->timeoutSeconds <= 0.0) {
            throw new \InvalidArgumentException('timeoutSeconds must be positive when provided.');
        }
    }

    public function path(): string
    {
        return '/' . $this->service . '/' . $this->method;
    }

}
