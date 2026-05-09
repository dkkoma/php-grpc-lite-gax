# Review: Transport Status Exception Mapping

State: closed
Source: transport behavior reviewer

## Finding

Malformed native status during `BackendServerStreamingCall::getStatus()` can escape as `RuntimeException`, bypassing `ServerStream`'s normal `ApiException` flow.

## Proposed Fix

Catch backend status exceptions in the GAX wrapper and expose an `UNAVAILABLE` status object.

## Fix Summary

Addressed in code and tests.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer test:spanner-smoke`
