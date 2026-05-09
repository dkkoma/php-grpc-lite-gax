# README Spanner Host Command

State: closed
Source: reviewer finding

## Context

Reviewers noted that the README documents `SPANNER_EMULATOR_HOST=localhost:9010 composer test:spanner-smoke`, but the script runs PHPUnit inside Docker containers.

## Impact

Inside the smoke containers, `localhost` points at the container itself. The documented command can fail even when the emulator is reachable from the host.

## Proposed Fix

Document a Docker-reachable emulator host and network, with `host.docker.internal` mentioned as the host-published-port alternative.

## Fix Summary

Updated README smoke commands to use a Docker-reachable Spanner emulator host and documented `host.docker.internal:9010` for host-published emulators.

## Verification

- `composer verify`
