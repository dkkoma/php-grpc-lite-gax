# README PubSub Host Command

State: closed
Source: reviewer finding

## Context

The transport reviewer found that the README `composer verify:smoke` example used the Docker service name for `PUBSUB_EMULATOR_HOST`, while `test:pubsub-smoke` currently runs directly through PHP rather than a Docker-network wrapper.

## Impact

The documented Pub/Sub endpoint may not resolve from the environment that runs Composer.

## Proposed Fix

Document a host-reachable Pub/Sub emulator endpoint in the README smoke command and clarify the Docker-network setting applies to Spanner smoke containers.

## Fix Summary

Changed the README `composer verify:smoke` example to use `PUBSUB_EMULATOR_HOST=localhost:8085` and clarified that Pub/Sub and Spanner smoke commands have different reachability contexts.

## Verification

- `composer verify`
