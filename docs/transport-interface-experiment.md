# TransportInterface Experiment Result

## Summary

The `google/gax` `TransportInterface` adapter path works technically, including
unary and server-streaming calls through `php-grpc-lite` and FrankenPHP grpc-go.
It is useful as an integration and behavior experiment, but it is not the best
mainline user experience for google-cloud-php applications.

## Findings

Passing a prebuilt `TransportInterface` object into GAX bypasses the normal GAX
transport construction path. As a result, the adapter must know the endpoint
before GAX can apply default endpoint, emulator, universe domain, mTLS, and
transport config behavior to the custom runtime.

A Composer patch adding a GAX `transportFactory` hook can solve that boundary
problem. The implementation was preserved on the
`experiment/gax-transport-factory-patch` branch. However, requiring application
users to pin `google/gax` and apply Composer patches is operationally heavy and
fragile for normal use.

## Conclusion

Do not make the GAX patch strategy the main integration path for this library.
Keep `GrpcLiteTransport` and `FrankenGrpcTransport` as experimental adapters and
test fixtures for validating behavior against GAX.

For benchmarking or production-oriented runtime switching, prefer moving the
runtime choice below the `Grpc\Channel` / `Grpc\Call` compatible layer. In
practice, that means making `php-grpc-lite` able to use a FrankenPHP grpc-go
backend directly, so google-cloud-php and GAX remain unchanged from the
application's perspective.
