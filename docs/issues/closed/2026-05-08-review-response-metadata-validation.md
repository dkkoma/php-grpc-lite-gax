# Review Finding: Cover UnaryResponse Metadata Validation

State: closed
Source: test reviewer

## Context

`UnaryResponse` metadata validation lacks direct negative coverage.

## Impact

Response value-object validation can regress asymmetrically from request validation.

## Proposed Fix

Add a direct negative test for invalid response metadata.

## Fix Summary

Added direct negative coverage for invalid `UnaryResponse` metadata.

## Verification

- `composer test`
