# Review Finding: PHPUnit Coverage Config Missing

State: closed
Source: test reviewer

## Context

`phpunit.xml.dist` defines test suites but no coverage source/reporting.

## Impact

`composer test` cannot produce coverage signal.

## Proposed Fix

Add PHPUnit source coverage configuration for `src`.

## Fix Summary

Added PHPUnit source configuration for `src`.

## Verification

- `composer test`
