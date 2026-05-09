# Laravel Multiple Google Cloud Clients

State: closed
Source: user instruction

## Context

The user asked how Laravel applications should use this library when both
Spanner and Pub/Sub are enabled at the same time. Each google-cloud-php package
has its own default endpoint and emulator environment variable conventions, and
Pub/Sub's handwritten client dispatches to multiple generated clients.

## Impact

A single global transport binding is not enough for applications that use
multiple Google Cloud APIs. Each service may need its own endpoint resolution,
credentials behavior, and channel options while sharing a selected backend
runtime such as grpc-lite or FrankenPHP.

## Proposed Fix

Document Laravel integration around backend selection plus patched GAX
`transportFactory`, not a global `TransportInterface` binding and not a service
endpoint registry. Each google-cloud-php client keeps its own endpoint/emulator
resolution.

## Fix Summary

Updated README Laravel guidance to pass `transportFactory` into Spanner and
Pub/Sub clients while sharing only `GOOGLE_CLOUD_GRPC_BACKEND`.

## Verification

Read `vendor/google/cloud-pubsub/src/PubSubClient.php` to confirm Pub/Sub
handwritten client dispatches to `PublisherClient`, `SubscriberClient`, and
`SchemaServiceClient`, and uses `PUBSUB_EMULATOR_HOST`.
Verified README examples by static checks through `composer lint`.
