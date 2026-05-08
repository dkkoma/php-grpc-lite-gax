# Review Finding: Disable Xdebug Noise By Default

State: closed
Source: test reviewer

## Context

Composer commands emit Xdebug step-debug connection warnings in the container unless `XDEBUG_MODE=off` is passed.

## Impact

Verification output is noisy and can obscure real failures.

## Proposed Fix

Disable Xdebug by default in the Dev Container environment.

## Fix Summary

Set `XDEBUG_MODE=off` in the Dev Container image and remote environment.

## Verification

Rebuilt the Docker image and verified Composer commands run without Xdebug warnings.
