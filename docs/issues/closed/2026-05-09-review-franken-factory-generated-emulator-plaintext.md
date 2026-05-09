# Franken Factory Misses Generated Client Emulator Plaintext

State: closed
Source: reviewer finding

## Context

Transport behavior review found that `GaxTransportFactory::franken()` only enables `['plaintext' => true]` when the patched factory context has `hasEmulator=true` (`src/GaxTransportFactory.php`). Current generated GAPIC clients such as `vendor/google/cloud-spanner/src/V1/Client/SpannerClient.php` resolve `SPANNER_EMULATOR_HOST` into `apiEndpoint`, insecure credentials, and an `InsecureCredentialsWrapper`, but do not set the internal `hasEmulator` option consumed by `Google\ApiCore\GapicClientTrait`.

## Impact

Using the documented generated-client `transportFactory` path with `SPANNER_EMULATOR_HOST` and the FrankenPHP backend can build a TLS Franken channel to a plaintext emulator. This breaks the stated behavior that GAX/google-cloud-php own emulator environment variable resolution while this package can switch the runtime backend.

## Proposed Fix

Make emulator/plaintext detection work for generated clients in the factory path. Options include teaching the GAX patch to derive and pass emulator state from generated-client emulator configuration, or making `GaxTransportFactory::franken()` infer emulator/plaintext from the resolved transport config or explicit factory/channel option in a way that preserves production TLS defaults.

## Fix Summary

Extended the GAX patch context with `hasInsecureCredentials` and made `GaxTransportFactory::franken()` translate that signal to `['plaintext' => true]`. Added tests for explicit insecure credentials context and patched GAX context propagation.

## Verification

Verified with `composer test`, `composer lint`, `composer verify`, and GAX patch forward/reverse dry-runs in the PHP 8.4 dev container.
