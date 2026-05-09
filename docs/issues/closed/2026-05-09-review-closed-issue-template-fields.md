# Normalize Closed Issue Fields

State: closed
Source: reviewer finding

## Context

The domain-model reviewer noted that the new closed issue files did not preserve all fields from `docs/issues/template.md`.

## Impact

Closed issues should retain the same lifecycle fields as open issues so reviewer findings, work units, and user instructions remain consistently auditable.

## Proposed Fix

Add missing `Impact` sections, keep `Proposed Fix`, replace `Resolution` with `Fix Summary`, and preserve verification notes.

## Fix Summary

Normalized the new closed issue files to include `Impact`, `Proposed Fix`, `Fix Summary`, and `Verification` sections.

## Verification

- domain-model reviewer finding addressed
- `composer verify`
