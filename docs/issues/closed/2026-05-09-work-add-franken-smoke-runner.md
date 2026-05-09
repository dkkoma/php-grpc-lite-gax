# Add Franken Smoke Runner

State: closed
Source: work unit

## Context

`franken-smoke` currently assumes a real FrankenPHP binary is already available and does not fetch or build the extension from the GitHub repository.

## Proposed Fix

Add `tools/franken-smoke.sh` that uses the GitHub main branch by default, builds the extension's FrankenPHP binary in Docker, and runs `vendor/bin/phpunit --testsuite franken-smoke` using `frankenphp php-cli`.

## Resolution

Added `tools/franken-smoke.sh` and wired it into Composer scripts, including `verify:smoke`.

## Verification

- `composer verify`
- `tools/franken-smoke.sh`
