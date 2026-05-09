# Review: Server Streaming Metadata Lifecycle

State: closed
Source: domain-model reviewer

## Finding

Calling initial metadata before response iteration can desynchronize the native stream because `metadata()` and `responses()` can both request initial metadata.

## Proposed Fix

Track whether initial metadata has been read and avoid duplicate `OP_RECV_INITIAL_METADATA` calls.

## Fix Summary

Addressed in code and tests.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer test:spanner-smoke`
