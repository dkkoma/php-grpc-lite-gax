# Review: Server Streaming Accessor Failure Mapping

State: closed
Source: domain-model reviewer

## Finding

`BackendServerStreamingCall` maps failures from `responses()` and `getStatus()`, but direct accessors such as `getMetadata()`, `getTrailingMetadata()`, `getPeer()`, and `cancel()` can still leak raw backend exceptions.

## Proposed Fix

Wrap direct call-state accessors with the same backend-failure mapping policy.

## Fix Summary

Wrapped direct server-streaming call-state accessors with backend failure mapping and added coverage for metadata, trailing metadata, peer, and cancel failures.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer test:spanner-smoke`
