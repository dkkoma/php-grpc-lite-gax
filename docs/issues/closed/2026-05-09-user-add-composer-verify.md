# Add Composer Verify Command

State: closed
Source: user instruction

## Context

The user accepted keeping unit coverage stub-based and native smoke separate, and asked to add a single command that runs both paths together.

## Impact

Contributors need one stable command that validates metadata, static analysis/style, unit coverage, and the real `php-grpc-lite` native smoke check.

## Proposed Fix

Add a `composer verify` script that runs `validate-project`, `lint`, `test:coverage`, and `test:native-smoke`.

## Fix Summary

Added a `composer verify` script that runs `validate-project`, `lint`, `test:coverage`, and `test:native-smoke` in order.

## Verification

Verified with `composer verify`; it passed metadata validation, PHPStan/PHPCS, 100% unit coverage, and the native `php-grpc-lite` smoke test.
