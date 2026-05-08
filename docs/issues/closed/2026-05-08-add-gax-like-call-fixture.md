# Add GAX-Like Call Fixture

State: closed
Source: work unit

## Context

Transport tests currently construct GAX `Call` objects inline.

## Impact

Inline construction hides which options are meant to represent typical GAPIC/GAX unary calls and makes future transport tests less consistent.

## Proposed Fix

Add a reusable test fixture for a GAX-like unary call and options, then use it in transport tests.

## Fix Summary

Added `GaxUnaryCallFixture` and updated transport tests to use a reusable GAX-like unary call and options.

## Verification

- `composer lint`
- `composer test`
