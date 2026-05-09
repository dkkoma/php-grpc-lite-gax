# README Verify Smoke Command

State: closed
Source: reviewer finding

## Context

The test/CI reviewer noted that the README lists individual smoke commands but omits the `composer verify:smoke` aggregate script.

## Impact

Contributors may miss the single command intended to run all smoke suites.

## Proposed Fix

Add `composer verify:smoke` with the required Pub/Sub and Spanner emulator environment variables.

## Fix Summary

Added a `composer verify:smoke` example with Pub/Sub and Spanner emulator environment variables.

## Verification

- `composer verify`
