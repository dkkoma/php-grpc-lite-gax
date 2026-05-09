# Google Cloud Endpoint Profiles

State: open
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

Design a small service profile layer that maps supported Google Cloud services
to their default endpoint and emulator environment variable. Use it from
user-facing helpers that build GAPIC client options with this repository's
transport. Keep raw `GrpcLiteTransport::build($endpoint)` and
`FrankenGrpcTransport::build($endpoint)` as low-level APIs.

## Fix Summary


## Verification

Not run; issue capture only.
