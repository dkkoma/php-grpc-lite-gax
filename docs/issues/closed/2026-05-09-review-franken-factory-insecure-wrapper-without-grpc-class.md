# Franken Factory Misses Insecure Wrapper Without Grpc Class

State: closed
Source: reviewer finding

## Context

Re-review of the GAX `transportFactory` patch and `GaxTransportFactory` fixes found that the patched `hasInsecureCredentials` context is derived only from `transportConfig['stubOpts']['credentials']` when `Grpc\ChannelCredentials` exists.

Generated clients such as `SpannerClient::setDefaultEmulatorConfig()` set `InsecureCredentialsWrapper` for emulator mode even when `Grpc\ChannelCredentials` is not available. In a FrankenPHP-only runtime, `Grpc\ChannelCredentials` is expected to be absent, so generated emulator clients can reach `GaxTransportFactory::franken()` with `hasEmulator=false` and `hasInsecureCredentials=false`.

Relevant files:

- `patches/google-gax-transport-factory.patch`
- `src/GaxTransportFactory.php`
- `vendor/google/cloud-spanner/src/V1/Client/SpannerClient.php`
- `tests/Transport/GaxTransportFactoryPatchTest.php`

## Impact

The previous Franken plaintext fix is incomplete for the intended FrankenPHP generated-client emulator path. With only the `FrankenGrpc` extension loaded, `SPANNER_EMULATOR_HOST` can select an emulator endpoint and insecure credentials wrapper, but the factory still builds a TLS Franken channel instead of `['plaintext' => true]`.

The current regression test does not catch this because dev autoload stubs define `Grpc\ChannelCredentials`, causing generated clients to populate `stubOpts['credentials']`.

## Proposed Fix

Teach the GAX patch context to mark insecure credentials when the resolved client credentials wrapper is an `InsecureCredentialsWrapper`, not only when gRPC stub credentials compare equal to `ChannelCredentials::createInsecure()`.

Add a focused regression that simulates the no-`Grpc\ChannelCredentials` Franken-only path or directly verifies that `InsecureCredentialsWrapper` sets `hasInsecureCredentials=true` independently of stub credentials.

## Fix Summary

Updated the GAX patch context so `hasInsecureCredentials` is true when the resolved client credentials wrapper is `InsecureCredentialsWrapper`, independent of `Grpc\ChannelCredentials`. Added regression coverage for the wrapper-only path.

## Verification

Verified with `composer test`, `composer verify`, and GAX patch forward/reverse dry-runs in the PHP 8.4 dev container.
