<?php

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace Grpc {
    if (!class_exists(Channel::class, false)) {
        \define('Grpc\\OP_SEND_INITIAL_METADATA', 0);
        \define('Grpc\\OP_SEND_MESSAGE', 1);
        \define('Grpc\\OP_SEND_CLOSE_FROM_CLIENT', 2);
        \define('Grpc\\OP_RECV_INITIAL_METADATA', 3);
        \define('Grpc\\OP_RECV_MESSAGE', 4);
        \define('Grpc\\OP_RECV_STATUS_ON_CLIENT', 5);

        final class ChannelCredentials
        {
            public function __construct(public readonly string $type)
            {
            }

            public static function createSsl(): self
            {
                return new self('ssl');
            }
        }

        final class Timeval
        {
            public function __construct(public readonly int $microseconds)
            {
            }

            public static function now(): self
            {
                return new self(1_000_000);
            }

            public static function infFuture(): self
            {
                return new self(PHP_INT_MAX);
            }

            public function add(self $other): self
            {
                return new self($this->microseconds + $other->microseconds);
            }
        }

        final class Channel
        {
            /** @var list<self> */
            public static array $instances = [];

            public bool $closed = false;

            /**
             * @param array<string, mixed> $opts
             */
            public function __construct(
                public readonly string $target,
                public readonly array $opts,
            ) {
                self::$instances[] = $this;
            }

            public function close(): void
            {
                $this->closed = true;
            }
        }

        final class Call
        {
            /** @var list<self> */
            public static array $instances = [];

            public static ?object $nextReceiveEvent = null;

            /** @var list<array<int, mixed>> */
            public array $batches = [];

            public function __construct(
                public readonly Channel $channel,
                public readonly string $method,
                public readonly Timeval $deadline,
            ) {
                self::$instances[] = $this;
            }

            /**
             * @param array<int, mixed> $ops
             */
            public function startBatch(array $ops): object
            {
                $this->batches[] = $ops;

                if (array_key_exists(OP_RECV_STATUS_ON_CLIENT, $ops)) {
                    return self::$nextReceiveEvent ?? (object) [
                        'metadata' => [],
                        'message' => '',
                        'status' => (object) ['code' => 0, 'details' => '', 'metadata' => []],
                    ];
                }

                return new \stdClass();
            }
        }
    }
}
