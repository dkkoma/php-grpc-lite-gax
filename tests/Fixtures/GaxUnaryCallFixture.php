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

    public static function serverStreamingCall(string $requestValue = 'request-value'): Call
    {
        return new Call(
            method: 'google.example.v1.ExampleService/ListExamples',
            decodeType: StringValue::class,
            message: new StringValue(['value' => $requestValue]),
            callType: Call::SERVER_STREAMING_CALL,
        );
    }

    /**
     * @return array{
     *     headers: array<string, list<string>>,
     *     timeoutMillis: int
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
        ];
    }

    private static function gaxVersion(): string
    {
        return \Google\ApiCore\Version::getApiCoreVersion();
    }
}
