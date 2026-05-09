# Patch Should Document TransportFactory Signature

State: closed
Source: reviewer finding

## Context

`patches/google-gax-transport-factory.patch` adds a public
`transportFactory` option to GAX client options. The patched docblock says the
option is a callable used after endpoint and transport configuration
resolution, but it does not document the callable signature, argument meanings,
context keys, or return contract. Users writing a custom factory have to infer
the API from `GapicClientTrait::createTransportFromFactory()` or from this
repository's design notes.

## Impact

The patch creates a new public extension point without enough inline API
documentation for safe use. That increases the chance that application code
will depend on incidental details, rebuild endpoint registries, or miss that
GAX/google-cloud-php still own endpoint, emulator, mTLS, credentials, and client
default resolution.

## Proposed Fix

Expand the patched GAX option documentation to state the callable shape:
`callable(string $transport, string $apiEndpoint, array $transportConfig, array $context): TransportInterface`.
Document the current context keys (`clientCertSource`, `hasEmulator`) and state
that the callable receives already-resolved GAX inputs and must return a
`TransportInterface`.

## Fix Summary

Expanded the patched `ClientOptions` docblock and README/design notes with the `transportFactory` callable signature, return contract, and context keys.

## Verification

Verified by reviewing `patches/google-gax-transport-factory.patch`, README, and `docs/design.md`; also ran `composer verify`.
