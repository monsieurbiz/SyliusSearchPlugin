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

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\SearchableTrait;
use Sylius\Component\Attribute\Model\AttributeTranslationInterface;
use Sylius\Component\Product\Model\ProductAttribute as BaseProductAttribute;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_attribute")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_attribute')]
class ProductAttribute extends BaseProductAttribute implements SearchableInterface
{
    use SearchableTrait;

    protected function createTranslation(): AttributeTranslationInterface
    {
        return new ProductAttributeTranslation();
    }
}
