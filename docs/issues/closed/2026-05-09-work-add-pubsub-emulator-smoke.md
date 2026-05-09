# Add Pub/Sub Emulator Smoke

State: closed
Source: work unit

## Context

Validate a second google-cloud-php generated client over the repository transport without expanding to Cloud Storage or BigQuery.

## Proposed Fix

Add `google/cloud-pubsub` as a dev dependency, define a Pub/Sub smoke PHPUnit suite, and cover topic creation, subscription creation, publish, pull, and ack against the Pub/Sub emulator.

## Fix Summary

Implemented and verified.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e PUBSUB_EMULATOR_HOST=php-grpc-lite-gax-pubsub-emulator:8085 -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify:smoke`
