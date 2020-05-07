<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Helper;

class AggregationHelper
{
    /**
     * Build sort array to add in query
     *
     * @param string $field
     * @return array
     */
    public static function buildAggregation(string $field): array
    {
        return [
            'filter' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => ['attributes.code' => $field]
                        ]
                    ]
                ]
            ],
            'aggs' => [
                'values' => [
                    'terms' => ['field' => 'attributes.value.keyword']
                ]
            ]
        ];
    }

    /**
     * Build sort array to add in query
     *
     * @param array $filters
     * @return array
     */
    public static function buildAggregations(array $filters): array
    {
        if (empty($filters)) {
            return [];
        }

        $aggregations = [];
        foreach ($filters as $field) {
            $aggregations[$field] = self::buildAggregation($field);
        }

        return [
            'filters' => [
                'nested' => ['path' => 'attributes'],
                'aggs' => $aggregations
            ],
            // Get attributes info to be able to retrieve the attribute name from code
            'attributes' => [
                'nested' => ['path' => 'attributes'],
                'aggs' => [
                    'codes' => [
                        'terms' => ['field' => 'attributes.code']
                        ,
                        'aggs' => [
                            'names' => [
                                'terms' => ['field' => 'attributes.name.keyword']
                            ],
                        ]
                    ]
                ]
            ],
        ];
    }
}
