<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Tests\Unit;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use MonsieurBiz\SyliusSearchPlugin\EventSubscriber\ReindexProductEventSubscriber;
use MonsieurBiz\SyliusSearchPlugin\Manager\AutomaticReindexManagerInterface;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromTaxon;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class ReindexProductEventSubscriberTest extends TestCase
{
    public function testTransportFailureIsLoggedAndRequiresExplicitReconciliation(): void
    {
        $messages = [];
        [, $manager] = $this->subscriber([$this->productTaxon(8), $this->productTaxon(9)], $messages);
        $automatic = $this->createMock(AutomaticReindexManagerInterface::class);
        $automatic->method('shouldBeAutomaticallyReindex')->willReturn(true);
        $bus = $this->createMock(MessageBusInterface::class);
        $calls = 0;
        $bus->expects(self::exactly(2))->method('dispatch')->willReturnCallback(static function ($message) use (&$calls) {
            if (2 === ++$calls) {
                throw new RuntimeException('Transport unavailable');
            }

            return new Envelope($message);
        });
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::stringContains('monsieurbiz:search:populate'),
            self::callback(static fn (array $context): bool => [8 => 8, 9 => 9] === $context['taxon_ids']),
        );
        $subscriber = new ReindexProductEventSubscriber($bus, $automatic);
        $subscriber->setLogger($logger);
        $subscriber->onFlush(new OnFlushEventArgs($manager));

        try {
            $subscriber->postFlush(new PostFlushEventArgs($manager));
            self::fail('Dispatch failure must not report success.');
        } catch (RuntimeException $exception) {
            self::assertSame('Transport unavailable', $exception->getMessage());
        }
        // No recursive or implicit duplicate delivery after a failed publication.
        $subscriber->postFlush(new PostFlushEventArgs($manager));
    }

    public function testMessagesAreDeduplicatedAndOnlyDispatchedAfterFlush(): void
    {
        $messages = [];
        [$subscriber, $manager] = $this->subscriber([$this->productTaxon(8), $this->productTaxon(8), $this->productTaxon(null)], $messages);
        $subscriber->onFlush(new OnFlushEventArgs($manager));
        self::assertSame([], $messages);
        $subscriber->postFlush(new PostFlushEventArgs($manager));
        self::assertCount(1, $messages);
        self::assertSame(8, $messages[0]->getTaxonId());
        $subscriber->postFlush(new PostFlushEventArgs($manager));
        self::assertCount(1, $messages);
        $subscriber->onFlush(new OnFlushEventArgs($manager));
        $subscriber->postFlush(new PostFlushEventArgs($manager));
        self::assertCount(2, $messages);
    }

    public function testWorkerResetDiscardsPendingWorkFromFailedFlush(): void
    {
        $messages = [];
        [$subscriber, $manager] = $this->subscriber([$this->productTaxon(8)], $messages);
        $subscriber->onFlush(new OnFlushEventArgs($manager));
        $subscriber->reset();
        $subscriber->postFlush(new PostFlushEventArgs($manager));
        self::assertSame([], $messages);
    }

    public function testDisabledAutomaticIndexingDoesNotDispatch(): void
    {
        $messages = [];
        [$subscriber, $manager] = $this->subscriber([$this->productTaxon(8)], $messages, false);
        $subscriber->onFlush(new OnFlushEventArgs($manager));
        $subscriber->postFlush(new PostFlushEventArgs($manager));
        self::assertSame([], $messages);
    }

    public function testLegacySerializedMessageRemainsReadable(): void
    {
        $class = ProductReindexFromTaxon::class;
        $field = "\0" . $class . "\0taxonId";
        $serialized = 'O:' . \strlen($class) . ':"' . $class . '":1:{s:' . \strlen($field) . ':"' . $field . '";i:8;}';
        $message = unserialize($serialized, ['allowed_classes' => [$class]]);
        self::assertInstanceOf(ProductReindexFromTaxon::class, $message);
        self::assertSame(8, $message->getTaxonId());
    }

    private function productTaxon(?int $id): ProductTaxonInterface
    {
        $taxon = $this->createMock(TaxonInterface::class);
        $taxon->method('getId')->willReturn($id);
        $link = $this->createMock(ProductTaxonInterface::class);
        $link->method('getTaxon')->willReturn($taxon);

        return $link;
    }

    private function subscriber(array $links, array &$messages, bool $enabled = true): array
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(static function ($message) use (&$messages) {
            $messages[] = $message;

            return new Envelope($message);
        });
        $automatic = $this->createMock(AutomaticReindexManagerInterface::class);
        $automatic->method('shouldBeAutomaticallyReindex')->willReturn($enabled);
        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->method('getScheduledEntityInsertions')->willReturn($links);
        $unitOfWork->method('getScheduledEntityUpdates')->willReturn([]);
        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->method('getUnitOfWork')->willReturn($unitOfWork);

        return [new ReindexProductEventSubscriber($bus, $automatic), $manager];
    }
}
