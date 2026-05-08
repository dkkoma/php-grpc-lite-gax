# Design GrpcLiteBackend

State: closed
Source: work unit

## Context

`GrpcLiteBackend` is planned for the `php-grpc-lite` / nghttp2 path, but its mapping rules are not yet documented.

## Impact

Implementing `FrankenGrpcBackend` first without comparing the `php-grpc-lite` path may bias the shared backend contract.

## Proposed Fix

Update `docs/design.md` with the current `GrpcLiteBackend` request, response, metadata, timeout, status, and lifecycle mapping.

## Fix Summary

Updated `docs/design.md` with `GrpcLiteBackend` request mapping, response mapping, metadata/status handling, timeout handling, failure behavior, and lifecycle requirements.

## Verification

Reviewed `docs/design.md`.
