# Review Finding: Cover All Unsupported Streaming Methods

State: closed
Source: test reviewer

## Context

Tests cover only server-streaming rejection.

## Impact

Bidi and client-streaming unsupported behavior can regress unnoticed.

## Proposed Fix

Add tests for bidi-streaming and client-streaming rejection.

## Fix Summary

Added tests for unsupported client-streaming and bidi-streaming calls.

## Verification

- `composer test`
