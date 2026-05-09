# Review: Franken Missing Transport Entry

State: closed
Source: domain-model reviewer

## Finding

The FrankenPHP backend is implemented as an internal backend/bridge but has no public `TransportInterface` entry point equivalent to `GrpcLiteTransport::build()`.

## Proposed Fix

Add `FrankenGrpcTransport::build(string $target, array $channelOptions = [])`.

## Fix Summary

Added `FrankenGrpcTransport::build()` and a separate `franken-smoke` suite/script that fails if only the test stub is loaded. Updated design to document the pending real-extension smoke boundary.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
