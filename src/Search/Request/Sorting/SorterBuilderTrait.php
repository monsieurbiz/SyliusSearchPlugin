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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting;

use Elastica\Query\AbstractQuery;

trait SorterBuilderTrait
{
    protected function buildSort(
        string $field,
        string $order,
        ?string $nestedPath = null,
        ?string $sortFilterField = null,
        $sortFilterValue = null
    ): array {
        $sort = [$field => ['order' => $order]];
        if (null !== $nestedPath) {
            $sort[$field]['nested']['path'] = $nestedPath;
            $filter = [
                'term' => [
                    $sortFilterField => $sortFilterValue,
                ],
            ];
            if ($sortFilterValue instanceof AbstractQuery) {
                $filter = $sortFilterValue->toArray();
            }
            $sort[$field]['nested']['filter'] = $filter;
        }

        return $sort;
    }
}
