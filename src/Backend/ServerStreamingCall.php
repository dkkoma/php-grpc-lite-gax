<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend;

/**
 * @internal
 */
interface ServerStreamingCall
{
    /**
     * @return iterable<string>
     */
    public function responses(): iterable;

    public function statusCode(): GrpcStatusCode;

    public function statusMessage(): string;

    /**
     * @return array<string, list<string>>
     */
    public function metadata(): array;

    /**
     * @return array<string, list<string>>
     */
    public function trailingMetadata(): array;

    public function getPeer(): string;

    public function cancel(): void;
}
