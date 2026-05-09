# Review: Spanner Smoke Not In Verify

State: closed
Source: test/smoke reviewer

## Finding

`composer verify` does not run the Spanner emulator smoke test, so standard verification can pass without exercising the requested DML and streaming SELECT behavior.

## Proposed Fix

Add a separate smoke verification script or document that Spanner smoke must be run separately with an emulator.

## Fix Summary

Addressed in code and tests.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer test:spanner-smoke`
