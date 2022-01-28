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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore\Product;

use Elastica\Query\FunctionScore;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore\FunctionScoreInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

class InStockWeightFunction implements FunctionScoreInterface
{
    private bool $enableStockFilter;

    private int $inStockWeight;

    private array $applyOnRequestTypes;

    public function __construct(
        bool $enableStockFilter,
        int $inStockWeight,
        array $applyOnRequestTypes
    ) {
        $this->enableStockFilter = $enableStockFilter;
        $this->inStockWeight = $inStockWeight;
        $this->applyOnRequestTypes = $applyOnRequestTypes;
    }

    public function addFunctionScore(FunctionScore $functionScore, RequestConfiguration $requestConfiguration): void
    {
        if (
            $this->enableStockFilter
            || 1 > $this->inStockWeight
            || !\in_array($requestConfiguration->getType(), $this->applyOnRequestTypes, true)
        ) {
            return;
        }

        $qb = new QueryBuilder();
        $functionScore->addWeightFunction(
            $this->inStockWeight,
            $qb->query()->nested()->setPath('variants')
                ->setQuery($qb->query()->term(['variants.is_in_stock' => true]))
        );
    }
}
