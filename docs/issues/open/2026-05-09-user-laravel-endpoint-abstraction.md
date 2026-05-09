# Laravel Endpoint Abstraction Feels Too Low-Level

State: open
Source: user instruction

## Context

The user noted that Laravel applications normally consume google-cloud-php
packages such as `google/cloud-spanner` and do not think in terms of raw gRPC
endpoints. The current README Laravel example asks users to configure a
`services.spanner.endpoint` value and pass it both as `apiEndpoint` and into
`GrpcLiteTransport::build()`.

## Impact

The documented Laravel integration exposes a lower-level transport concern than
most google-cloud-php users expect. This makes the library feel like a GAPIC
transport assembly tool instead of a drop-in runtime selection layer.

## Proposed Fix

Clarify the intended abstraction. Keep endpoint-aware transport construction for
low-level usage, but provide or document a higher-level helper that derives the
default google-cloud endpoint from the selected service and only requires an
endpoint for emulator/custom endpoint use cases.

## Fix Summary


## Verification

Not run; issue capture only.
