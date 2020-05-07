<?php

declare(strict_types=1);

namespace Tests\MonsieurBiz\SyliusSearchPlugin\App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableProductTrait;
use MonsieurBiz\SyliusSearchPlugin\Model\DocumentableInterface;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Core\Model\ProductTranslation;
use Sylius\Component\Product\Model\ProductTranslationInterface;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(name="sylius_product")
 */
class Product extends BaseProduct implements DocumentableInterface
{
    use DocumentableProductTrait;

    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }
}
