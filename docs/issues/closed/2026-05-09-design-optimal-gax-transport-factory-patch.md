# Optimal GAX Transport Factory Patch

State: closed
Source: user instruction

## Context

The user wants to evaluate whether patching `google/gax` with Composer patches
is the best route for this library, and if so whether the proposed
`transportFactory` approach is the optimal patch shape.

## Impact

A local GAX patch can solve endpoint resolution without replacing Google Cloud
client bindings, but a poorly shaped patch can diverge from upstream GAX and
become hard to maintain.

## Proposed Fix

Design the patch around GAX's existing transport construction boundary: keep
endpoint, emulator, universe domain, mTLS, credentials, and client defaults in
GAX/google-cloud-php, and allow a user-supplied factory only at the point where
GAX would otherwise call `GrpcTransport::build()`, `RestTransport::build()`, or
`GrpcFallbackTransport::build()`.

## Fix Summary

Added `patches/google-gax-transport-factory.patch` for `google/gax` 1.42.3.
The patch adds `transportFactory` to client options and invokes it from
`GapicClientTrait::createTransport()` after transport-specific config has been
resolved. The factory must return `TransportInterface`.

## Verification

`composer test`, `composer lint`, `composer validate-project`, and patch
dry-runs against `google/gax` 1.42.3.
