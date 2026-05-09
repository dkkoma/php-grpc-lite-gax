<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Transport;

use FrankenGrpc\Channel;
use FrankenGrpc\UnaryCall;
use FrankenGrpc\UnaryResult;
use FrankenGrpc\Status;
use Google\ApiCore\Call;
use Google\Protobuf\StringValue;
use GrpcLiteGax\Transport\FrankenGrpcTransport;
use PHPUnit\Framework\TestCase;

final class FrankenGrpcTransportTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        Channel::$instances = [];
        UnaryCall::$instances = [];
        UnaryCall::$nextResult = null;
    }

    public function testBuildCreatesTransportBackedByFrankenNativeBridge(): void
    {
        UnaryCall::$nextResult = new UnaryResult(
            payload: (new StringValue(['value' => 'response-value']))->serializeToString(),
            status: new Status(0),
        );

        $transport = FrankenGrpcTransport::build('localhost:50051', ['plaintext' => true]);
        $response = $transport->startUnaryCall(
            new Call(
                method: 'service.v1.Service/Method',
                decodeType: StringValue::class,
                message: new StringValue(['value' => 'request-value']),
            ),
            [],
        )->wait();

        self::assertInstanceOf(StringValue::class, $response);
        self::assertSame('response-value', $response->getValue());
        self::assertSame('localhost:50051', Channel::$instances[0]->target);
        self::assertSame(['plaintext' => true], Channel::$instances[0]->options);
        self::assertSame('/service.v1.Service/Method', UnaryCall::$instances[0]->method);
    }
}
