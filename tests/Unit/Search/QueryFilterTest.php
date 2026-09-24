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

use Elastica\Query\BoolQuery;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\ChannelFilter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\EnabledFilter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\Product\IsInStockFilter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\Product\TaxonFilter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\SearchTermFilter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Tests\Support\SearchTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

final class QueryFilterTest extends SearchTestCase
{
    #[DataProvider('channels')]
    public function testEveryRequestIsRestrictedToItsOwnChannel(string $code): void
    {
        $query = new BoolQuery();
        (new ChannelFilter($this->channelContext($code)))->apply($query, $this->configuration());
        self::assertSame(['bool' => ['filter' => [['nested' => ['path' => 'channels', 'query' => ['term' => ['channels.code' => ['value' => $code]]]]]]]], $query->toArray());
    }

    public static function channels(): iterable
    {
        yield ['WEB_US'];
        yield ['WEB_FR'];
    }

    public function testDisabledProductsAreExcluded(): void
    {
        $query = new BoolQuery();
        (new EnabledFilter())->apply($query, $this->configuration());
        self::assertSame(['bool' => ['filter' => [['term' => ['enabled' => ['value' => true]]]]]], $query->toArray());
    }

    public function testStockRestrictionCanBeEnabledWithoutChangingOtherFilters(): void
    {
        $query = new BoolQuery();
        (new EnabledFilter())->apply($query, $this->configuration());
        $before = $query->toArray();
        (new IsInStockFilter(false))->apply($query, $this->configuration());
        self::assertSame($before, $query->toArray());
        (new IsInStockFilter(true))->apply($query, $this->configuration());
        self::assertSame(['nested' => ['path' => 'variants', 'query' => ['term' => ['variants.is_in_stock' => ['value' => true]]]]], $query->toArray()['bool']['filter'][1]);
    }

    public function testChildTaxonUsesBothNestedBoundaries(): void
    {
        $taxon = $this->taxon('shirts', $this->taxon('catalog'));
        $query = new BoolQuery();
        (new TaxonFilter())->apply($query, $this->configuration([], RequestInterface::TAXON_TYPE, $taxon));
        $taxonQuery = ['nested' => ['path' => 'product_taxons.taxon', 'query' => ['term' => ['product_taxons.taxon.code' => ['value' => 'shirts']]]]];
        self::assertSame(['bool' => ['must' => [['nested' => ['path' => 'product_taxons', 'query' => $taxonQuery]]]]], $query->toArray());
    }

    public function testRootTaxonDoesNotRestrictTheCatalogToTheRootCode(): void
    {
        $query = new BoolQuery();
        (new TaxonFilter())->apply($query, $this->configuration([], RequestInterface::TAXON_TYPE, $this->taxon('catalog')));
        self::assertEquals(['bool' => ['must' => [['bool' => new stdClass()]]]], $query->toArray());
    }

    public function testExactCodeAndFuzzyWeightedFieldsAreAlternativeMatches(): void
    {
        $query = new BoolQuery();
        (new SearchTermFilter(['name^5', 'description']))->apply($query, $this->configuration(['query' => '  Cotton%20shirt  ']));
        self::assertSame(['bool' => ['must' => [['bool' => ['should' => [
            ['term' => ['code' => 'Cotton shirt']],
            ['multi_match' => ['fields' => ['name^5', 'description'], 'query' => 'Cotton shirt', 'type' => 'most_fields', 'fuzziness' => 'AUTO']],
        ]]]]]], $query->toArray());
    }

    public function testNestedFieldsAreGroupedByPathAndInvalidExpressionsIgnored(): void
    {
        $query = new BoolQuery();
        (new SearchTermFilter([], ['main_taxon:name^2', 'broken', 'main_taxon:code', 'wrong:a:b', 'brand:name']))->apply($query, $this->configuration(['query' => 'shirt']));
        $alternatives = $query->toArray()['bool']['must'][0]['bool']['should'];
        self::assertCount(3, $alternatives);
        self::assertSame(['term' => ['code' => 'shirt']], $alternatives[0]);
        self::assertSame('main_taxon', $alternatives[1]['nested']['path']);
        self::assertSame(['main_taxon.name^2', 'main_taxon.code'], $alternatives[1]['nested']['query']['multi_match']['fields']);
        self::assertSame('brand', $alternatives[2]['nested']['path']);
        self::assertSame(['brand.name'], $alternatives[2]['nested']['query']['multi_match']['fields']);
    }
}
