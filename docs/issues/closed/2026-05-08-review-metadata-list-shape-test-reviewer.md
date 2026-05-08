# Review Finding: Enforce Metadata List Shape In Tests

State: closed
Source: test reviewer

## Context

The test reviewer also identified that metadata was documented as `array<string, list<string>>`, but associative value arrays passed runtime validation.

## Impact

Backends could receive non-list metadata values and serialize them inconsistently.

## Proposed Fix

Reject metadata values that are not PHP lists and add regression coverage.

## Fix Summary

Updated `MetadataValidator` to require `array_is_list($values)` and added regression coverage.

## Verification

- `composer lint`
- `composer test`
