<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Integration\PubSub;

use Google\ApiCore\ApiException;
use Google\ApiCore\InsecureCredentialsWrapper;
use Google\Cloud\PubSub\V1\AcknowledgeRequest;
use Google\Cloud\PubSub\V1\Client\PublisherClient;
use Google\Cloud\PubSub\V1\Client\SubscriberClient;
use Google\Cloud\PubSub\V1\DeleteSubscriptionRequest;
use Google\Cloud\PubSub\V1\DeleteTopicRequest;
use Google\Cloud\PubSub\V1\PublishRequest;
use Google\Cloud\PubSub\V1\PubsubMessage;
use Google\Cloud\PubSub\V1\PullRequest;
use Google\Cloud\PubSub\V1\Subscription;
use Google\Cloud\PubSub\V1\Topic;
use Grpc\ChannelCredentials;
use GrpcLiteGax\Transport\GrpcLiteTransport;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('pubsub-smoke')]
final class PubSubEmulatorSmokeTest extends TestCase
{
    private const PROJECT = 'test-project';

    private string $topicName;
    private string $subscriptionName;

    #[\Override]
    protected function setUp(): void
    {
        $host = getenv('PUBSUB_EMULATOR_HOST');
        if (!is_string($host) || $host === '') {
            if (getenv('PUBSUB_SMOKE_SKIP_MISSING_EMULATOR') === '1') {
                self::markTestSkipped('PUBSUB_EMULATOR_HOST is required for the Pub/Sub smoke suite.');
            }

            self::fail('PUBSUB_EMULATOR_HOST is required for the Pub/Sub smoke suite.');
        }

        $suffix = strtolower(bin2hex(random_bytes(4)));
        $this->topicName = PublisherClient::topicName(self::PROJECT, 'gax-smoke-' . $suffix);
        $this->subscriptionName = SubscriberClient::subscriptionName(self::PROJECT, 'gax-smoke-' . $suffix);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $host = getenv('PUBSUB_EMULATOR_HOST');
        if (!is_string($host) || $host === '' || !isset($this->topicName, $this->subscriptionName)) {
            return;
        }

        $publisher = $this->publisher($host);
        $subscriber = $this->subscriber($host);

        try {
            $this->ignoreNotFound(fn () => $subscriber->deleteSubscription(
                (new DeleteSubscriptionRequest())->setSubscription($this->subscriptionName),
            ));
            $this->ignoreNotFound(fn () => $publisher->deleteTopic(
                (new DeleteTopicRequest())->setTopic($this->topicName),
            ));
        } finally {
            $publisher->close();
            $subscriber->close();
        }
    }

    public function testPublishesPullsAndAcknowledgesMessage(): void
    {
        $host = (string) getenv('PUBSUB_EMULATOR_HOST');
        $publisher = $this->publisher($host);
        $subscriber = $this->subscriber($host);

        try {
            $createdTopic = $publisher->createTopic((new Topic())->setName($this->topicName));
            self::assertSame($this->topicName, $createdTopic->getName());

            $createdSubscription = $subscriber->createSubscription((new Subscription())
                ->setName($this->subscriptionName)
                ->setTopic($this->topicName));
            self::assertSame($this->subscriptionName, $createdSubscription->getName());

            $publishResponse = $publisher->publish((new PublishRequest())
                ->setTopic($this->topicName)
                ->setMessages([
                    (new PubsubMessage())->setData('hello from grpc-lite gax'),
                ]));
            self::assertCount(1, $publishResponse->getMessageIds());

            $received = $this->pullOne($subscriber);
            $message = $received->getMessage();
            self::assertNotNull($message);
            self::assertSame('hello from grpc-lite gax', $message->getData());

            $subscriber->acknowledge((new AcknowledgeRequest())
                ->setSubscription($this->subscriptionName)
                ->setAckIds([$received->getAckId()]));
        } finally {
            $publisher->close();
            $subscriber->close();
        }
    }

    private function pullOne(SubscriberClient $subscriber): \Google\Cloud\PubSub\V1\ReceivedMessage
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $response = $subscriber->pull((new PullRequest())
                ->setSubscription($this->subscriptionName)
                ->setMaxMessages(1)
                ->setReturnImmediately(true));

            foreach ($response->getReceivedMessages() as $message) {
                return $message;
            }

            usleep(100_000);
        }

        self::fail('Pub/Sub emulator did not return the published message.');
    }

    private function publisher(string $host): PublisherClient
    {
        return new PublisherClient($this->clientOptions($host));
    }

    private function subscriber(string $host): SubscriberClient
    {
        return new SubscriberClient($this->clientOptions($host));
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
