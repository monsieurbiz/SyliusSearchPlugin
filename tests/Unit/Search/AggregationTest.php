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

use Elastica\Query\Nested;
use Elastica\Query\Term;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Aggregation as Agg;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\AggregationBuilder;
use MonsieurBiz\SyliusSearchPlugin\Tests\Support\SearchableAttribute;
use MonsieurBiz\SyliusSearchPlugin\Tests\Support\SearchableOption;
use MonsieurBiz\SyliusSearchPlugin\Tests\Support\SearchTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

final class AggregationTest extends SearchTestCase
{
    public function testPriceFacetDropsOnlyItsOwnSelectionAndKeepsChannelScope(): void
    {
        $other = new Term(['enabled' => true]);
        $attribute = $this->nested('attributes.color');
        $result = (new Agg\PriceAggregation($this->channelContext('WEB_FR')))->build('price', [$this->nested('prices'), $other, $attribute])->toArray();
        self::assertSame(['bool' => ['must' => [$other->toArray(), $attribute->toArray()]]], $result['filter']);
        $prices = $result['aggs']['prices'];
        self::assertSame(['path' => 'prices'], $prices['nested']);
        self::assertSame(['term' => ['prices.channel_code' => ['value' => 'WEB_FR', 'boost' => 1.0]]], $prices['aggs']['prices']['filter']);
        self::assertSame(['field' => 'prices.price'], $prices['aggs']['prices']['aggs']['prices_stats']['stats']);
    }

    public function testMainTaxonFacetRetainsOtherSelectedFilters(): void
    {
        $price = $this->nested('prices');
        $result = (new Agg\MainTaxonAggregation())->build('main_taxon', [$this->nested('main_taxon'), $price])->toArray();
        self::assertSame(['bool' => ['must' => [$price->toArray()]]], $result['filter']);
        self::assertSame(['path' => 'main_taxon'], $result['aggs']['main_taxon']['nested']);
        self::assertSame('main_taxon.code', $result['aggs']['main_taxon']['aggs']['codes']['terms']['field']);
        self::assertSame('main_taxon.name', $result['aggs']['main_taxon']['aggs']['codes']['aggs']['levels']['aggs']['names']['terms']['field']);
    }

    public function testTaxonFacetUsesTheNextTreeLevelAndDropsItsOwnSelection(): void
    {
        $taxon = $this->taxon('shirts', $this->taxon('catalog'));
        $price = $this->nested('prices');
        $result = (new Agg\TaxonsAggregation())->build(['taxons' => $taxon], [$this->nested('product_taxons'), $price])->toArray();
        self::assertSame(['bool' => ['must' => [$price->toArray()]]], $result['filter']);
        self::assertSame(['path' => 'product_taxons'], $result['aggs']['taxons']['nested']);
        $nested = $result['aggs']['taxons']['aggs']['taxons'];
        self::assertSame(['path' => 'product_taxons.taxon'], $nested['nested']);
        self::assertSame(['term' => ['product_taxons.taxon.level' => ['value' => 2]]], $nested['aggs']['taxons']['filter']);
    }

    #[DataProvider('resourceModes')]
    public function testResourceFacetsRequireAFilterableResourceWithACode(string $kind, bool $filterable, ?string $code, bool $expected): void
    {
        $resource = 'attributes' === $kind ? new SearchableAttribute() : new SearchableOption();
        $resource->setCode($code);
        $resource->setFilterable($filterable);
        $builder = 'attributes' === $kind ? new Agg\ProductAttributeAggregation() : new Agg\ProductOptionAggregation(false);
        $result = $builder->build($resource, []);
        if (!$expected) {
            self::assertNull($result);

            return;
        }
        self::assertSame($code, $result->getName());
        self::assertSame($kind . '.' . $code, $result->toArray()['aggs'][$code]['nested']['path']);
    }

    public static function resourceModes(): iterable
    {
        foreach (['attributes', 'options'] as $kind) {
            yield [$kind, true, 'color', true];
            yield [$kind, false, 'color', false];
            yield [$kind, true, null, false];
        }
    }

    public function testAttributeFacetScopesOuterAndSiblingFiltersWithoutSelfFiltering(): void
    {
        $color = new SearchableAttribute();
        $color->setCode('color');
        $color->setFilterable(true);
        $price = $this->nested('prices');
        $material = $this->nested('attributes.material');
        $result = (new Agg\ProductAttributesAggregation())->build([$color], [$this->nested('attributes.color'), $material, $price])->toArray();
        self::assertSame(['bool' => ['must' => [$price->toArray()]]], $result['filter']);
        $colorAggregation = $result['aggs']['attributes']['aggs']['color'];
        self::assertSame(['bool' => ['must' => [$material->toArray()]]], $colorAggregation['filter']);
        self::assertSame('attributes.color.value.keyword', $colorAggregation['aggs']['color']['aggs']['names']['aggs']['values']['terms']['field']);
    }

    #[DataProvider('stockModes')]
    public function testOptionFacetScopesSiblingsAndEnforcesEnabledAndOptionalStock(bool $stock): void
    {
        $color = new SearchableOption();
        $color->setCode('color');
        $color->setFilterable(true);
        $price = $this->nested('prices');
        $size = $this->nested('options.size.values');
        $result = (new Agg\ProductOptionsAggregation(new Agg\ProductOptionAggregation($stock)))->build([$color], [$this->nested('options.color.values'), $size, $price])->toArray();
        self::assertSame(['bool' => ['must' => [$price->toArray()]]], $result['filter']);
        $inner = $result['aggs']['options']['aggs']['color'];
        self::assertSame(['bool' => ['must' => [$size->toArray()]]], $inner['filter']);
        $values = $inner['aggs']['color']['aggs']['names']['aggs']['values']['aggs']['values'];
        $expected = [['term' => ['options.color.values.enabled' => ['value' => true]]]];
        if ($stock) {
            $expected[] = ['term' => ['options.color.values.is_in_stock' => ['value' => true]]];
        }
        self::assertSame(['bool' => ['must' => $expected]], $values['filter']);
        self::assertSame('options.color.values.value.keyword', $values['aggs']['values']['terms']['field']);
    }

    public static function stockModes(): iterable
    {
        yield [false];
        yield [true];
    }

    public function testUnsupportedAggregationShapesAreNotClaimed(): void
    {
        foreach ([new Agg\MainTaxonAggregation(), new Agg\TaxonsAggregation(), new Agg\PriceAggregation($this->channelContext()), new Agg\ProductAttributeAggregation(), new Agg\ProductAttributesAggregation(), new Agg\ProductOptionAggregation(false), new Agg\ProductOptionsAggregation(new Agg\ProductOptionAggregation(false))] as $builder) {
            self::assertNull($builder->build('unknown', []));
            self::assertNull($builder->build([], []));
        }
    }

    public function testNonFilterableCollectionsAreOmittedRatherThanTreatedAsUnknown(): void
    {
        $attribute = new SearchableAttribute();
        $attribute->setCode('color');
        self::assertFalse((new Agg\ProductAttributesAggregation())->build([$attribute], []));
        $option = new SearchableOption();
        $option->setCode('size');
        self::assertFalse((new Agg\ProductOptionsAggregation(new Agg\ProductOptionAggregation(false)))->build([$option], []));
        $dispatcher = new AggregationBuilder([new Agg\ProductAttributesAggregation(), new Agg\PriceAggregation($this->channelContext())]);
        $result = $dispatcher->buildAggregations([[], '', [$attribute], 'price'], []);
        self::assertCount(1, $result);
        self::assertSame('prices', reset($result)->getName());
    }

    public function testUnknownNonemptyAggregationIsAConfigurationError(): void
    {
        $this->expectException(RuntimeException::class);
        (new AggregationBuilder([new Agg\MainTaxonAggregation()]))->buildAggregations(['unknown'], []);
    }

    private function nested(string $path): Nested
    {
        return (new Nested())->setPath($path)->setQuery(new Term(['selected' => true]));
    }
}
