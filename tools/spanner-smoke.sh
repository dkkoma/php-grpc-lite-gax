#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEV_IMAGE="${PHP_GRPC_LITE_GAX_DEV_IMAGE:-php-grpc-lite-gax-dev:latest}"
DOCKER_NETWORK="${SPANNER_SMOKE_DOCKER_NETWORK:-php-grpc-lite_default}"
if [[ -z "${SPANNER_EMULATOR_HOST:-}" ]]; then
  printf 'SPANNER_EMULATOR_HOST is required for tools/spanner-smoke.sh.\n' >&2
  exit 1
fi

SPANNER_HOST="$SPANNER_EMULATOR_HOST"

run_grpc_lite_spanner() {
  docker run --rm \
    --network "$DOCKER_NETWORK" \
    -e "SPANNER_EMULATOR_HOST=$SPANNER_HOST" \
    -v "$ROOT_DIR:/workspace" \
    -w /workspace \
    "$DEV_IMAGE" \
    php -d extension=grpc.so vendor/bin/phpunit --testsuite spanner-smoke "$@"
}

run_franken_spanner() {
  SPANNER_EMULATOR_HOST="$SPANNER_HOST" \
  FRANKEN_GRPC_CLIENT_DOCKER_NETWORK="$DOCKER_NETWORK" \
    "$ROOT_DIR/tools/franken-smoke.sh" --testsuite franken-spanner-smoke "$@"
}

run_grpc_lite_spanner "$@"
run_franken_spanner "$@"
