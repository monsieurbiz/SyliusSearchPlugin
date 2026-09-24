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

namespace App\Search\Mapper;

use InvalidArgumentException;
use MonsieurBiz\SyliusSearchPlugin\Mapper\DocumentMappingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ProductMapperDecorator implements DocumentMappingInterface
{
    public function __construct(private DocumentMappingInterface $inner, private PropertyAccessorInterface $propertyAccessor)
    {
    }

    public function supports(object $source, string $targetClass): bool
    {
        return $this->inner->supports($source, $targetClass);
    }

    public function map(object $source, string $targetClass): object
    {
        if (!$source instanceof ProductInterface) {
            throw new InvalidArgumentException('Expected a product.');
        }
        $target = $this->inner->map($source, $targetClass);
        $this->propertyAccessor->setValue($target, 'short_description', $source->getShortDescription());

        return $target;
    }
}
