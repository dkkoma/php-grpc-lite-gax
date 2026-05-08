# Install Real php-grpc-lite Extension

State: closed
Source: user instruction

## Context

The user asked to actually use the real `dkkoma/php-grpc-lite` extension, not only test stubs, and make the native smoke path green.

## Impact

Without installing/loading the real extension in the Dev Container, `GrpcLiteNativeBridge` is only verified against test stubs and `composer test:native-smoke` correctly fails.

## Proposed Fix

Update the Dev Container image to build and enable `dkkoma/php-grpc-lite` as the `grpc` extension. Rebuild the image and verify `composer test:native-smoke` passes against the native surface.

## Fix Summary

Updated `.devcontainer/Dockerfile` to build and install `dkkoma/php-grpc-lite` from commit `21567d001b2e00708b4ba2c99c9cd235d11f6d3e`. The extension is installed but not loaded by default; `composer test:native-smoke` explicitly loads `grpc.so` so unit tests keep deterministic stubs while native smoke uses the real extension.

## Verification

Rebuilt `php-grpc-lite-gax-dev`. Verified default PHP does not load `grpc`, explicit `php -d extension=grpc.so` exposes `Grpc\VERSION`, `Grpc\Channel`, and `Grpc\Call`, and `composer test:native-smoke` passes. Also verified `composer validate-project`, `composer lint`, and `composer test:coverage`.
