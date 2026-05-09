# Review: Server Streaming Backend Failure Mapping

State: closed
Source: domain-model reviewer

## Finding

Server-streaming backend and native status failures can escape as raw exceptions instead of GAX `ApiException`.

## Proposed Fix

Map backend failures during stream start, response iteration, and final status reads to `UNAVAILABLE` GAX failures.

## Fix Summary

Addressed in code and tests.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer test:spanner-smoke`
