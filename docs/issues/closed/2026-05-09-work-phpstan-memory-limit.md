# Raise PHPStan Memory Limit

State: closed
Source: work unit

## Context

After adding the GrpcLite backend and test stubs, `composer lint` crashed because PHPStan reached the configured PHP memory limit of 128M.

## Impact

The repository's stable lint entry point must be reliable in the Dev Container, especially with PHPStan level max.

## Proposed Fix

Update the Composer lint script to run PHPStan with an explicit memory limit.

## Fix Summary

Updated the Composer `lint` script to run PHPStan with `--memory-limit=1G`.

## Verification

Fixed in `20763ad`. Verified with `composer lint`.
