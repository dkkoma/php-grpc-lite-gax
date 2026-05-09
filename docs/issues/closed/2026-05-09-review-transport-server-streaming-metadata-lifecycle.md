# Review: Transport Metadata Lifecycle

State: closed
Source: transport behavior reviewer

## Finding

`GrpcLiteNativeServerStreamingCall` uses an empty array as both unread and read-empty metadata state, allowing repeated metadata reads.

## Proposed Fix

Use nullable metadata state or a separate boolean to distinguish unread metadata from empty metadata.

## Fix Summary

Addressed in code and tests.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer test:spanner-smoke`
