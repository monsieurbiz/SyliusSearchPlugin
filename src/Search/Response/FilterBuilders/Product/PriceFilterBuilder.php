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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterBuilders\Product;

use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Filter\RangeFilter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterBuilders\FilterBuilderInterface;

class PriceFilterBuilder implements FilterBuilderInterface
{
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function build(
        DocumentableInterface $documentable,
        RequestConfiguration $requestConfiguration,
        string $aggregationCode,
        array $aggregationData
    ): ?array {
        if (false === (bool) preg_match('/monsieurbiz_product$/', $documentable->getIndexCode()) || 'prices' !== $aggregationCode) {
            return null;
        }

        $filter = null;
        $priceAggregation = $aggregationData['prices']['prices'] ?? null;
        if ($priceAggregation && $priceAggregation['doc_count'] > 0) {
            $filter = [
                new RangeFilter(
                    $requestConfiguration,
                    'price',
                    'monsieurbiz_searchplugin.filters.price_filter',
                    'monsieurbiz_searchplugin.filters.price_min',
                    'monsieurbiz_searchplugin.filters.price_max',
                    (int) floor(($priceAggregation['prices_stats']['min'] ?? 0) / 100),
                    (int) ceil(($priceAggregation['prices_stats']['max'] ?? 0) / 100)
                ),
            ];
        }

        return $filter;
    }

    public function getPosition(): int
    {
        return 10;
    }
}
