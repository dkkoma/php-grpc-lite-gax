# Reuse Client Default Endpoint Before Transport Injection

State: open
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

Investigate a pre-transport options helper that accepts a generated client class
name or client options array, lets the upstream client default/emulator logic
determine `apiEndpoint` where possible, then constructs this repository's
transport from that resolved endpoint. Do not try to derive endpoints inside
`TransportInterface` call methods because GAX `Call` objects do not carry
endpoint information.

## Fix Summary


## Verification

Read `vendor/google/gax/src/Transport/TransportInterface.php`,
`vendor/google/gax/src/GapicClientTrait.php`, and
`vendor/google/gax/src/Options/ClientOptions.php` to confirm endpoint is used
before transport construction and is not passed through `Call` options.
