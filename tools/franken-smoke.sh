#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPO_URL="${FRANKEN_GRPC_CLIENT_REPO:-https://github.com/dkkoma/frankenphp-grpc-go-client.git}"
REPO_REF="${FRANKEN_GRPC_CLIENT_REF:-main}"
CACHE_DIR="${FRANKEN_GRPC_CLIENT_DIR:-$ROOT_DIR/.cache/frankenphp-grpc-go-client}"
IMAGE="${FRANKEN_GRPC_CLIENT_IMAGE:-php-grpc-lite-gax-franken-builder:latest}"

mkdir -p "$(dirname "$CACHE_DIR")"

if [[ ! -d "$CACHE_DIR/.git" ]]; then
  git clone "$REPO_URL" "$CACHE_DIR"
else
  git -C "$CACHE_DIR" remote set-url origin "$REPO_URL"
  git -C "$CACHE_DIR" fetch origin
fi

git -C "$CACHE_DIR" checkout "$REPO_REF"
if [[ "$REPO_REF" == "main" ]]; then
  git -C "$CACHE_DIR" pull --ff-only origin main
fi

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
