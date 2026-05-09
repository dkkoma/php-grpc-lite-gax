# Google Cloud Endpoint Profiles

State: closed
Source: user instruction

## Context

The user clarified that the endpoint abstraction should not be Spanner-specific.
For Google Cloud APIs, normal users should select this transport runtime without
manually configuring the default gRPC endpoint. Emulator environment variables
or explicit custom endpoints should remain supported where the upstream
google-cloud-php package already exposes that behavior.

## Impact

Requiring endpoint configuration for every service makes this library feel lower
level than google-cloud-php. It also risks diverging from service-specific
conventions such as `SPANNER_EMULATOR_HOST` and `PUBSUB_EMULATOR_HOST`.

## Proposed Fix

Do not add a service profile layer. Keep Google Cloud API endpoint and emulator
knowledge in upstream google-cloud-php/GAX. Use the patched GAX
`transportFactory` option so this repository only chooses the transport runtime.

## Fix Summary

Closed without implementing a service endpoint registry. Added
`patches/google-gax-transport-factory.patch`, `GaxTransportFactory`, README
usage, and design documentation for backend-only runtime selection.

## Verification

`composer test`, `composer lint`, `composer validate-project`, and patch
dry-runs against `google/gax` 1.42.3.
