# Raise PHPStan Memory Limit

State: open
Source: work unit

## Context

After adding the GrpcLite backend and test stubs, `composer lint` crashed because PHPStan reached the configured PHP memory limit of 128M.

## Impact

The repository's stable lint entry point must be reliable in the Dev Container, especially with PHPStan level max.

## Proposed Fix

Update the Composer lint script to run PHPStan with an explicit memory limit.

## Fix Summary

Fill this in when closing the issue.

## Verification

List verification commands, review steps, or acceptance notes. Use `not run` with a reason when verification is skipped.
