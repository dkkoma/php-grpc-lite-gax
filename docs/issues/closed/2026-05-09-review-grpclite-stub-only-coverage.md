# GrpcLite native bridge coverage uses only hand-written stubs

State: closed
Source: reviewer finding

## Context

Tests for `GrpcLiteNativeBridge` rely on `tests/Support/GrpcLiteExtensionStubs.php`, loaded globally through Composer `autoload-dev.files`. The stub defines `Grpc\Channel`, `Grpc\Call`, `Grpc\Timeval`, credentials, constants, and deterministic behavior.

## Impact

PHPUnit coverage reaches 100%, but the most important integration risk is not locked: whether the bridge matches the real `dkkoma/php-grpc-lite` / native `Grpc` surface. The tests can pass even if constructor signatures, batch event shapes, constants, metadata shape, or deadline behavior differ from the real library.

## Proposed Fix

Keep the unit stub for deterministic branch coverage, but add a separate smoke or integration test path that runs against the installed `dkkoma/php-grpc-lite` package/extension when available. Mark it skipped when the real native surface is unavailable, and document the command in Composer scripts or docs.

## Fix Summary

Kept deterministic stubs for unit coverage and added `GrpcLiteNativeSurfaceTest` plus `composer test:native-smoke` to exercise the real native extension surface when `grpc` is loaded.

## Verification

Fixed in `20763ad`. Verified with `composer lint` and `composer test:coverage`; the native smoke test is present but skipped in the current container because the `grpc` extension is not loaded.
