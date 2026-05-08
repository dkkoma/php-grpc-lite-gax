<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Fixtures;

use Google\ApiCore\Call;
use Google\Protobuf\StringValue;

final class GaxUnaryCallFixture
{
    public static function call(string $requestValue = 'request-value'): Call
    {
        return new Call(
            method: 'google.example.v1.ExampleService/GetExample',
            decodeType: StringValue::class,
            message: new StringValue(['value' => $requestValue]),
        );
    }

    /**
     * @return array{
     *     headers: array<string, list<string>>,
     *     timeoutMillis: int,
     *     retryAttempt: int,
     *     serviceName: string
     * }
     */
    public static function options(): array
    {
        return [
            'headers' => [
                'x-goog-request-params' => ['name=projects/example/locations/global'],
                'x-goog-api-client' => ['gapic/0.0.0 gax/' . self::gaxVersion()],
            ],
            'timeoutMillis' => 1500,
            'retryAttempt' => 0,
            'serviceName' => 'google.example.v1.ExampleService',
        ];
    }

    private static function gaxVersion(): string
    {
        return \Google\ApiCore\Version::getApiCoreVersion();
    }
}
