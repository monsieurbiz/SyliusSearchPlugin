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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\Product;

use Elastica\Query\BoolQuery;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\PostFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use Sylius\Component\Channel\Context\ChannelContextInterface;

final class PricePostFilter implements PostFilterInterface
{
    private ChannelContextInterface $channelContext;

    public function __construct(ChannelContextInterface $channelContext)
    {
        $this->channelContext = $channelContext;
    }

    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();
        $priceValue = $requestConfiguration->getAppliedFilters('price');
        if (0 !== \count($priceValue)) {
            $channelPriceFilter = $qb->query()
                ->term(['prices.channel_code' => $this->channelContext->getChannel()->getCode()])
            ;
            $conditions = [];
            if (\array_key_exists('min', $priceValue)) {
                $conditions['gte'] = $priceValue['min'] * 100;
            }
            if (\array_key_exists('max', $priceValue)) {
                $conditions['lte'] = $priceValue['max'] * 100;
            }
            $priceQuery = $qb->query()
                ->range('prices.price', $conditions)
            ;

            $boolQuery->addMust(
                $qb->query()
                    ->nested()
                    ->setPath('prices')
                    ->setQuery(
                        $qb->query()->bool()
                            ->addMust($channelPriceFilter)
                            ->addMust($priceQuery)
                    )
            );
        }
    }
}
