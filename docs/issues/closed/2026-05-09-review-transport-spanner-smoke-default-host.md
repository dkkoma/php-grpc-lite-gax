# Review: Spanner Smoke Default Host

State: closed
Source: transport behavior reviewer

## Finding

`test:spanner-smoke` supplies a default emulator host, bypassing the missing-env guard and producing connection failures when the emulator is absent.

## Proposed Fix

Require `SPANNER_EMULATOR_HOST` explicitly or add an intentional connectivity probe.

## Fix Summary

Addressed in code and tests.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer test:spanner-smoke`
