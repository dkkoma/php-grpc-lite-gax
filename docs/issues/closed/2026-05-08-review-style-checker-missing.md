# Review Finding: Style Checker Missing From Lint

State: closed
Source: test reviewer

## Context

`AGENTS.md` says `composer lint` runs static analysis and style checks, but `composer lint` only runs PHPStan.

## Impact

PSR-12 is documented but not enforced.

## Proposed Fix

Add a style checker and include it in Composer scripts, or narrow the contributor guidance.

## Fix Summary

Added PHP_CodeSniffer, `phpcs.xml.dist`, `composer cs-check`, `composer cs-fix`, and included PHPCS in `composer lint`.

## Verification

- `composer lint`
