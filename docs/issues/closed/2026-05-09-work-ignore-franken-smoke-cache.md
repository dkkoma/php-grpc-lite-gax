# Ignore Franken Smoke Cache

State: closed
Source: work unit

## Context

`tools/franken-smoke.sh` stores the cloned FrankenPHP extension repository under `.cache/frankenphp-grpc-go-client`, which should not appear as an untracked project change after smoke runs.

## Impact

Generated clone and build output should not pollute `git status` after normal contributor workflows.

## Proposed Fix

Ignore the repository-local `.cache/` directory.

## Fix Summary

Ignore the repository-local `.cache/` directory.

## Verification

- `git status --short`
