# Review: Server Streaming Request Validation

State: closed
Source: domain-model reviewer

## Finding

`ServerStreamingRequest` only validates non-empty service and method names, while `UnaryRequest` enforces protobuf service and method token rules.

## Proposed Fix

Apply the same service and method validation invariants to server-streaming requests.

## Fix Summary

Addressed in code and tests.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer test:spanner-smoke`
