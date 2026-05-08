# GrpcLite runtime dependency is not declared

State: open
Source: reviewer finding

## Context

`GrpcLiteTransport::build()` constructs `GrpcLiteNativeBridge`, which requires the low-level `Grpc\Channel`, `Grpc\Call`, `Grpc\Timeval`, and credential surface at runtime. `composer.json` currently requires `google/gax` but does not declare `dkkoma/php-grpc-lite` or an equivalent runtime requirement.

## Impact

Consumers can install this package and call `GrpcLiteTransport::build()` without Composer ensuring the intended `php-grpc-lite` runtime is present. Because `google/gax` currently brings `grpc/grpc` into the dependency graph, `Grpc\*` symbols may be satisfied by the wrong package, weakening the intended non-wrapper runtime boundary.

## Proposed Fix

Declare the intended `php-grpc-lite` runtime dependency or an explicit Composer suggestion/conflict strategy once the package constraint is chosen. Add documentation and verification that `GrpcLiteNativeBridge` is exercised against `dkkoma/php-grpc-lite` low-level classes rather than `grpc/grpc` wrapper call paths.

## Fix Summary

Fill this in when closing the issue. Summarize what changed and where.

## Verification

Review finding only. `composer lint` and `composer test:coverage` pass with the current dependency set.
