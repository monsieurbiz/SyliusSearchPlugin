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

use DateTimeInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use UnexpectedValueException;

class DateTimeReader implements ReaderInterface
{
    protected string $defaultFormat = 'Y-m-d H:i:s';

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) Validate the widened Sylius 2 mixed value contract. */
    public function getValue(ProductAttributeValueInterface $productAttribute)
    {
        if (null === $productAttribute->getAttribute()) {
            return '';
        }

        $productAttributeValue = $productAttribute->getValue();
        if ($productAttributeValue instanceof DateTimeInterface) {
            $productAttributeValue = $productAttributeValue->format($this->defaultFormat);
        }

        if (!\is_string($productAttributeValue) && !\is_array($productAttributeValue)) {
            throw new UnexpectedValueException('Expected a date attribute value.');
        }

        return $productAttributeValue;
    }

    public static function getReaderCode(): string
    {
        return 'datetime';
    }
}
