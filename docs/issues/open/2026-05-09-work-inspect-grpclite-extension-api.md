# Inspect php-grpc-lite Extension API

State: open
Source: work unit

## Context

Before implementing `GrpcLiteBackend`, inspect `dkkoma/php-grpc-lite` to identify the direct extension API and avoid depending on `grpc/grpc` wrapper classes for runtime call execution.

## Impact

The backend mapping depends on the exact low-level call shape, deadline handling, metadata output, and status object shape exported by the extension.

## Proposed Fix

Use the Packagist package metadata and source tree to document the direct API assumptions in `docs/design.md`, then implement against those assumptions.

## Fix Summary

Fill this in when closing the issue.

## Verification

List verification commands, review steps, or acceptance notes. Use `not run` with a reason when verification is skipped.
