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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Product;

use Jacquesbh\Eater\Eater;

final class VariantDTO extends Eater
{
    public function getCode(): ?string
    {
        return $this->getData('code');
    }

    public function setCode(string $code): void
    {
        $this->setData('code', $code);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) $this->getData('enabled');
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->setData('enabled', $enabled);
    }

    /**
     * @return bool
     */
    public function isInStock(): bool
    {
        return (bool) $this->getData('is_in_stock');
    }

    /**
     * @param bool $isInStock
     */
    public function setIsInStock(bool $isInStock): void
    {
        $this->setData('is_in_stock', $isInStock);
    }
}
