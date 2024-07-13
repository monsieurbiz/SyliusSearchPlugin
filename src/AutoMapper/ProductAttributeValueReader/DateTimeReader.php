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

use DateTime;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;

class DateTimeReader implements ReaderInterface
{
    protected string $defaultFormat = 'Y-m-d H:i:s';

    public function getValue(ProductAttributeValueInterface $productAttribute)
    {
        if (null === $productAttribute->getAttribute()) {
            return '';
        }

        $productAttributeValue = $productAttribute->getValue();
        if ($productAttributeValue instanceof DateTime) {
            $productAttributeValue = $productAttributeValue->format($this->defaultFormat);
        }

        return $productAttributeValue;
    }

    public static function getReaderCode(): string
    {
        return 'datetime';
    }
}
