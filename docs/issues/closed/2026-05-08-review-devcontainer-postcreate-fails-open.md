# Review Finding: Dev Container PostCreate Must Fail Openly

State: closed
Source: test reviewer

## Context

`.devcontainer/devcontainer.json` runs `composer install || true`.

## Impact

Dependency installation failures can be hidden, leaving the container ready without `vendor/`.

## Proposed Fix

Remove `|| true` so Dev Container setup fails visibly.

## Fix Summary

Removed `|| true` from `postCreateCommand`.

## Verification

Verified `composer validate --strict`, `composer lint`, and `composer test` in the rebuilt Docker image.
