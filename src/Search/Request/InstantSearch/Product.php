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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\InstantSearch;

use Elastica\Query;
use Elastica\Query\MultiMatch;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

class Product implements RequestInterface
{
    private DocumentableInterface $documentable;
    private ?RequestConfiguration $configuration;
    private ChannelContextInterface $channelContext;
    private ProductAttributeRepositoryInterface $productAttributeRepository;

    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        ChannelContextInterface $channelContext,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->documentable = $documentableRegistry->get('search.documentable.monsieurbiz_product');
        $this->channelContext = $channelContext;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    public function getType(): string
    {
        return RequestInterface::INSTANT_TYPE;
    }

    public function getDocumentable(): DocumentableInterface
    {
        return $this->documentable;
    }

    public function getQuery(): Query
    {
        if (!$this->configuration || '' === $this->configuration->getQueryText()) {
            throw new \Exception('missing query text'); //todo
        }

        $queryText = $this->configuration->getQueryText();
        $qb = $this->getQueryBuilder();
        $searchQuery = $qb->query()->bool()
            ->addShould($qb->query()->term(['code' => ['value' => $queryText]]))
            ->addShould(
                $qb->query()->multi_match()
                    ->setFields([
                        'name^5', // todo configuration
                        'description', // move to should ? score impact but not include in result
                    ])
                    ->setQuery($queryText)
                    ->setType(MultiMatch::TYPE_MOST_FIELDS)
                    ->setFuzziness(MultiMatch::FUZZINESS_AUTO)
            )
        ;

        foreach ($this->getAttributeQueries($queryText) as $attributeQuery) {
            $searchQuery->addShould($attributeQuery);
        }

        $boolQuery = $qb->query()->bool()
            ->addFilter($qb->query()->term(['enabled' => ['value' => true]]))
            ->addFilter(
                $qb->query()->nested()
                    ->setPath('channels')
                    ->setQuery(
                        $qb->query()->term(['channels.code' => ['value' => $this->channelContext->getChannel()->getCode()]])
                    )
            )
            ->addMust($searchQuery)
        ;

        return Query::create($boolQuery);
    }

    public function supports(string $type, string $documentableCode): bool
    {
        return RequestInterface::INSTANT_TYPE == $type && $this->getDocumentable()->getIndexCode() == $documentableCode;
    }

    public function setConfiguration(RequestConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    private function getAttributeQueries(string $queryText): array
    {
        $attributeQueries = [];
        $qb = $this->getQueryBuilder();
        foreach ($this->productAttributeRepository->findIsSearchableOrFilterable() as $productAttribute) {
            if (!$productAttribute->isSearchable()) {
                continue;
            }

            $attributeQueries[] = $qb->query()->nested()
                ->setPath('attributes')
                ->setQuery(
                    $qb->query()->nested()
                        ->setPath(sprintf('attributes.%s', $productAttribute->getCode()))
                        ->setQuery(
                            $qb->query()->multi_match()
                                ->setFields([
                                    sprintf('attributes.%s.value^%d', $productAttribute->getCode(), $productAttribute->getSearchWeight()),
                                ])
                                ->setQuery($queryText)
                                ->setFuzziness(MultiMatch::FUZZINESS_AUTO)
                        )
                )
            ;
        }

        return $attributeQueries;
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder();
    }
}
