# Review: Spanner Smoke Missing Env Skip

State: closed
Source: test/smoke reviewer

## Finding

Direct `phpunit --testsuite spanner-smoke` skips when `SPANNER_EMULATOR_HOST` is missing, which can report green without covering DML or SELECT.

## Proposed Fix

Fail closed when the env var is missing unless an explicit opt-out is provided.

## Fix Summary

Addressed in code and tests.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer test:spanner-smoke`
