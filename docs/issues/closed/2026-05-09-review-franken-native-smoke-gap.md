# Review: Franken Native Smoke Gap

State: closed
Source: test and transport reviewers

## Finding

The Franken bridge has stub-backed unit coverage but no smoke suite that loads the real `FrankenGrpc` extension from `/Users/daisuke/src/frankenphp-grpc-go-client`.

## Proposed Fix

Add an explicit `franken-smoke` PHPUnit suite and Composer script, keep it separate from default `verify` until the extension is available in this repository's test image, and document the pending real-extension verification boundary.

## Fix Summary

Added `FrankenGrpcTransport::build()` and a separate `franken-smoke` suite/script that fails if only the test stub is loaded. Updated design to document the pending real-extension smoke boundary.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
