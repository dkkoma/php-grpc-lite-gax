# Review Finding: Cover Transport Validation Branches

State: closed
Source: test reviewer

## Context

Transport tests do not cover unsupported streaming calls, malformed method names, non-protobuf request messages, invalid headers, invalid timeout values, or invalid decode types.

## Impact

Transport boundary validation can regress without targeted tests.

## Proposed Fix

Add PHPUnit coverage for these validation and unsupported-call branches.

## Fix Summary

Added PHPUnit coverage for unsupported streaming, malformed method names, non-protobuf request messages, invalid headers, invalid timeouts, and invalid decode types.

## Verification

- `composer test`
