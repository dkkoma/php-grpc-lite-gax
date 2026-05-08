# Implement FrankenGrpcBackend

State: closed
Source: work unit

## Context

The repository has an internal unary backend contract and a design for FrankenPHP grpc-go mapping, but no concrete `FrankenGrpcBackend` implementation.

## Impact

The first production backend path is not represented in code or tests.

## Proposed Fix

Implement `FrankenGrpcBackend` against an internal bridge interface that can be adapted to FrankenPHP grpc-go, and cover it with the backend contract tests plus backend-specific tests.

## Fix Summary

Implemented `FrankenGrpcBackend`, `FrankenGrpcBridge`, and `FrankenGrpcResponse`. Added bridge fake, backend-specific tests, and reusable contract coverage for the Franken backend.

## Verification

- `composer validate-project`
- `composer lint`
- `composer test`
