<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Integration\Spanner;

use Google\ApiCore\ApiException;
use Google\ApiCore\InsecureCredentialsWrapper;
use Google\ApiCore\OperationResponse;
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\CreateDatabaseRequest;
use Google\Cloud\Spanner\Admin\Database\V1\DropDatabaseRequest;
use Google\Cloud\Spanner\Admin\Instance\V1\Client\InstanceAdminClient;
use Google\Cloud\Spanner\Admin\Instance\V1\CreateInstanceRequest;
use Google\Cloud\Spanner\Admin\Instance\V1\DeleteInstanceRequest;
use Google\Cloud\Spanner\Admin\Instance\V1\Instance;
use Google\Cloud\Spanner\V1\BeginTransactionRequest;
use Google\Cloud\Spanner\V1\Client\SpannerClient;
use Google\Cloud\Spanner\V1\CommitRequest;
use Google\Cloud\Spanner\V1\CreateSessionRequest;
use Google\Cloud\Spanner\V1\DeleteSessionRequest;
use Google\Cloud\Spanner\V1\ExecuteSqlRequest;
use Google\Cloud\Spanner\V1\PartialResultSet;
use Google\Cloud\Spanner\V1\ResultSet;
use Google\Cloud\Spanner\V1\Transaction;
use Google\Cloud\Spanner\V1\TransactionOptions;
use Google\Cloud\Spanner\V1\TransactionOptions\ReadWrite;
use Google\Cloud\Spanner\V1\TransactionSelector;
use Grpc\ChannelCredentials;
use GrpcLiteGax\Transport\GrpcLiteTransport;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('spanner-smoke')]
final class SpannerEmulatorSmokeTest extends TestCase
{
    private const PROJECT = 'projects/test-project';
    private const INSTANCE_CONFIG = 'projects/test-project/instanceConfigs/emulator-config';

    private string $instanceId;
    private string $databaseId;
    private string $instanceName;
    private string $databaseName;

    #[\Override]
    protected function setUp(): void
    {
        $host = getenv('SPANNER_EMULATOR_HOST');
        if (!is_string($host) || $host === '') {
            if (getenv('SPANNER_SMOKE_SKIP_MISSING_EMULATOR') === '1') {
                self::markTestSkipped('SPANNER_EMULATOR_HOST is required for the Spanner smoke suite.');
            }

            self::fail('SPANNER_EMULATOR_HOST is required for the Spanner smoke suite.');
        }

        $suffix = strtolower(bin2hex(random_bytes(4)));
        $this->instanceId = 'gax-smoke-' . $suffix;
        $this->databaseId = 'smokedb' . $suffix;
        $this->instanceName = self::PROJECT . '/instances/' . $this->instanceId;
        $this->databaseName = $this->instanceName . '/databases/' . $this->databaseId;

        $this->createInstance($host);
        $this->createDatabase($host);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $host = getenv('SPANNER_EMULATOR_HOST');
        if (!is_string($host) || $host === '' || !isset($this->instanceName)) {
            return;
        }

        $this->ignoreNotFound(fn () => $this->databaseAdmin($host)->dropDatabase(
            (new DropDatabaseRequest())->setDatabase($this->databaseName),
        ));
        $this->ignoreNotFound(fn () => $this->instanceAdmin($host)->deleteInstance(
            (new DeleteInstanceRequest())->setName($this->instanceName),
        ));
    }

    public function testExecutesDmlAndReadsRowsThroughServerStreamingSelect(): void
    {
        $host = (string) getenv('SPANNER_EMULATOR_HOST');
        $client = $this->spanner($host);
        $sessionName = $client->createSession(
            (new CreateSessionRequest())->setDatabase($this->databaseName),
        )->getName();

        try {
            $transaction = $this->beginReadWriteTransaction($client, $sessionName);
            $result = $client->executeSql((new ExecuteSqlRequest())
                ->setSession($sessionName)
                ->setTransaction((new TransactionSelector())->setId($transaction->getId()))
                ->setSql("INSERT INTO Singers (SingerId, FirstName, LastName) VALUES (1, 'Marc', 'Richards')"));

            $stats = $result->getStats();
            self::assertNotNull($stats);
            self::assertSame(1, (int) $stats->getRowCountExact());

            $client->commit((new CommitRequest())
                ->setSession($sessionName)
                ->setTransactionId($transaction->getId()));

            $stream = $client->executeStreamingSql((new ExecuteSqlRequest())
                ->setSession($sessionName)
                ->setSql('SELECT SingerId, FirstName, LastName FROM Singers ORDER BY SingerId'));

            /** @var iterable<PartialResultSet> $partials */
            $partials = $stream->readAll();
            self::assertSame([['1', 'Marc', 'Richards']], $this->streamedRows($partials));
        } finally {
            $client->deleteSession((new DeleteSessionRequest())->setName($sessionName));
            $client->close();
        }
    }

    private function createInstance(string $host): void
    {
        $instance = (new Instance())
            ->setConfig(self::INSTANCE_CONFIG)
            ->setDisplayName($this->instanceId)
            ->setNodeCount(1);

        $operation = $this->instanceAdmin($host)->createInstance((new CreateInstanceRequest())
            ->setParent(self::PROJECT)
            ->setInstanceId($this->instanceId)
            ->setInstance($instance));

        $this->waitForOperation($operation, 'CreateInstance');
    }

    private function createDatabase(string $host): void
    {
        $operation = $this->databaseAdmin($host)->createDatabase((new CreateDatabaseRequest())
            ->setParent($this->instanceName)
            ->setCreateStatement('CREATE DATABASE `' . $this->databaseId . '`')
            ->setExtraStatements([
                'CREATE TABLE Singers (
                    SingerId INT64 NOT NULL,
                    FirstName STRING(MAX),
                    LastName STRING(MAX)
                ) PRIMARY KEY (SingerId)',
            ]));

        $this->waitForOperation($operation, 'CreateDatabase');
    }

    private function beginReadWriteTransaction(SpannerClient $client, string $sessionName): Transaction
    {
        return $client->beginTransaction((new BeginTransactionRequest())
            ->setSession($sessionName)
            ->setOptions((new TransactionOptions())->setReadWrite(new ReadWrite())));
    }

    /**
     * @param iterable<PartialResultSet> $partials
     * @return list<list<int|string>>
     */
    private function streamedRows(iterable $partials): array
    {
        $values = [];
        foreach ($partials as $partial) {
            foreach ($partial->getValues() as $value) {
                if ($value->hasStringValue()) {
                    $values[] = $value->getStringValue();
                    continue;
                }

                $values[] = (int) $value->getNumberValue();
            }
        }

        return array_chunk($values, 3);
    }

    private function instanceAdmin(string $host): InstanceAdminClient
    {
        return new InstanceAdminClient($this->clientOptions($host));
    }

    private function databaseAdmin(string $host): DatabaseAdminClient
    {
        return new DatabaseAdminClient($this->clientOptions($host));
    }

    private function spanner(string $host): SpannerClient
    {
        return new SpannerClient($this->clientOptions($host));
    }

    /**
     * @return array<string, mixed>
     */
    private function clientOptions(string $host): array
    {
        return [
            'apiEndpoint' => $host,
            'credentials' => new InsecureCredentialsWrapper(),
            'transport' => GrpcLiteTransport::build($host, [
                'credentials' => ChannelCredentials::createInsecure(),
            ]),
            'disableRetries' => true,
        ];
    }

    private function waitForOperation(OperationResponse $operation, string $name): void
    {
        $operation->pollUntilComplete([
            'initialPollDelayMillis' => 10,
            'pollDelayMultiplier' => 1.0,
            'maxPollDelayMillis' => 10,
            'totalPollTimeoutMillis' => 5000,
        ]);

        $error = $operation->getError();
        self::assertTrue(
            $operation->operationSucceeded(),
            $error === null ? $name . ' did not complete successfully.' : $name . ' failed: ' . $error->getMessage(),
        );
    }

    private function ignoreNotFound(callable $operation): void
    {
        try {
            $operation();
        } catch (ApiException $exception) {
            if ($exception->getStatus() !== 'NOT_FOUND') {
                throw $exception;
            }
        }
    }
}
