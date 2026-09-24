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

namespace MonsieurBiz\SyliusSearchPlugin\Tests\Unit\Search;

use Elastica\Query;
use Elastica\Query\FunctionScore;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore\Product\InStockWeightFunction;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\Product\CreatedAtSorter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\Product\NameSorter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\Product\PositionSorter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\Product\PriceSorter;
use MonsieurBiz\SyliusSearchPlugin\Tests\Support\SearchTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class SorterAndScoringTest extends SearchTestCase
{
    #[DataProvider('simpleSorts')]
    public function testSimpleSortingUsesTheCorrectIndexedField(string $class, string $key, string $field, string $direction): void
    {
        $query = new Query();
        (new $class())->apply($query, $this->configuration(['sorting' => [$key => $direction]]));
        self::assertSame([[$field => ['order' => $direction]]], $query->toArray()['sort']);
    }

    public static function simpleSorts(): iterable
    {
        foreach (['asc', 'desc'] as $direction) {
            yield [NameSorter::class, 'name', 'name.keyword', $direction];
            yield [CreatedAtSorter::class, 'created_at', 'created_at', $direction];
        }
    }

    #[DataProvider('directions')]
    public function testPriceSortingCannotUseAnotherChannelsPrice(string $direction): void
    {
        $query = new Query();
        (new PriceSorter($this->channelContext('WEB_FR')))->apply($query, $this->configuration(['sorting' => ['price' => $direction]]));
        self::assertSame([['prices.price' => ['order' => $direction, 'nested' => ['path' => 'prices', 'filter' => ['term' => ['prices.channel_code' => 'WEB_FR']]]]]], $query->toArray()['sort']);
    }

    public static function directions(): iterable
    {
        yield ['asc'];
        yield ['desc'];
    }

    public function testUnknownSortDoesNotTriggerOtherSorters(): void
    {
        $query = new Query();
        $before = $query->toArray();
        foreach ([new NameSorter(), new CreatedAtSorter(), new PriceSorter($this->channelContext()), new PositionSorter()] as $sorter) {
            $sorter->apply($query, $this->configuration(['sorting' => ['custom' => 'asc']]));
            self::assertSame($before, $query->toArray());
        }
    }

    public function testDefaultRelevanceAndTaxonPositionHaveStablePrecedence(): void
    {
        $query = new Query();
        (new PositionSorter())->apply($query, $this->configuration());
        self::assertSame([['_score' => ['order' => 'desc']]], $query->toArray()['sort']);
        $query = new Query();
        (new PositionSorter())->apply($query, $this->configuration([], RequestInterface::TAXON_TYPE, $this->taxon('shirts')));
        $sort = $query->toArray()['sort'];
        self::assertSame(['_score' => ['order' => 'desc']], $sort[0]);
        self::assertSame('asc', $sort[1]['product_taxons.position']['order']);
        self::assertSame('product_taxons', $sort[1]['product_taxons.position']['nested']['path']);
        self::assertSame(['nested' => ['path' => 'product_taxons.taxon', 'query' => ['term' => ['product_taxons.taxon.code' => ['value' => 'shirts']]]]], $sort[1]['product_taxons.position']['nested']['filter']);
    }

    #[DataProvider('scoringCases')]
    public function testStockBoostRespectsSwitchWeightAndRequestScope(bool $filterStock, int $weight, string $type, bool $expected): void
    {
        $query = new FunctionScore();
        (new InStockWeightFunction($filterStock, $weight, [RequestInterface::SEARCH_TYPE, RequestInterface::TAXON_TYPE]))->addFunctionScore($query, $this->configuration([], $type));
        $functions = $query->toArray()['function_score']['functions'] ?? [];
        if (!$expected) {
            self::assertSame([], $functions);

            return;
        }
        self::assertEquals([['weight' => $weight, 'filter' => ['nested' => ['path' => 'variants', 'query' => ['term' => ['variants.is_in_stock' => true]]]]]], $functions);
    }

    public static function scoringCases(): iterable
    {
        yield 'filter already enforces stock' => [true, 200, 'search', false];
        yield 'negative weight' => [false, -1, 'search', false];
        yield 'disabled weight' => [false, 0, 'search', false];
        yield 'boundary weight' => [false, 1, 'search', true];
        yield 'boost' => [false, 200, 'search', true];
        yield 'taxon boost' => [false, 200, 'taxon', true];
        yield 'instant excluded' => [false, 200, 'instant_search', false];
    }
}
