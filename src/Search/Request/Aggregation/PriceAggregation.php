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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\Aggregation;

use Elastica\QueryBuilder;
use Sylius\Component\Channel\Context\ChannelContextInterface;

final class PriceAggregation implements AggregationBuilderInterface
{
    private ChannelContextInterface $channelContext;

    public function __construct(ChannelContextInterface $channelContext)
    {
        $this->channelContext = $channelContext;
    }

    public function build($aggregation, array $filters)
    {
        if (!$this->isSupported($aggregation)) {
            return null;
        }

        $qb = new QueryBuilder();
        $channelCode = $this->channelContext->getChannel()->getCode() ?? '';

        $filters = array_filter($filters, function ($filter): bool {
            return !$filter->hasParam('path') || 'prices' !== $filter->getParam('path');
        });

        $filterQuery = $qb->query()->bool();
        foreach ($filters as $filter) {
            $filterQuery->addMust($filter);
        }

        return $qb->aggregation()
            ->filter('prices')
            ->setFilter($filterQuery)
            ->addAggregation(
                $qb->aggregation()
                    ->nested('prices', 'prices')
                    ->addAggregation(
                        $qb->aggregation()
                            ->filter('prices')
                            ->setFilter(
                                $qb->query()->term()
                                    ->setTerm('prices.channel_code', $channelCode)
                            )
                            ->addAggregation(
                                $qb->aggregation()
                                    ->stats('prices_stats')
                                    ->setField('prices.price')
                            )
                    )
            )
        ;
    }

    /**
     * @param string|array|object $aggregation
     */
    private function isSupported($aggregation): bool
    {
        return 'price' === $aggregation && null !== $this->channelContext->getChannel()->getCode();
    }
}
