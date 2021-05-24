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

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\FilterableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\FilterableTrait;
use Sylius\Component\Product\Model\ProductOption as BaseProductOption;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(name="sylius_product_option")
 */
class ProductOption extends BaseProductOption implements FilterableInterface
{
    use FilterableTrait;
}
