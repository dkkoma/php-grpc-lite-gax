# Document FrankenPHP Extension API

State: closed
Source: work unit

## Context

The current PHP adapter has stable unary and server-streaming backend contracts. A separate FrankenPHP extension can implement these contracts if this repository supplies a precise API spec.

## Proposed Fix

Create `docs/frankenphp-extension-api.md` with required PHP extension classes, method signatures, lifecycle rules, metadata and status shape, error semantics, and smoke/compliance test expectations.

## Fix Summary

Added `docs/frankenphp-extension-api.md` and linked it from the current design document.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
