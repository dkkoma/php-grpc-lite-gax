# Native Smoke Test Succeeds When Extension Is Missing

State: closed
Source: reviewer finding

## Context

`composer test:native-smoke` runs `GrpcLiteNativeSurfaceTest`, but the test marks itself skipped when the native `grpc` extension is not loaded. In the current Dev Container image this command exits successfully with one skipped test and zero assertions.

## Impact

The smoke script does not prove that `GrpcLiteTransport::build()` works against the real `dkkoma/php-grpc-lite` runtime. CI or local verification can report success while only the stub-backed unit coverage has run.

## Proposed Fix

Make the native smoke entry point fail when the native extension is unavailable, or add a separate required environment gate such as `GRPC_LITE_NATIVE_SMOKE=1` that turns missing native support into a failure. Update the Dev Container or CI path to install/build `dkkoma/php-grpc-lite` when running that command.

## Fix Summary

Separated `native-smoke` into its own PHPUnit suite and made the test fail, not skip, when the real native extension surface is unavailable. Default `composer test` and `composer test:coverage` now run the unit suite only.

## Verification

`composer test:native-smoke` fails clearly in the current container with: `The test stub Grpc\Channel is loaded instead of the native extension class.` Verification with the real extension is pending a container image that loads `dkkoma/php-grpc-lite`.
