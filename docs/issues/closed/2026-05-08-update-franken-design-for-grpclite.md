# Update FrankenGrpcBackend Design Against GrpcLiteBackend

State: closed
Source: work unit

## Context

`FrankenGrpcBackend` design should be checked against the `GrpcLiteBackend` design before implementation.

## Impact

The first concrete backend could otherwise encode assumptions that do not fit the second backend.

## Proposed Fix

Adjust `docs/design.md` so the FrankenPHP and `php-grpc-lite` backend mappings share the same `UnaryBackend` concepts.

## Fix Summary

Updated `FrankenGrpcBackend` design to use the same backend-native scalar boundary as `GrpcLiteBackend` and clarified bridge ownership.

## Verification

Reviewed `docs/design.md`.
