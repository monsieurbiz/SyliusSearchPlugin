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

class DateReader extends DateTimeReader implements ReaderInterface
{
    protected string $defaultFormat = 'Y-m-d';

    public static function getReaderCode(): string
    {
        return 'date';
    }
}
