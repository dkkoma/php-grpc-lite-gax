<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend;

/**
 * @internal
 */
final class MetadataValidator
{
    /**
     * @param array<mixed> $metadata
     */
    public static function assertMetadata(array $metadata): void
    {
        foreach ($metadata as $name => $values) {
            if (!is_string($name) || $name === '') {
                throw new \InvalidArgumentException('metadata names must be non-empty strings.');
            }

            if (!preg_match('/^[0-9a-z_.-]+$/', $name)) {
                throw new \InvalidArgumentException('metadata names must use lowercase gRPC metadata characters.');
            }

            if (str_starts_with($name, 'grpc-')) {
                throw new \InvalidArgumentException('metadata names starting with grpc- are reserved.');
            }

            if (!is_array($values)) {
                throw new \InvalidArgumentException('metadata values must be lists of strings.');
            }

            if (!array_is_list($values)) {
                throw new \InvalidArgumentException('metadata values must be lists of strings.');
            }

            foreach ($values as $value) {
                if (!is_string($value)) {
                    throw new \InvalidArgumentException('metadata values must be lists of strings.');
                }
            }
        }
    }
}
