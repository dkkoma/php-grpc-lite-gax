# Clean Franken Smoke Cache

State: closed
Source: reviewer finding

## Context

The test/CI reviewer noted that `tools/franken-smoke.sh` reuses a mutable Git checkout cache without resetting or cleaning it before Docker builds.

## Impact

Untracked files or failed build output in the cache can affect later smoke runs and make failures hard to interpret.

## Proposed Fix

Reset the cached checkout to the resolved ref and clean untracked or ignored files before building.

## Fix Summary

`tools/franken-smoke.sh` now resets the checkout to the resolved ref and runs `git clean -ffdx` before building the Docker image.

## Verification

- `tools/franken-smoke.sh`
- `composer verify`
