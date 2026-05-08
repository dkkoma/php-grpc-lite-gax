# User Instruction: PHP 8.4+ Dev Container

State: closed
Source: user instruction

## Context

The host environment does not provide PHP or Composer, and the library should target PHP 8.4+.

## Impact

Without a Dev Container, implementation and verification would depend on host setup.

## Proposed Fix

Provide a PHP 8.4+ Dev Container with Composer.

## Fix Summary

Added `.devcontainer/Dockerfile` and `.devcontainer/devcontainer.json`; updated the Dockerfile to remove the stale Yarn apt source before package installation.

## Verification

Built the Docker image and used it to run Composer, PHPStan, and PHPUnit.
