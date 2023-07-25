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

namespace App\Search\Request\Sorting\Product;

use Elastica\Query;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterBuilderTrait;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterInterface;

class ShortDescriptionSorter implements SorterInterface
{
    use SorterBuilderTrait;

    public function apply(Query $query, RequestConfiguration $requestConfiguration): void
    {
        $sorting = $requestConfiguration->getSorting();
        if (!\array_key_exists('short_description', $sorting)) {
            return;
        }

        $query->addSort($this->buildSort('short_description.keyword', $sorting['short_description']));
    }
}
