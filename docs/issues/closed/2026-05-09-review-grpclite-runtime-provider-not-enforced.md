# GrpcLite Transport Does Not Enforce php-grpc-lite Runtime Provider

State: closed
Source: reviewer finding

## Context

Transport behavior re-review found that `GrpcLiteNativeBridge::assertNativeSurfaceAvailable()` only checks for generic `Grpc\Channel`, `Grpc\Call`, and `Grpc\Timeval` classes. The current Composer graph still installs `grpc/grpc` through `google/gax`, while `dkkoma/php-grpc-lite` is only a suggestion. As a result, `GrpcLiteTransport::build()` can pass its runtime surface checks in an environment backed by the standard gRPC extension and `grpc/grpc` package rather than the intended `php-grpc-lite` provider.

## Impact

The repository's main transport behavior goal is to use a `TransportInterface` implementation that avoids the `grpc/grpc` wrapper runtime path and targets `php-grpc-lite` low-level `Grpc\Channel` / `Grpc\Call` mapping. Without enforcing or detecting the intended provider, tests and users can accidentally validate the wrong runtime, hiding mapping differences in channel options, deadlines, metadata, trailers, and status objects.

## Proposed Fix

Define an explicit runtime-provider strategy for `GrpcLiteTransport::build()`: require or otherwise verify `dkkoma/php-grpc-lite`, document any unavoidable `google/gax` transitive `grpc/grpc` package constraint, and add a native smoke check that confirms the active low-level `Grpc\*` surface is the intended `php-grpc-lite` provider rather than only any generic `Grpc\*` symbols.

## Fix Summary

Added a provider check requiring `Grpc\VERSION` in `GrpcLiteNativeBridge`, documented that `dkkoma/php-grpc-lite` is the intended runtime provider, and made `composer test:native-smoke` fail when only test stubs are available.

## Verification

Verified with `composer test`, `composer lint`, and `composer test:coverage`. `composer test:native-smoke` fails clearly in the current container because the real extension is unavailable.
