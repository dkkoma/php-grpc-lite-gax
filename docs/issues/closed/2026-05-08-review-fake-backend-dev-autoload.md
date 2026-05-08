# Review Finding: Keep FakeBackend Test-Only

State: closed
Source: test reviewer design decision

## Context

`FakeBackend` is under `tests/Support` and only available through `autoload-dev`.

## Impact

Consumers of the package will not receive `FakeBackend` as a public test utility.

## Proposed Fix

Accept this boundary for now, because FakeBackend is intended for repository tests only.

## Fix Summary

Kept `FakeBackend` in `tests/Support` and documented the planned concrete backends in `docs/design.md`.

## Verification

Reviewed `composer.json` autoload and autoload-dev mappings.
