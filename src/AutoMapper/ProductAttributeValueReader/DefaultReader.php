<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductAttributeValueReader;

use Sylius\Component\Product\Model\ProductAttributeValueInterface;

abstract class DefaultReader implements ReaderInterface
{
    public function getValue(ProductAttributeValueInterface $productAttribute)
    {
        return (string) $productAttribute->getValue();
    }

    abstract public static function getReaderCode(): string;
}
