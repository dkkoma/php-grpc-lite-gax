# Laravel Multiple Google Cloud Clients

State: open
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

Document and design Laravel integration around per-service client bindings:
bind Spanner clients with Spanner endpoint/emulator rules and Pub/Sub clients
with Pub/Sub endpoint/emulator rules. Share only backend selection and common
runtime defaults globally. Avoid a single app-wide `TransportInterface` binding
unless it is scoped to one endpoint.

## Fix Summary


## Verification

Read `vendor/google/cloud-pubsub/src/PubSubClient.php` to confirm Pub/Sub
handwritten client dispatches to `PublisherClient`, `SubscriberClient`, and
`SchemaServiceClient`, and uses `PUBSUB_EMULATOR_HOST`.
