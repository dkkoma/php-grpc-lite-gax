<?php

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace FrankenGrpc {
    if (!class_exists(Channel::class, false)) {
        final class Channel
        {
            /** @var list<self> */
            public static array $instances = [];

            public bool $closed = false;

            /**
             * @param array<string, mixed> $options
             */
            public function __construct(
                public readonly string $target,
                public readonly array $options = [],
            ) {
                self::$instances[] = $this;
            }

            public function close(): void
            {
                $this->closed = true;
            }
        }

        final class UnaryCall
        {
            /** @var list<self> */
            public static array $instances = [];

            public static ?UnaryResult $nextResult = null;

            /** @var list<array{payload: string, metadata: array<string, list<string>>, timeoutSeconds: ?float}> */
            public array $starts = [];

            public bool $cancelled = false;

            public function __construct(
                public readonly Channel $channel,
                public readonly string $method,
            ) {
                self::$instances[] = $this;
            }

            /**
             * @param array<string, list<string>> $metadata
             */
            public function start(string $payload, array $metadata = [], ?float $timeoutSeconds = null): UnaryResult
            {
                $this->starts[] = [
                    'payload' => $payload,
                    'metadata' => $metadata,
                    'timeoutSeconds' => $timeoutSeconds,
                ];

                return self::$nextResult ?? new UnaryResult('default-response', new Status(0));
            }

            public function cancel(): void
            {
                $this->cancelled = true;
            }

            public function getPeer(): string
            {
                return $this->channel->target;
            }
        }

        final class ServerStreamingCall
        {
            /** @var list<self> */
            public static array $instances = [];

            /** @var list<string> */
            public static array $nextResponses = [];

            public static Status $nextStatus;

            /** @var array<string, list<string>> */
            public static array $nextInitialMetadata = [];

            /** @var array<string, list<string>> */
            public static array $nextTrailingMetadata = [];

            /** @var list<array{payload: string, metadata: array<string, list<string>>, timeoutSeconds: ?float}> */
            public array $starts = [];

            public bool $cancelled = false;

            public function __construct(
                public readonly Channel $channel,
                public readonly string $method,
            ) {
                self::$instances[] = $this;
            }

            /**
             * @param array<string, list<string>> $metadata
             */
            public function start(string $payload, array $metadata = [], ?float $timeoutSeconds = null): void
            {
                $this->starts[] = [
                    'payload' => $payload,
                    'metadata' => $metadata,
                    'timeoutSeconds' => $timeoutSeconds,
                ];
            }

            public function read(): ?string
            {
                return array_shift(self::$nextResponses);
            }

            /**
             * @return array<string, list<string>>
             */
            public function getInitialMetadata(): array
            {
                return self::$nextInitialMetadata;
            }

            public function getStatus(): Status
            {
                return self::$nextStatus;
            }

            /**
             * @return array<string, list<string>>
             */
            public function getTrailingMetadata(): array
            {
                return self::$nextTrailingMetadata;
            }

            public function cancel(): void
            {
                $this->cancelled = true;
            }

            public function getPeer(): string
            {
                return $this->channel->target;
            }
        }

        final readonly class UnaryResult
        {
            /**
             * @param array<string, list<string>> $initialMetadata
             * @param array<string, list<string>> $trailingMetadata
             */
            public function __construct(
                public string $payload,
                public Status $status,
                public array $initialMetadata = [],
                public array $trailingMetadata = [],
            ) {
            }
        }

        final readonly class Status
        {
            /**
             * @param array<string, list<string>> $metadata
             */
            public function __construct(
                public int $code,
                public string $details = '',
                public array $metadata = [],
            ) {
            }
        }

        ServerStreamingCall::$nextStatus = new Status(0);
    }
}
