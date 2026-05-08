# Inspect php-grpc-lite Extension API

State: closed
Source: work unit

## Context

Before implementing `GrpcLiteBackend`, inspect `dkkoma/php-grpc-lite` to identify the direct extension API and avoid depending on `grpc/grpc` wrapper classes for runtime call execution.

## Impact

The backend mapping depends on the exact low-level call shape, deadline handling, metadata output, and status object shape exported by the extension.

## Proposed Fix

Use the Packagist package metadata and source tree to document the direct API assumptions in `docs/design.md`, then implement against those assumptions.

## Fix Summary

Inspected `dkkoma/php-grpc-lite` package metadata and source. Updated `docs/design.md` to record the direct low-level extension surface used by this repository.

## Verification

Fixed in `20763ad`. Verified with `composer validate-project`, `composer lint`, and `composer test:coverage`.
