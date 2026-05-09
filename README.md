# php-grpc-lite-gax

`dkkoma/php-grpc-lite-gax` provides `google/gax` `TransportInterface`
implementations for lightweight gRPC runtimes:

- `GrpcLiteTransport` for the `dkkoma/php-grpc-lite` low-level `Grpc\*` extension.
- `FrankenGrpcTransport` for the FrankenPHP `FrankenGrpc\*` grpc-go bridge.

The transports support unary and server-streaming calls. They are intended for
generated google-cloud-php GAPIC clients that accept a `transport` option.

This repository is an experiment around the GAX `TransportInterface` boundary.
The key finding is that when a prebuilt `TransportInterface` object is passed
to GAX, the client-resolved `apiEndpoint` is not available to that transport.
That prevents a custom runtime from reusing google-cloud-php's normal default
endpoint, emulator, universe domain, and mTLS resolution without either asking
users for the endpoint or patching GAX's transport construction path.

A GAX `transportFactory` patch was implemented to validate the idea, but that
approach was archived as too heavy for normal application operation. See
[`docs/transport-interface-experiment.md`](docs/transport-interface-experiment.md)
for the conclusion and recommended next direction.

## Installation

```sh
composer require dkkoma/php-grpc-lite-gax google/gax
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

## Choosing a Transport

```php
use GrpcLiteGax\Transport\FrankenGrpcTransport;
use GrpcLiteGax\Transport\GrpcLiteTransport;
use Grpc\ChannelCredentials;

$endpoint = 'spanner.googleapis.com:443';

$grpcLite = GrpcLiteTransport::build($endpoint, [
    'credentials' => ChannelCredentials::createSsl(),
]);

$franken = FrankenGrpcTransport::build($endpoint);
```

For local emulators, use plaintext/insecure transport:

```php
$endpoint = getenv('SPANNER_EMULATOR_HOST') ?: 'localhost:9010';

$grpcLite = GrpcLiteTransport::build($endpoint, [
    'credentials' => ChannelCredentials::createInsecure(),
]);

$franken = FrankenGrpcTransport::build($endpoint, ['plaintext' => true]);
```

## google-cloud-php Generated Clients

Generated clients accept a `TransportInterface` object through the `transport`
option. This example uses the generated Spanner GAPIC client from
`google/cloud-spanner`.

```php
use Google\ApiCore\InsecureCredentialsWrapper;
use Google\Cloud\Spanner\V1\Client\SpannerClient;
use Google\Cloud\Spanner\V1\CreateSessionRequest;
use Grpc\ChannelCredentials;
use GrpcLiteGax\Transport\GrpcLiteTransport;

$endpoint = getenv('SPANNER_EMULATOR_HOST') ?: 'localhost:9010';
$database = 'projects/test-project/instances/test-instance/databases/test-db';

$spanner = new SpannerClient([
    'apiEndpoint' => $endpoint,
    'credentials' => new InsecureCredentialsWrapper(),
    'transport' => GrpcLiteTransport::build($endpoint, [
        'credentials' => ChannelCredentials::createInsecure(),
    ]),
    'disableRetries' => true,
]);

$session = $spanner->createSession(
    (new CreateSessionRequest())->setDatabase($database),
);
```

Switching to FrankenPHP only changes the transport:

```php
use GrpcLiteGax\Transport\FrankenGrpcTransport;

$spanner = new SpannerClient([
    'apiEndpoint' => $endpoint,
    'credentials' => new InsecureCredentialsWrapper(),
    'transport' => FrankenGrpcTransport::build($endpoint, ['plaintext' => true]),
    'disableRetries' => true,
]);
```

The handwritten `Google\Cloud\Spanner\SpannerClient` currently checks that the
`grpc` extension is loaded before constructing its internal clients. Use the
generated GAPIC clients directly when running only the FrankenPHP `FrankenGrpc`
extension.

## Laravel Example

Bind the transport and generated client in a service provider. The same pattern
works for other generated google-cloud-php clients.

```php
<?php

namespace App\Providers;

use Google\ApiCore\InsecureCredentialsWrapper;
use Google\Cloud\Spanner\V1\Client\SpannerClient;
use Grpc\ChannelCredentials;
use GrpcLiteGax\Transport\FrankenGrpcTransport;
use GrpcLiteGax\Transport\GrpcLiteTransport;
use Illuminate\Support\ServiceProvider;

final class GoogleCloudServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SpannerClient::class, function (): SpannerClient {
            $endpoint = config('services.spanner.endpoint', 'spanner.googleapis.com:443');
            $backend = config('services.spanner.grpc_backend', 'grpc-lite');
            $emulator = (bool) config('services.spanner.emulator', false);

            $transport = $backend === 'franken'
                ? FrankenGrpcTransport::build($endpoint, $emulator ? ['plaintext' => true] : [])
                : GrpcLiteTransport::build($endpoint, [
                    'credentials' => $emulator
                        ? ChannelCredentials::createInsecure()
                        : ChannelCredentials::createSsl(),
                ]);

            $options = [
                'apiEndpoint' => $endpoint,
                'transport' => $transport,
            ];

            if ($emulator) {
                $options['credentials'] = new InsecureCredentialsWrapper();
            }

            return new SpannerClient($options);
        });
    }
}
```

Example `config/services.php` entries:

```php
'spanner' => [
    'endpoint' => env('SPANNER_EMULATOR_HOST', 'spanner.googleapis.com:443'),
    'emulator' => env('SPANNER_EMULATOR', false),
    'grpc_backend' => env('GOOGLE_CLOUD_GRPC_BACKEND', 'grpc-lite'),
],
```

Use the generated client through dependency injection:

```php
use Google\Cloud\Spanner\V1\Client\SpannerClient;

final class ListSessions
{
    public function __construct(private SpannerClient $spanner)
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
