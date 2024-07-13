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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Product;

use Jacquesbh\Eater\Eater;

final class VariantDTO extends Eater
{
    public function getCode(): ?string
    {
        /** @phpstan-ignore-next-line */
        return $this->getData('code');
    }

    public function setCode(string $code): void
    {
        $this->setData('code', $code);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->getData('enabled');
    }

    public function setEnabled(bool $enabled): void
    {
        $this->setData('enabled', $enabled);
    }

    public function isInStock(): bool
    {
        return (bool) $this->getData('is_in_stock');
    }

    public function setIsInStock(bool $isInStock): void
    {
        $this->setData('is_in_stock', $isInStock);
    }
}
