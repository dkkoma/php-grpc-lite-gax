# README PIE Enable Path

State: closed
Source: reviewer finding

## Context

The test/CI docs reviewer found that the README documents plain `pie install dkkoma/php-grpc-lite` followed by a manual ini write, while the verified command used `--skip-enable-extension`.

## Impact

If PIE enables the extension automatically, a subsequent manual `extension=grpc` line can cause duplicate loading.

## Proposed Fix

Document the verified `--skip-enable-extension` path when showing manual ini configuration.

## Fix Summary

README now documents the verified `pie install dkkoma/php-grpc-lite --skip-enable-extension --no-interaction` path before showing the manual `extension=grpc` ini line.

## Verification

- Clean `php:8.4-cli-trixie` container with build prerequisites and `unzip`: `pie install dkkoma/php-grpc-lite --skip-enable-extension --no-interaction --no-cache`
- `composer verify`
