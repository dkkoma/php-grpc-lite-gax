# Auth Metadata Authorization Override Is Not Case-Insensitive

State: closed
Source: reviewer finding

## Context

Domain model re-review found that `AbstractGrpcTransport::headersWithCredentials()` preserves user-provided authorization metadata only when the header key is exactly `Authorization` or `authorization`. gRPC metadata names are normalized case-insensitively later, so a caller-provided `AUTHORIZATION` header is treated as absent during credential injection and then merged into the same lowercase `authorization` metadata key.

## Impact

Authenticated google-cloud-php calls can send both caller-provided and credentials-wrapper authorization values. That violates the transport invariant that explicit user metadata should not be overridden or duplicated by automatic auth metadata.

## Proposed Fix

Detect existing authorization metadata case-insensitively before invoking `credentialsWrapper->getAuthorizationHeaderCallback()`. Add regression coverage for an uppercase or mixed-case authorization header.

## Fix Summary

Added case-insensitive authorization metadata detection in `AbstractGrpcTransport::headersWithCredentials()` and regression coverage for uppercase `AUTHORIZATION`.

## Verification

Verified with `composer test`, `composer lint`, and `composer test:coverage`.
