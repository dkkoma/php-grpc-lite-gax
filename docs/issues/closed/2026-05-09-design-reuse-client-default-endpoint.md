# Reuse Client Default Endpoint Before Transport Injection

State: closed
Source: design decision

## Context

The user wants this library to avoid duplicating Google Cloud API default
endpoints and instead reuse the endpoint that the upstream google-cloud-php
client would normally select. GAX currently accepts a prebuilt
`TransportInterface` object, but once that object is supplied the generated
client does not reconstruct it from `apiEndpoint`.

## Impact

If this library owns a separate endpoint registry, it can drift from upstream
google-cloud-php defaults. If it always requires explicit endpoints, normal
Google Cloud usage becomes lower-level than the official clients.

## Proposed Fix

Use a GAX patch instead of a pre-transport options helper. The patch adds a
`transportFactory` hook at the GAX transport construction boundary, after
google-cloud-php/GAX has resolved `apiEndpoint` and transport config.

## Fix Summary

Implemented `patches/google-gax-transport-factory.patch` and
`GaxTransportFactory`. The factory receives the resolved endpoint from patched
GAX and builds `GrpcLiteTransport` or `FrankenGrpcTransport`; `default` returns
`null` to keep GAX behavior unchanged.

## Verification

Read `vendor/google/gax/src/Transport/TransportInterface.php`,
`vendor/google/gax/src/GapicClientTrait.php`, and
`vendor/google/gax/src/Options/ClientOptions.php` to confirm endpoint is used
before transport construction and is not passed through `Call` options.
Verified with `GaxTransportFactoryPatchTest`, `composer test`, `composer lint`,
`composer validate-project`, and patch dry-runs against `google/gax` 1.42.3.
