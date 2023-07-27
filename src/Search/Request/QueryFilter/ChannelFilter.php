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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter;

use Elastica\Query\BoolQuery;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use Sylius\Component\Channel\Context\ChannelContextInterface;

final class ChannelFilter implements QueryFilterInterface
{
    private ChannelContextInterface $channelContext;

    public function __construct(ChannelContextInterface $channelContext)
    {
        $this->channelContext = $channelContext;
    }

    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();

        $boolQuery->addFilter(
            $qb->query()->nested()
                ->setPath('channels')
                ->setQuery(
                    $qb->query()->term(['channels.code' => ['value' => $this->channelContext->getChannel()->getCode()]])
                )
        );
    }
}
