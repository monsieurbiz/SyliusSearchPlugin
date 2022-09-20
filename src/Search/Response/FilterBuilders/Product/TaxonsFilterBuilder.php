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
use MonsieurBiz\SyliusSearchPlugin\Search\Filter\Filter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterBuilders\FilterBuilderInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;

class TaxonsFilterBuilder implements FilterBuilderInterface
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
        if (false === (bool) preg_match('/monsieurbiz_product$/', $documentable->getIndexCode()) || 'taxons' !== $aggregationCode) {
            return null;
        }

        $childTaxonCodes = null;
        if (RequestInterface::TAXON_TYPE == $requestConfiguration->getType()) {
            $childTaxonCodes = $requestConfiguration->getTaxon()->getChildren()->map(function (TaxonInterface $taxon): ?string {
                return $taxon->getCode();
            });
        }

        $taxonAggregation = $aggregationData['taxons']['taxons']['taxons'] ?? null;
        if ($taxonAggregation && $taxonAggregation['doc_count'] > 0) {
            $filter = new Filter($requestConfiguration, 'taxons', 'monsieurbiz_searchplugin.filters.taxon_filter', $taxonAggregation['doc_count']);

            // Get main taxon code in aggregation
            $taxonCodeBuckets = $taxonAggregation['codes']['buckets'] ?? [];
            foreach ($taxonCodeBuckets as $taxonCodeBucket) {
                if (0 === $taxonCodeBucket['doc_count']) {
                    continue;
                }
                $taxonCode = $taxonCodeBucket['key'];
                // If we have a current taxon, add only the filter for the children of this taxon.
                if (null !== $childTaxonCodes && !\in_array($taxonCode, $childTaxonCodes->toArray(), true)) {
                    continue;
                }
                $taxonName = null;
                $taxonNameBuckets = $taxonCodeBucket['names']['buckets'] ?? [];
                foreach ($taxonNameBuckets as $taxonNameBucket) {
                    $taxonName = $taxonNameBucket['key'];
                    $filter->addValue($taxonName ?? $taxonCode, $taxonCodeBucket['doc_count'], $taxonCode);
                }
            }

            // Put taxon filter in first if contains value
            if (0 !== \count($filter->getValues())) {
                return [$filter];
            }
        }

        return null;
    }

    public function getPosition(): int
    {
        return 2;
    }
}
