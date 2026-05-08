# Review Finding: Cover Invalid Metadata Callback

State: closed
Source: test reviewer

## Context

The non-callable `metadataCallback` validation branch is untested.

## Impact

Invalid callback handling can regress without test coverage.

## Proposed Fix

Add a test for non-callable `metadataCallback`.

## Fix Summary

Added a test for non-callable `metadataCallback`.

## Verification

- `composer test`
