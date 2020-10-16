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

namespace MonsieurBiz\SyliusSearchPlugin\Helper;

class AggregationHelper
{
    public const MAX_AGGREGATED_ATTRIBUTES_INFO = 100;
    public const MAX_AGGREGATED_TAXON_INFO = 500;

    /**
     * Build sort array to add in query.
     *
     * @param string $field
     *
     * @return array
     */
    public static function buildAggregation(string $field): array
    {
        return [
            'filter' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => ['attributes.code' => $field],
                        ],
                    ],
                ],
            ],
            'aggs' => [
                'values' => [
                    'terms' => ['field' => 'attributes.value.keyword'],
                ],
            ],
        ];
    }

    /**
     * Build sort array to add in query.
     *
     * @param array $filters
     *
     * @return array
     */
    public static function buildAggregations(array $filters): array
    {
        $attributeAggregations = [];
        foreach ($filters as $field) {
            $attributeAggregations[$field] = self::buildAggregation($field);
        }

        $aggregations = [
            'attributes' => [
                'nested' => ['path' => 'attributes'],
                'aggs' => [
                    'codes' => [
                        'terms' => ['field' => 'attributes.code', 'size' => self::MAX_AGGREGATED_ATTRIBUTES_INFO] // Retrieve all attributes info
                        ,
                        'aggs' => [
                            'names' => [
                                'terms' => ['field' => 'attributes.name.keyword'],
                            ],
                        ],
                    ],
                ],
            ],
            // Get taxon info to be able to retrieve the attribute name from code, we also need the level
            'taxons' => [
                'nested' => ['path' => 'taxon'],
                'aggs' => [
                    'codes' => [
                        'terms' => ['field' => 'taxon.code', 'size' => self::MAX_AGGREGATED_TAXON_INFO], // Retrieve all taxon info
                        'aggs' => [
                            'levels' => [
                                'terms' => ['field' => 'taxon.level'],
                                'aggs' => [
                                    'names' => [
                                        'terms' => ['field' => 'taxon.name'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // Get main taxon info to be able to retrieve the attribute name from code, we also need the level
            'mainTaxon' => [
                'nested' => ['path' => 'mainTaxon'],
                'aggs' => [
                    'codes' => [
                        'terms' => ['field' => 'mainTaxon.code', 'size' => self::MAX_AGGREGATED_TAXON_INFO], // Retrieve all taxon info
                        'aggs' => [
                            'levels' => [
                                'terms' => ['field' => 'mainTaxon.level'],
                                'aggs' => [
                                    'names' => [
                                        'terms' => ['field' => 'mainTaxon.name'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // Get attributes info to be able to retrieve the attribute name from code
            'price' => [
                'nested' => ['path' => 'price'],
                'aggs' => [
                    'values' => [
                        'stats' => ['field' => 'price.value'],
                    ],
                ],
            ],
        ];

        if (!empty($attributeAggregations)) {
            $aggregations['filters'] = [
                'nested' => ['path' => 'attributes'],
                'aggs' => $attributeAggregations,
            ];
        }

        return $aggregations;
    }
}
