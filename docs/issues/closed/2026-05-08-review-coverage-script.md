# Review Finding: Add Stable Coverage Script

State: closed
Source: test reviewer

## Context

Coverage is available only through an ad hoc PHPUnit CLI flag.

## Impact

Coverage checks are not discoverable through stable Composer commands.

## Proposed Fix

Add a Composer coverage script and document it.

## Fix Summary

Added `composer test:coverage` and documented it in `AGENTS.md`.

## Verification

- `composer test:coverage`
