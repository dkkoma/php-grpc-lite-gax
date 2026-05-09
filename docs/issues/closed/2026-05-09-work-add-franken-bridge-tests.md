# Add Franken Bridge Tests

State: closed
Source: work unit

## Context

The native Franken bridge needs deterministic unit tests without requiring the extension to be loaded in the default test process.

## Proposed Fix

Add dev autoload stubs for `FrankenGrpc\*` classes and tests for unary delegation, server streaming, status mapping, metadata, deadlines, cancellation, and close lifecycle.

## Fix Summary

Implemented the FrankenPHP native bridge for unary and server streaming, added test stubs and unit coverage, and updated the design document.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
