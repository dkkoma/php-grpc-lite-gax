#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEV_IMAGE="${PHP_GRPC_LITE_GAX_DEV_IMAGE:-php-grpc-lite-gax-dev:latest}"
DOCKER_NETWORK="${SPANNER_SMOKE_DOCKER_NETWORK:-php-grpc-lite_default}"
SPANNER_HOST="${SPANNER_EMULATOR_HOST:-php-grpc-lite-spanner-emulator-1:9010}"

docker run --rm \
  --network "$DOCKER_NETWORK" \
  -e "SPANNER_EMULATOR_HOST=$SPANNER_HOST" \
  -v "$ROOT_DIR:/workspace" \
  -w /workspace \
  "$DEV_IMAGE" \
  php -d extension=grpc.so vendor/bin/phpunit --testsuite spanner-smoke

SPANNER_EMULATOR_HOST="$SPANNER_HOST" \
FRANKEN_GRPC_CLIENT_DOCKER_NETWORK="$DOCKER_NETWORK" \
  "$ROOT_DIR/tools/franken-smoke.sh" --testsuite franken-spanner-smoke
