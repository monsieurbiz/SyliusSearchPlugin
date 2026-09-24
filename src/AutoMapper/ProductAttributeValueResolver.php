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

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper;

use MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductAttributeValueReader\ReaderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use RuntimeException;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Traversable;

final class ProductAttributeValueResolver implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ReaderInterface[]
     */
    private array $productAttributeValueReaders;

    public function __construct(iterable $productAttributeValueReaders)
    {
        $this->logger = new NullLogger();
        $this->productAttributeValueReaders = $productAttributeValueReaders instanceof Traversable
            ? iterator_to_array($productAttributeValueReaders)
            : $productAttributeValueReaders;
        if (0 === \count($this->productAttributeValueReaders)) {
            throw new RuntimeException('Undefined product attribute value reader');
        }
    }

    /**
     * @return array|string|null
     */
    public function getProductAttributeValue(ProductAttributeValueInterface $productAttributeValue)
    {
        if (null === $productAttributeValue->getType()) {
            return null;
        }
        if (!\array_key_exists($productAttributeValue->getType(), $this->productAttributeValueReaders)) {
            // @phpstan-ignore-next-line The logger can't be null here
            $this->logger->alert(\sprintf('Missing product attribute value reader for "%s" type', $productAttributeValue->getType()));

            return null;
        }
        $reader = $this->productAttributeValueReaders[$productAttributeValue->getType()];

        return $reader->getValue($productAttributeValue);
    }
}
