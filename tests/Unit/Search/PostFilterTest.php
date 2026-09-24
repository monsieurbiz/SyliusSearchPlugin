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
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\Product\AttributesPostFilter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\Product\MainTaxonPostFilter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\Product\OptionsPostFilter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\Product\PricePostFilter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\Product\ProductTaxonPostFilter;
use MonsieurBiz\SyliusSearchPlugin\Tests\Support\SearchTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class PostFilterTest extends SearchTestCase
{
    public function testAbsentSelectionsDoNotModifyTheQuery(): void
    {
        $query = new BoolQuery();
        $before = $query->toArray();
        foreach ([new AttributesPostFilter(), new OptionsPostFilter(true), new MainTaxonPostFilter(), new ProductTaxonPostFilter(), new PricePostFilter($this->channelContext())] as $filter) {
            $filter->apply($query, $this->configuration());
            self::assertSame($before, $query->toArray());
        }
    }

    public function testAttributeValuesAreOrWithinAFacetAndAcrossFacets(): void
    {
        $query = new BoolQuery();
        (new AttributesPostFilter())->apply($query, $this->configuration(['attributes' => ['material' => ['Cotton', 'Blue%20silk'], 'size' => ['L']]]));
        self::assertSame(['bool' => ['must' => [
            ['nested' => ['path' => 'attributes.material', 'query' => ['bool' => ['should' => [['term' => ['attributes.material.value.keyword' => 'Cotton']], ['term' => ['attributes.material.value.keyword' => 'Blue silk']]]]]]],
            ['nested' => ['path' => 'attributes.size', 'query' => ['bool' => ['should' => [['term' => ['attributes.size.value.keyword' => 'L']]]]]]],
        ]]], $query->toArray());
    }

    #[DataProvider('stockModes')]
    public function testOptionsKeepValueEnabledAndStockRestrictionsInsideTheSameNestedQuery(bool $stock): void
    {
        $query = new BoolQuery();
        (new OptionsPostFilter($stock))->apply($query, $this->configuration(['options' => ['color' => ['Blue%20sky', 'Red']]]));
        $nested = $query->toArray()['bool']['must'][0]['nested'];
        self::assertSame('options.color.values', $nested['path']);
        $must = [['term' => ['options.color.values.enabled' => true]]];
        if ($stock) {
            $must[] = ['term' => ['options.color.values.is_in_stock' => true]];
        }
        $must[] = ['bool' => ['should' => [['term' => ['options.color.values.value.keyword' => 'Blue sky']], ['term' => ['options.color.values.value.keyword' => 'Red']]]]];
        self::assertSame(['bool' => ['must' => $must]], $nested['query']);
    }

    public static function stockModes(): iterable
    {
        yield [false];
        yield [true];
    }

    #[DataProvider('prices')]
    public function testPriceBoundsAndChannelAreAppliedToTheSamePriceDocument(array $bounds, array $expected): void
    {
        $query = new BoolQuery();
        (new PricePostFilter($this->channelContext('WEB_FR')))->apply($query, $this->configuration(['price' => $bounds]));
        self::assertEquals(['bool' => ['must' => [['nested' => ['path' => 'prices', 'query' => ['bool' => ['must' => [
            ['term' => ['prices.channel_code' => 'WEB_FR']], ['range' => ['prices.price' => $expected]],
        ]]]]]]]], $query->toArray());
    }

    public static function prices(): iterable
    {
        yield 'minimum' => [['min' => '12.50'], ['gte' => 1250]];
        yield 'maximum' => [['max' => '20'], ['lte' => 2000]];
        yield 'both' => [['min' => '12.50', 'max' => '20'], ['gte' => 1250, 'lte' => 2000]];
        yield 'reversed' => [['min' => '20', 'max' => '12.50'], ['gte' => 1250, 'lte' => 2000]];
        yield 'free only' => [['max' => '0'], ['lte' => 0]];
    }

    public function testMainAndProductTaxonsHaveDifferentNesting(): void
    {
        $main = new BoolQuery();
        (new MainTaxonPostFilter())->apply($main, $this->configuration(['taxon' => ['main_taxon' => ['Shirts%20and%20tops']]]));
        self::assertSame('main_taxon', $main->toArray()['bool']['must'][0]['nested']['path']);
        self::assertSame(['term' => ['main_taxon.code' => ['value' => 'Shirts and tops', 'boost' => 1.0]]], $main->toArray()['bool']['must'][0]['nested']['query']['bool']['should'][0]);
        $all = new BoolQuery();
        (new ProductTaxonPostFilter())->apply($all, $this->configuration(['taxons' => ['shirts', 'caps']]));
        $nested = $all->toArray()['bool']['must'][0]['nested'];
        self::assertSame('product_taxons', $nested['path']);
        self::assertSame('product_taxons.taxon', $nested['query']['nested']['path']);
        self::assertSame([['term' => ['product_taxons.taxon.code' => ['value' => 'shirts', 'boost' => 1.0]]], ['term' => ['product_taxons.taxon.code' => ['value' => 'caps', 'boost' => 1.0]]]], $nested['query']['nested']['query']['bool']['should']);
    }

    public function testZeroTaxonCodeIsARealSelection(): void
    {
        $query = new BoolQuery();
        (new MainTaxonPostFilter())->apply($query, $this->configuration(['taxon' => ['main_taxon' => ['0', '']]]));
        self::assertSame('0', $query->toArray()['bool']['must'][0]['nested']['query']['bool']['should'][0]['term']['main_taxon.code']['value']);
    }
}
