<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend;

/**
 * @internal
 */
interface ServerStreamingBackend
{
    public function start(ServerStreamingRequest $request): ServerStreamingCall;

    public function close(): void;
}
