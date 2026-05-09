# Use GitHub Franken Main For Smoke

State: closed
Source: user instruction

## Context

The user pushed `https://github.com/dkkoma/frankenphp-grpc-go-client` main and asked to use it so this repository can run FrankenPHP smoke tests like the grpc-lite smoke flow.

## Proposed Fix

Add a tool script that clones or updates the GitHub repository, builds the FrankenPHP binary with the `FrankenGrpc` extension, and runs this repository's `franken-smoke` PHPUnit suite through `frankenphp php-cli`. Wire `composer test:franken-smoke` to that script.

## Resolution

Implemented `composer test:franken-smoke` with a GitHub-backed runner for `dkkoma/frankenphp-grpc-go-client` main.

## Verification

- `composer verify`
- `tools/franken-smoke.sh`
