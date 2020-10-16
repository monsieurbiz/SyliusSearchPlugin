<?php

declare(strict_types=1);

namespace Tests\MonsieurBiz\SyliusSearchPlugin\App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\FilterableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\FilterableTrait;
use Sylius\Component\Attribute\Model\AttributeTranslationInterface;
use Sylius\Component\Product\Model\ProductAttribute as BaseProductAttribute;
use Sylius\Component\Product\Model\ProductAttributeTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_attribute")
 */
class ProductAttribute extends BaseProductAttribute implements FilterableInterface
{
    use FilterableTrait;

    protected function createTranslation(): AttributeTranslationInterface
    {
        return new ProductAttributeTranslation();
    }
}
