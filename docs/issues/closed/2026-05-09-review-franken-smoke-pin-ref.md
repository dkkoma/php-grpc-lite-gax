# Pin Franken Smoke Ref

State: closed
Source: reviewer finding

## Context

The test/CI reviewer noted that `tools/franken-smoke.sh` defaulted to the mutable `main` branch and pulled it on every run.

## Impact

Mutable branch defaults make local smoke verification non-reproducible because the tested FrankenPHP extension code can change between runs.

## Proposed Fix

Default the runner to the currently verified GitHub main commit SHA while allowing `FRANKEN_GRPC_CLIENT_REF` to override it. Print the resolved SHA before building.

## Fix Summary

`tools/franken-smoke.sh` now defaults to the verified GitHub commit `7c38b47e2779b8f8f34e5c99ef0d907b16300a16`, still allowing `FRANKEN_GRPC_CLIENT_REF` overrides, and prints the resolved SHA before building.

## Verification

- `tools/franken-smoke.sh`
- `composer verify`
