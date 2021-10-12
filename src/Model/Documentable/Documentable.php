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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Documentable;

use Sylius\Component\Resource\Model\TranslatableInterface;

class Documentable implements DocumentableInterface
{
    use DocumentableDatasourceTrait;
    use DocumentableMappingProviderTrait;
    private string $indexCode;
    private string $sourceClass;
    private string $targetClass;

    public function __construct(string $indexCode, string $sourceClass, string $targetClass)
    {
        $this->indexCode = $indexCode;
        $this->sourceClass = $sourceClass;
        $this->targetClass = $targetClass;
    }

    public function getIndexCode(): string
    {
        return $this->indexCode;
    }

    public function getSourceClass(): string
    {
        return $this->sourceClass;
    }

    public function getTargetClass(): string
    {
        return $this->targetClass;
    }

    public function isTranslatable(): bool
    {
        $interface = (array) (class_implements($this->getSourceClass()) ?? []);

        return \in_array(TranslatableInterface::class, $interface, true);
    }
}
