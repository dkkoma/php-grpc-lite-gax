#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPO_URL="${FRANKEN_GRPC_CLIENT_REPO:-https://github.com/dkkoma/frankenphp-grpc-go-client.git}"
REPO_REF="${FRANKEN_GRPC_CLIENT_REF:-7c38b47e2779b8f8f34e5c99ef0d907b16300a16}"
CACHE_DIR="${FRANKEN_GRPC_CLIENT_DIR:-$ROOT_DIR/.cache/frankenphp-grpc-go-client}"
IMAGE="${FRANKEN_GRPC_CLIENT_IMAGE:-php-grpc-lite-gax-franken-builder:latest}"

mkdir -p "$(dirname "$CACHE_DIR")"

if [[ ! -d "$CACHE_DIR/.git" ]]; then
  git clone "$REPO_URL" "$CACHE_DIR"
else
  git -C "$CACHE_DIR" remote set-url origin "$REPO_URL"
  git -C "$CACHE_DIR" fetch origin
fi

if [[ "$REPO_REF" == "main" ]]; then
  git -C "$CACHE_DIR" checkout main
  git -C "$CACHE_DIR" pull --ff-only origin main
else
  git -C "$CACHE_DIR" checkout --detach "$REPO_REF"
fi
RESOLVED_REF="$(git -C "$CACHE_DIR" rev-parse HEAD)"
git -C "$CACHE_DIR" reset --hard "$RESOLVED_REF"
git -C "$CACHE_DIR" clean -ffdx
printf 'Using frankenphp-grpc-go-client %s\n' "$RESOLVED_REF"

docker build -t "$IMAGE" "$CACHE_DIR"

docker run --rm \
  -v "$CACHE_DIR:/extension" \
  -v "$ROOT_DIR:/consumer" \
  -w /extension \
  "$IMAGE" \
  make build-frankenphp

docker run --rm \
  -v "$CACHE_DIR:/extension" \
  -v "$ROOT_DIR:/consumer" \
  -w /consumer \
  "$IMAGE" \
  /extension/bin/frankenphp php-cli vendor/bin/phpunit --testsuite franken-smoke
