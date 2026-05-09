# Verify php-grpc-lite Install Docs

State: closed
Source: user instruction

## Context

The user pointed out that `php-grpc-lite` is a PHP extension, not a normal Composer library, and asked to verify the actual install procedure before changing README guidance.

## Impact

Publishing an unverified `composer require dkkoma/php-grpc-lite` instruction would mislead users and hide the runtime extension installation requirement.

## Proposed Fix

Verify the current `php-grpc-lite` package metadata and a real installation path, preferably through PIE if supported, then update README with only the verified procedure and note any prerequisites.

## Fix Summary

Verified that `pie install dkkoma/php-grpc-lite` now resolves Packagist release `0.0.1`, builds `grpc.so`, and loads successfully after adding `extension=grpc`. Updated README and Composer suggestion to describe `php-grpc-lite` as a PIE PHP extension package rather than an application Composer library.

## Verification

- Clean `php:8.4-cli-trixie` container with build prerequisites and `unzip`: `pie install dkkoma/php-grpc-lite --skip-enable-extension --no-interaction --no-cache`
- `php -m | grep -x grpc`
- `php -r 'var_dump(extension_loaded("grpc"), defined("Grpc\\\\VERSION"), defined("Grpc\\\\VERSION") ? constant("Grpc\\\\VERSION") : null);'`
