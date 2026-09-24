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

use MonsieurBiz\SyliusSearchPlugin\Helper\SlugHelper;
use MonsieurBiz\SyliusSearchPlugin\Search\Filter\Filter;
use MonsieurBiz\SyliusSearchPlugin\Search\Filter\FilterValue;
use MonsieurBiz\SyliusSearchPlugin\Search\Filter\RangeFilter;
use MonsieurBiz\SyliusSearchPlugin\Tests\Support\SearchTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class FilterValueTest extends SearchTestCase
{
    #[DataProvider('labels')]
    public function testLabelsRoundTripWithoutLossOrCollisions(string $label): void
    {
        $value = new FilterValue($label, 3);
        self::assertSame($label, SlugHelper::toLabel($value->getSlug()));
        self::assertSame($label, $value->getValue());
        self::assertSame(3, $value->getCount());
        self::assertFalse($value->isApplied());
        $value->setValue('Replacement + /');
        self::assertSame('Replacement + /', SlugHelper::toLabel($value->getSlug()));
        self::assertSame($label, $value->getLabel());
    }

    public static function labels(): iterable
    {
        foreach (['Blue sky', 'C++', '50%', 'a/b', 'café', '0', '', '日本語'] as $label) {
            yield [$label];
        }
    }

    public function testAppliedValuesUseTheExplicitValueNotItsDisplayLabel(): void
    {
        $filter = new Filter($this->configuration(['attributes' => ['material' => ['Cotton+blue']]]), 'material', 'Material', 8, 'attributes');
        $filter->addValue('Cotton label', 4, 'Cotton blue');
        $filter->addValue('Wool', 3);
        self::assertTrue($filter->getValues()[0]->isApplied());
        self::assertFalse($filter->getValues()[1]->isApplied());
        self::assertSame([$filter->getValues()[0]], $filter->getAppliedValues());
        self::assertSame('material', $filter->getCode());
        self::assertSame('Material', $filter->getLabel());
        self::assertSame(8, $filter->getCount());
        $filter->setType('custom');
        self::assertSame('custom', $filter->getType());
    }

    public function testTopLevelSelectionsAreSupportedAndUnselectedValuesRemain(): void
    {
        $filter = new Filter($this->configuration(['taxons' => ['shirts']]), 'taxons', 'Taxons', 10);
        $filter->addValue('Caps', 2, 'caps');
        $filter->addValue('Shirts', 8, 'shirts');
        self::assertCount(2, $filter->getValues());
        self::assertSame([1 => $filter->getValues()[1]], $filter->getAppliedValues());
    }

    public function testRangeDefaultsRemainDistinctFromUserSelections(): void
    {
        $range = new RangeFilter($this->configuration(['price' => ['min' => '12.5']]), 'price', 'Price', 'Minimum', 'Maximum', 0, 100);
        self::assertSame('price', $range->getCode());
        self::assertSame('Price', $range->getLabel());
        self::assertSame('range', $range->getType());
        self::assertSame('12.5', $range->getValues()[0]->getValue());
        self::assertTrue($range->getValues()[0]->isApplied());
        self::assertSame('100', $range->getValues()[1]->getValue());
        self::assertFalse($range->getValues()[1]->isApplied());
        self::assertSame(0, $range->getDefaultValue('min'));
        self::assertSame(100, $range->getDefaultValue('max'));
        self::assertSame('min', $range->getValueType('Minimum'));
        self::assertSame('max', $range->getValueType('Maximum'));
        self::assertCount(1, $range->getAppliedValues());
    }

    public function testGenericRangeSelectionTakesPrecedenceOverPrice(): void
    {
        $range = new RangeFilter($this->configuration(['range' => ['max' => '20'], 'price' => ['max' => '50']]), 'price', 'Price', 'Min', 'Max', 0, 100);
        self::assertSame('20', $range->getValues()[1]->getValue());
    }
}
