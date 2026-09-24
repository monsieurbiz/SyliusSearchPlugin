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

use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterBuilders\Product as Builder;
use MonsieurBiz\SyliusSearchPlugin\Tests\Support\SearchTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class FacetBuilderTest extends SearchTestCase
{
    #[DataProvider('builders')]
    public function testBuildersIgnoreOtherDocumentsOtherAggregationsAndEmptyResults(string $class, string $code, int $position): void
    {
        $builder = new $class();
        self::assertNull($builder->build($this->document('app_taxon'), $this->configuration(), $code, []));
        self::assertNull($builder->build($this->document(), $this->configuration(), 'unrelated', []));
        self::assertNull($builder->build($this->document(), $this->configuration(), $code, []));
        self::assertSame($position, $builder->getPosition());
    }

    public static function builders(): iterable
    {
        yield [Builder\AttributeFilterBuilder::class, 'attributes', 20];
        yield [Builder\OptionFilterBuilder::class, 'options', 20];
        yield [Builder\MainTaxonFilterBuilder::class, 'main_taxon', 1];
        yield [Builder\TaxonsFilterBuilder::class, 'taxons', 2];
        yield [Builder\PriceFilterBuilder::class, 'prices', 10];
    }

    #[DataProvider('attributeShapes')]
    public function testAttributeAndOptionFacetsDecodeTheirRealWrapperShapes(string $kind, bool $doubleWrapper): void
    {
        $values = ['buckets' => [['key' => 'Blue sky', 'doc_count' => 4], ['key' => 'Red', 'doc_count' => 2], ['key' => 'Unused', 'doc_count' => 0]]];
        $name = ['key' => 'Color', 'doc_count' => 6, 'values' => 'options' === $kind ? ['values' => ['values' => $values]] : $values];
        $data = ['doc_count' => 6, 'color' => ['color' => ['names' => ['buckets' => [$name]]]]];
        $data = [$kind => $doubleWrapper ? [$kind => $data] : $data];
        $builder = 'options' === $kind ? new Builder\OptionFilterBuilder() : new Builder\AttributeFilterBuilder();
        $filters = $builder->build($this->document('prefix_monsieurbiz_product'), $this->configuration([$kind => ['color' => ['Blue+sky']]]), $kind, $data);
        self::assertCount(1, $filters);
        $filter = $filters[0];
        self::assertSame('color', $filter->getCode());
        self::assertSame('Color', $filter->getLabel());
        self::assertSame($kind, $filter->getType());
        self::assertSame(6, $filter->getCount());
        self::assertCount(2, $filter->getValues());
        self::assertSame('Blue sky', $filter->getValues()[0]->getLabel());
        self::assertTrue($filter->getValues()[0]->isApplied());
        self::assertFalse($filter->getValues()[1]->isApplied());
        self::assertSame(2, $filter->getValues()[1]->getCount());
    }

    public static function attributeShapes(): iterable
    {
        yield ['attributes', false];
        yield ['attributes', true];
        yield ['options', false];
        yield ['options', true];
    }

    #[DataProvider('priceStats')]
    public function testPriceFacetRoundsOutwardsAndIncludesFreePrices(int $minimum, int $maximum, int $expectedMin, int $expectedMax): void
    {
        $filters = (new Builder\PriceFilterBuilder())->build($this->document(), $this->configuration(), 'prices', ['prices' => ['prices' => ['doc_count' => 2, 'prices_stats' => ['min' => $minimum, 'max' => $maximum]]]]);
        self::assertCount(1, $filters);
        self::assertSame($expectedMin, $filters[0]->getDefaultValue('min'));
        self::assertSame($expectedMax, $filters[0]->getDefaultValue('max'));
        self::assertSame('price', $filters[0]->getCode());
    }

    public static function priceStats(): iterable
    {
        yield [1251, 2099, 12, 21];
        yield [1000, 1000, 10, 10];
        yield [0, 0, 0, 0];
        yield [1, 99, 0, 1];
    }

    public function testZeroCountPriceAndTaxonFacetsDisappear(): void
    {
        self::assertNull((new Builder\PriceFilterBuilder())->build($this->document(), $this->configuration(), 'prices', ['prices' => ['prices' => ['doc_count' => 0, 'prices_stats' => ['min' => null, 'max' => null]]]]));
        self::assertNull((new Builder\MainTaxonFilterBuilder())->build($this->document(), $this->configuration(), 'main_taxon', ['main_taxon' => ['doc_count' => 0]]));
    }

    public function testMainTaxonUsesFirstNameAndOmitsEmptyBuckets(): void
    {
        $data = ['main_taxon' => ['doc_count' => 5, 'codes' => ['buckets' => [
            ['key' => 'shirts', 'doc_count' => 5, 'levels' => ['buckets' => [['names' => ['buckets' => [['key' => 'Shirts'], ['key' => 'Another name']]]]]]],
            ['key' => 'caps', 'doc_count' => 0],
            ['key' => 'nameless', 'doc_count' => 3],
        ]]]];
        $filters = (new Builder\MainTaxonFilterBuilder())->build($this->document(), $this->configuration(['taxon' => ['main_taxon' => ['shirts']]]), 'main_taxon', $data);
        self::assertCount(1, $filters);
        self::assertCount(1, $filters[0]->getValues());
        self::assertSame('Shirts', $filters[0]->getValues()[0]->getLabel());
        self::assertSame('shirts', $filters[0]->getValues()[0]->getValue());
        self::assertTrue($filters[0]->getValues()[0]->isApplied());
    }

    public function testTaxonPageShowsOnlyDirectChildrenButSearchCanShowAllTaxons(): void
    {
        $root = $this->taxon('catalog');
        $child = $this->taxon('shirts', $root);
        $this->taxon('small-shirts', $child);
        $buckets = [];
        foreach (['shirts', 'small-shirts', 'unrelated'] as $code) {
            $buckets[] = ['key' => $code, 'doc_count' => 2, 'names' => ['buckets' => [['key' => strtoupper($code)]]]];
        }
        $data = ['taxons' => ['taxons' => ['taxons' => ['doc_count' => 6, 'codes' => ['buckets' => $buckets]]]]];
        $builder = new Builder\TaxonsFilterBuilder();
        $taxonFilters = $builder->build($this->document(), $this->configuration([], RequestInterface::TAXON_TYPE, $root), 'taxons', $data);
        self::assertCount(1, $taxonFilters[0]->getValues());
        self::assertSame('shirts', $taxonFilters[0]->getValues()[0]->getValue());
        $searchFilters = $builder->build($this->document(), $this->configuration(), 'taxons', $data);
        self::assertCount(3, $searchFilters[0]->getValues());
    }
}
