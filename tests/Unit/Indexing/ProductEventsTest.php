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

namespace MonsieurBiz\SyliusSearchPlugin\Tests\Unit\Indexing;

use InvalidArgumentException;
use MonsieurBiz\SyliusSearchPlugin\EventListener\ProductEventListener;
use MonsieurBiz\SyliusSearchPlugin\EventListener\ProductVariantEventListener;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromIds;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductToDeleteFromIds;
use MonsieurBiz\SyliusSearchPlugin\Tests\Support\RecordingMessageBus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Symfony\Component\EventDispatcher\GenericEvent;

final class ProductEventsTest extends TestCase
{
    #[DataProvider('messages')]
    public function testIdMessagesDeduplicateAndPreserveTheirSerializedContract(string $class): void
    {
        $message = new $class([2, 3, 2]);
        $message->addProductId(4);
        self::assertSame([0 => 2, 1 => 3, 3 => 4], $message->getProductIds());
        self::assertSame($message->getProductIds(), unserialize(serialize($message), ['allowed_classes' => [$class]])->getProductIds());
    }

    public static function messages(): iterable
    {
        yield [ProductReindexFromIds::class];
        yield [ProductToDeleteFromIds::class];
    }

    public function testProductUpdatePublishesItsPersistedIdentifier(): void
    {
        $bus = new RecordingMessageBus();
        (new ProductEventListener($bus))->dispatchProductReindexMessage(new GenericEvent($this->product(42)));
        self::assertCount(1, $bus->messages);
        self::assertInstanceOf(ProductReindexFromIds::class, $bus->messages[0]);
        self::assertSame([42], $bus->messages[0]->getProductIds());
    }

    public function testDeletionCapturesIdsBeforeRemovalAndDrainsOnlyOnce(): void
    {
        $bus = new RecordingMessageBus();
        $listener = new ProductEventListener($bus);
        $product = $this->product(42);
        $listener->dispatchDeleteProductReindexMessage();
        self::assertSame([], $bus->messages);
        $listener->saveProductIdToDispatchReindexMessage(new GenericEvent($product));
        $listener->saveProductIdToDispatchReindexMessage(new GenericEvent($this->product(43)));
        (new ReflectionProperty($product, 'id'))->setValue($product, null);
        self::assertSame([], $bus->messages);
        $listener->dispatchDeleteProductReindexMessage();
        $listener->dispatchDeleteProductReindexMessage();
        self::assertCount(1, $bus->messages);
        self::assertInstanceOf(ProductToDeleteFromIds::class, $bus->messages[0]);
        self::assertSame([42, 43], $bus->messages[0]->getProductIds());
    }

    public function testVariantUpdateReindexesItsParentNotTheVariantId(): void
    {
        $bus = new RecordingMessageBus();
        $variant = new ProductVariant();
        (new ReflectionProperty($variant, 'id'))->setValue($variant, 99);
        $variant->setProduct($this->product(42));
        (new ProductVariantEventListener($bus))->dispatchProductVariantReindexMessage(new GenericEvent($variant));
        self::assertSame([42], $bus->messages[0]->getProductIds());
    }

    public function testOrphanVariantsAndEmptyDeletionQueueAreNoOps(): void
    {
        $bus = new RecordingMessageBus();
        $listener = new ProductVariantEventListener($bus);
        $event = new GenericEvent(new ProductVariant());
        $listener->dispatchProductVariantReindexMessage($event);
        $listener->saveProductIdToDispatchReindexMessage($event);
        $listener->dispatchProductReindexMessage($event);
        self::assertSame([], $bus->messages);
    }

    public function testDeletedVariantsDeduplicateParentIdsAndDrainOnlyOnce(): void
    {
        $bus = new RecordingMessageBus();
        $listener = new ProductVariantEventListener($bus);
        $variant = new ProductVariant();
        $variant->setProduct($this->product(42));
        $event = new GenericEvent($variant);
        $listener->saveProductIdToDispatchReindexMessage($event);
        $listener->saveProductIdToDispatchReindexMessage($event);
        $listener->dispatchProductReindexMessage(new GenericEvent(new ProductVariant()));
        $listener->dispatchProductReindexMessage($event);
        self::assertCount(1, $bus->messages);
        self::assertSame([42], $bus->messages[0]->getProductIds());
    }

    #[DataProvider('invalidSubjects')]
    public function testWrongEventSubjectsAreRejected(string $class, string $method): void
    {
        $listener = new $class(new RecordingMessageBus());
        $this->expectException(InvalidArgumentException::class);
        $listener->{$method}(new GenericEvent(new stdClass()));
    }

    public static function invalidSubjects(): iterable
    {
        yield [ProductEventListener::class, 'dispatchProductReindexMessage'];
        yield [ProductEventListener::class, 'saveProductIdToDispatchReindexMessage'];
        yield [ProductVariantEventListener::class, 'dispatchProductVariantReindexMessage'];
        yield [ProductVariantEventListener::class, 'saveProductIdToDispatchReindexMessage'];
        yield [ProductVariantEventListener::class, 'dispatchProductReindexMessage'];
    }

    private function product(int $id): Product
    {
        $product = new Product();
        (new ReflectionProperty($product, 'id'))->setValue($product, $id);

        return $product;
    }
}
