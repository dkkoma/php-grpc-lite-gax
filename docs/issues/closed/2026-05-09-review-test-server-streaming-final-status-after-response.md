# Review: Server Streaming Final Status After Response

State: closed
Source: test/smoke reviewer

## Finding

Server-streaming final-status coverage only checks a non-OK stream with no responses; it does not cover a stream that yields responses and then fails at final status.

## Proposed Fix

Add a test that yields at least one decoded response and throws `ApiException` only after final status is reached.

## Fix Summary

Addressed in code and tests.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer test:spanner-smoke`
