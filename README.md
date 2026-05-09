# php-grpc-lite-gax

`dkkoma/php-grpc-lite-gax` provides `google/gax` `TransportInterface`
implementations for lightweight gRPC runtimes:

- `GrpcLiteTransport` for the `dkkoma/php-grpc-lite` low-level `Grpc\*` extension.
- `FrankenGrpcTransport` for the FrankenPHP `FrankenGrpc\*` grpc-go bridge.

The transports support unary and server-streaming calls. They are intended for
generated google-cloud-php GAPIC clients that accept a `transport` option.
With the bundled GAX patch applied, applications can select the runtime with a
`transportFactory` option while google-cloud-php keeps ownership of default
endpoints and emulator environment variables.

## Installation

```sh
composer require dkkoma/php-grpc-lite-gax google/gax
```

This package can run against `google/gax` `^1.42.3` through the low-level
`GrpcLiteTransport::build()` and `FrankenGrpcTransport::build()` APIs. The
recommended `transportFactory` integration is patch-verified against
`google/gax` 1.42.3. In an application root that wants the factory integration,
pin that GAX version, install a Composer patch plugin, and add this patch
configuration:

```json
{
  "require": {
    "google/gax": "1.42.3",
    "cweagans/composer-patches": "^2.0"
  },
  "config": {
    "allow-plugins": {
      "cweagans/composer-patches": true
    }
  },
  "extra": {
    "patches": {
      "google/gax": {
        "Add transportFactory option for custom TransportInterface construction": "vendor/dkkoma/php-grpc-lite-gax/patches/google-gax-transport-factory.patch"
      }
    }
  }
}
```

For the grpc-lite backend, install and load the runtime provider:

```sh
pie install dkkoma/php-grpc-lite --skip-enable-extension --no-interaction
echo 'extension=grpc' > /path/to/php/conf.d/20-php-grpc-lite.ini
php -m | grep -x grpc
php -r 'var_dump(extension_loaded("grpc"), defined("Grpc\\VERSION"), defined("Grpc\\VERSION") ? constant("Grpc\\VERSION") : null);'
```

`dkkoma/php-grpc-lite` is a PIE PHP extension package, not an application
Composer library. It builds a PHP module named `grpc`, so do not load it at the
same time as the official `ext-grpc` module. The official `ext-grpc` module is
not a valid runtime provider for `GrpcLiteTransport`; verify the provider with
the `Grpc\VERSION` check above. PIE needs a normal PHP extension build toolchain
and an archive extractor such as PHP `zip`, `unzip`, or `7z`.

For the FrankenPHP backend, run PHP through a FrankenPHP binary that includes
the `FrankenGrpc` extension from `dkkoma/frankenphp-grpc-go-client`.

## Choosing a Runtime

```php
use GrpcLiteGax\GaxTransportFactory;

$factory = GaxTransportFactory::forBackend(
    getenv('GOOGLE_CLOUD_GRPC_BACKEND') ?: GaxTransportFactory::BACKEND_GRPC_LITE,
);
```

Supported backend values are:

- `default`: do not change GAX transport construction.
- `grpc-lite`: build `GrpcLiteTransport` from the endpoint resolved by GAX.
- `frankenphp-grpc-go`: build `FrankenGrpcTransport` from the endpoint resolved by GAX.

Do not configure normal Google Cloud endpoints in this package. GAX and
google-cloud-php continue to resolve defaults such as `spanner.googleapis.com:443`
and emulator variables such as `SPANNER_EMULATOR_HOST` and `PUBSUB_EMULATOR_HOST`.
The patched callable signature is
`function (string $transport, string $apiEndpoint, array $transportConfig, array $context): TransportInterface`;
`GaxTransportFactory` supplies that callable for this package's supported
backends.

## google-cloud-php Generated Clients

Generated clients accept the patched `transportFactory` option. This example
uses the generated Spanner GAPIC client from `google/cloud-spanner`. For
emulator usage, set `SPANNER_EMULATOR_HOST`; do not duplicate it in code.

```php
use Google\Cloud\Spanner\V1\Client\SpannerClient;
use Google\Cloud\Spanner\V1\CreateSessionRequest;
use GrpcLiteGax\GaxTransportFactory;

$database = 'projects/test-project/instances/test-instance/databases/test-db';

$spanner = new SpannerClient([
    'transport' => 'grpc',
    'transportFactory' => GaxTransportFactory::forBackend('grpc-lite'),
]);

$session = $spanner->createSession(
    (new CreateSessionRequest())->setDatabase($database),
);
```

Switching to FrankenPHP only changes the backend selection:

```php
$spanner = new SpannerClient([
    'transport' => 'grpc',
    'transportFactory' => GaxTransportFactory::forBackend('frankenphp-grpc-go'),
]);
```

The handwritten `Google\Cloud\Spanner\SpannerClient` currently checks that the
`grpc` extension is loaded before constructing its internal clients. Use the
generated GAPIC clients directly when running only the FrankenPHP `FrankenGrpc`
extension.

## Laravel Example

Expose backend selection in configuration and pass the factory into each
google-cloud-php client you create. Do not bind one global `TransportInterface`;
the factory lets GAX resolve the correct endpoint per client.

```php
<?php

namespace App\Providers;

use Google\Cloud\Spanner\V1\Client\SpannerClient;
use Google\Cloud\PubSub\PubSubClient;
use GrpcLiteGax\GaxTransportFactory;
use Illuminate\Support\ServiceProvider;

final class GoogleCloudServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SpannerClient::class, function (): SpannerClient {
            return new SpannerClient([
                'transport' => 'grpc',
                'transportFactory' => GaxTransportFactory::forBackend(
                    config('google_cloud.grpc_backend', GaxTransportFactory::BACKEND_GRPC_LITE),
                ),
            ]);
        });

        $this->app->singleton(PubSubClient::class, function (): PubSubClient {
            return new PubSubClient([
                'transport' => 'grpc',
                'transportFactory' => GaxTransportFactory::forBackend(
                    config('google_cloud.grpc_backend', GaxTransportFactory::BACKEND_GRPC_LITE),
                ),
            ]);
        });
    }
}
```

Example `config/google_cloud.php` entry:

```php
return [
    'grpc_backend' => env('GOOGLE_CLOUD_GRPC_BACKEND', 'grpc-lite'),
];
```

Use the clients through dependency injection:

```php
use Google\Cloud\Spanner\V1\Client\SpannerClient;
use Google\Cloud\PubSub\PubSubClient;

final class ListSessions
{
    public function __construct(
        private SpannerClient $spanner,
        private PubSubClient $pubsub,
    )
    {
    }
}
```

## Development and Smoke Tests

```sh
composer verify
composer test:franken-smoke
SPANNER_SMOKE_DOCKER_NETWORK=php-grpc-lite_default \
SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 \
  composer test:spanner-smoke
```

`composer test:spanner-smoke` runs the Spanner emulator scenario twice: once
with `GrpcLiteTransport` and once with `FrankenGrpcTransport`.

When the emulator is published on the host instead of attached to the Docker
network used by the smoke containers, set `SPANNER_EMULATOR_HOST` to a
container-reachable host such as `host.docker.internal:9010`.

To run all smoke suites:

```sh
PUBSUB_EMULATOR_HOST=localhost:8085 \
SPANNER_SMOKE_DOCKER_NETWORK=php-grpc-lite_default \
SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 \
  composer verify:smoke
```

`PUBSUB_EMULATOR_HOST` is read by the PHP process running Composer, so use a
host-reachable address. `SPANNER_EMULATOR_HOST` is passed into Docker smoke
containers by `tools/spanner-smoke.sh`, so use an address reachable on
`SPANNER_SMOKE_DOCKER_NETWORK`.
