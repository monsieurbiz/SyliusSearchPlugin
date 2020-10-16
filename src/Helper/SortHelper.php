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

class SortHelper
{
    /**
     * Get query's sort array depending on sorted field.
     *
     * @param string $field
     * @param string $channel
     * @param string $order
     * @param string $taxon
     *
     * @return array
     */
    public static function getSortParamByField(string $field, string $channel, string $order = 'asc', $taxon = ''): array
    {
        switch ($field) {
            case 'name':
                return self::buildSort('attributes.value.keyword', $order, 'attributes', 'attributes.code', $field);
            case 'created_at':
                return self::buildSort('attributes.value.keyword', $order, 'attributes', 'attributes.code', $field);
            case 'price':
                return self::buildSort('price.value', $order, 'price', 'price.channel', $channel);
            case 'position':
                return self::buildSort('taxon.productPosition', $order, 'taxon', 'taxon.code', $taxon);
            default:
                // Dummy value to have null sorting in ES and keep ES results sorting
                return self::buildSort('attributes.value.keyword', $order, 'attributes', 'attributes.code', 'dummy');
        }
    }

    /**
     * Build sort array to add in query.
     *
     * @param string $field
     * @param string $order
     * @param string $nestedPath
     * @param string $sortFilterField
     * @param string $sortFilterValue
     *
     * @return array
     */
    public static function buildSort(
        string $field,
        string $order,
        string $nestedPath,
        string $sortFilterField,
        string $sortFilterValue
    ): array {
        return [
            $field => [
                'order' => $order,
                'nested' => [
                    'path' => $nestedPath,
                    'filter' => [
                        'term' => [$sortFilterField => $sortFilterValue],
                    ],
                ],
            ],
        ];
    }
}
