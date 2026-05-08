# User Instruction: Add PHPStan At High Level

State: closed
Source: user instruction

## Context

The user requested PHPStan with as high a level as practical.

## Impact

Without static analysis, the backend and transport boundary could accumulate weak typing before the public API stabilizes.

## Proposed Fix

Add PHPStan and configure it at the maximum level.

## Fix Summary

Added `phpstan/phpstan`, `phpstan.neon` with `level: max`, and `composer lint`.

## Verification

- `composer lint`
