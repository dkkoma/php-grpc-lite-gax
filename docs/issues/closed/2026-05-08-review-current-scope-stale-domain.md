# Review Finding: Current Scope Stale After Franken Implementation

State: closed
Source: domain model reviewer

## Context

`docs/design.md` current scope still focuses on fake-backed transport tests despite `FrankenGrpcBackend` being implemented.

## Impact

Contributors can misread Franken backend support as planned rather than current.

## Proposed Fix

Update current scope to include Franken backend implementation and GrpcLite design-only state.

## Fix Summary

Updated `docs/design.md` current scope to include Franken backend, Franken bridge boundary, PHPCS, backend contract tests, and GrpcLite design-only state.

## Verification

Reviewed `docs/design.md`.
