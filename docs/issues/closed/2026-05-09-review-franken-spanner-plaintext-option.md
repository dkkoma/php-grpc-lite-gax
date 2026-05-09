# Franken Spanner Plaintext Option

State: closed
Source: reviewer finding

## Context

The transport reviewer found that the Franken Spanner smoke targets the emulator without explicitly requesting plaintext/insecure channel behavior.

## Impact

The smoke should not rely on extension defaults when the emulator transport mode is part of the test contract.

## Proposed Fix

Build `FrankenGrpcTransport` with an explicit plaintext channel option for the emulator.

## Fix Summary

`FrankenSpannerEmulatorSmokeTest` now builds `FrankenGrpcTransport` with `['plaintext' => true]` for emulator calls.

## Verification

- `composer verify`
- `SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 tools/spanner-smoke.sh`
