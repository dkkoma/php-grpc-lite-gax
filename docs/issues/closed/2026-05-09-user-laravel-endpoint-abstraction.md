# Laravel Endpoint Abstraction Feels Too Low-Level

State: closed
Source: user instruction

## Context

The user noted that Laravel applications normally consume google-cloud-php
packages such as `google/cloud-spanner` and do not think in terms of raw gRPC
endpoints. The current README Laravel example asks users to configure a
`services.spanner.endpoint` value and pass it both as `apiEndpoint` and into
`GrpcLiteTransport::build()`.

Follow-up implementation review confirmed that `google/cloud-spanner` generated
clients read `SPANNER_EMULATOR_HOST` and default `apiEndpoint`, insecure channel
credentials, and empty application credentials for emulator use. This repository
still needs an endpoint before passing a prebuilt `TransportInterface` object to
GAX, because GAX does not construct or reconfigure user-supplied transport
objects.

## Impact

The documented Laravel integration exposes a lower-level transport concern than
most google-cloud-php users expect. This makes the library feel like a GAPIC
transport assembly tool instead of a drop-in runtime selection layer.

## Proposed Fix

Clarify the intended abstraction. Keep endpoint-aware transport construction for
low-level usage, but provide or document a higher-level helper that follows
google-cloud-php conventions: use the service default endpoint normally, read
`SPANNER_EMULATOR_HOST` for emulator use, and only require explicit endpoint
configuration for custom/private endpoints.

## Fix Summary

Replaced the endpoint-oriented Laravel README example with patched GAX
`transportFactory` usage. Applications select `default`, `grpc-lite`, or
`frankenphp-grpc-go`; google-cloud-php/GAX keeps default endpoint and emulator
resolution.

## Verification

Read `vendor/google/cloud-spanner/src/V1/Client/SpannerClient.php`,
`vendor/google/cloud-spanner/src/SpannerClient.php`, and
`vendor/google/gax/src/GapicClientTrait.php` to confirm emulator and
preconstructed transport behavior.
Verified with `composer test`, `composer lint`, and `composer validate-project`.
