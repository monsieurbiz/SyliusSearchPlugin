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

namespace App\Search\Request\FunctionScore\Product;

use Elastica\Query\FunctionScore;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore\FunctionScoreInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;

class BoostExpensiveProductFunction implements FunctionScoreInterface
{
    private const PRICE_BOOST_VALUE = 5000;

    private const BOOST_WEIGHT = 100;

    public function __construct(
        private ChannelContextInterface $channelContext,
        private array $queryTexts = [],
    ) {
    }

    public function addFunctionScore(FunctionScore $functionScore, RequestConfiguration $requestConfiguration): void
    {
        // Only apply this boost on search request
        if (RequestInterface::SEARCH_TYPE !== $requestConfiguration->getType()) {
            return;
        }

        // Apply this boost only on specific search request
        if (\in_array($requestConfiguration->getQueryText(), $this->queryTexts, true)) {
        }

        $qb = new QueryBuilder();

        // Create the boost query, for the example, we boost products with a price greater than 5000
        $query = $qb->query()->bool()
            ->addFilter($qb->query()->range('prices.price', ['gte' => self::PRICE_BOOST_VALUE]))
            ->addFilter(
                $qb->query()->term(['prices.channel_code' => ['value' => $this->channelContext->getChannel()->getCode()]])
            )
        ;

        // Add the boost query to the function score
        $functionScore->addWeightFunction(
            self::BOOST_WEIGHT,
            $qb->query()->nested()->setPath('prices')
                ->setQuery($query)
        );
    }
}
