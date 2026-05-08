# Implement GrpcLiteBackend

State: open
Source: work unit

## Context

`GrpcLiteBackend` is currently design-only. The next implementation slice should add the backend, bridge contract, native bridge, and contract tests.

## Impact

This validates that the shared `UnaryBackend` contract can support a second concrete backend and exposes issues before creating a stable user-facing API.

## Proposed Fix

Add `GrpcLiteBackend`, `GrpcLiteBridge`, `GrpcLiteResponse`, and `GrpcLiteNativeBridge` under `src/Backend/GrpcLite/`. Cover bridge delegation, status mapping, close lifecycle, and native low-level API mapping with tests.

## Fix Summary

Fill this in when closing the issue.

## Verification

List verification commands, review steps, or acceptance notes. Use `not run` with a reason when verification is skipped.
