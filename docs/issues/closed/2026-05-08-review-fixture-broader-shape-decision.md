# Review Finding: Decide Fixture Option Shape

State: closed
Source: test reviewer design decision

## Context

The GAX-like fixture includes broader GAX options such as `retryAttempt` and `serviceName`.

## Impact

The fixture may imply coverage for behavior that is not asserted.

## Proposed Fix

Make the fixture reflect only currently modeled transport options.

## Fix Summary

Kept the fixture scoped to currently modeled transport options: headers and timeout.

## Verification

- `composer lint`
- `composer test`
