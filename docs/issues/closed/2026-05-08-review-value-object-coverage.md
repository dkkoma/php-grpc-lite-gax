# Review Finding: Add Value Object Coverage

State: closed
Source: test reviewer

## Context

`UnaryRequest` and `UnaryResponse` invariants had incomplete focused tests.

## Impact

Future refactors could weaken boundary validation without a targeted regression.

## Proposed Fix

Move value-object behavior into focused tests.

## Fix Summary

Added `UnaryRequestTest` and `UnaryResponseTest`; removed unrelated path coverage from `FakeBackendTest`.

## Verification

- `composer test`
