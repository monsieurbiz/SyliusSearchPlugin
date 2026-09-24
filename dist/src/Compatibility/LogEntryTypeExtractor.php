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

namespace App\Compatibility;

use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Symfony\Component\PropertyInfo\Extractor\ConstructorArgumentTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\TypeInfo\Exception\InvalidArgumentException;
use Symfony\Component\TypeInfo\Type;

/** Test-harness workaround for Gedmo's redundant `Loggable|object` template bound. */
final class LogEntryTypeExtractor implements PropertyTypeExtractorInterface, ConstructorArgumentTypeExtractorInterface
{
    public function __construct(private PhpStanExtractor $inner, private PhpDocExtractor $phpDoc)
    {
    }

    public function getType(string $class, string $property, array $context = []): ?Type
    {
        try {
            return $this->inner->getType($class, $property, $context);
        } catch (InvalidArgumentException $exception) {
            if (!is_a($class, AbstractLogEntry::class, true) || 'Cannot create union with both "object" and class type.' !== $exception->getMessage()) {
                throw $exception;
            }

            return $this->phpDoc->getType($class, $property, $context);
        }
    }

    public function getTypes(string $class, string $property, array $context = []): ?array
    {
        return $this->inner->getTypes($class, $property, $context);
    }

    public function getTypesFromConstructor(string $class, string $property): ?array
    {
        return $this->inner->getTypesFromConstructor($class, $property);
    }

    public function getTypeFromConstructor(string $class, string $property): ?Type
    {
        return $this->inner->getTypeFromConstructor($class, $property);
    }
}
