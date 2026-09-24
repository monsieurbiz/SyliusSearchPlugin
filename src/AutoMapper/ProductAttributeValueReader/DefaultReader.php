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

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductAttributeValueReader;

use Stringable;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use UnexpectedValueException;

abstract class DefaultReader implements ReaderInterface
{
    public function getValue(ProductAttributeValueInterface $productAttribute)
    {
        $value = $productAttribute->getValue();
        if (null !== $value && !\is_scalar($value) && !$value instanceof Stringable) {
            throw new UnexpectedValueException('Expected a scalar attribute value.');
        }

        return (string) $value;
    }

    abstract public static function getReaderCode(): string;
}
