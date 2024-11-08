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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Documentable;

use Sylius\Component\Resource\Model\TranslatableInterface as OldTranslatableInterface;
use Sylius\Resource\Model\TranslatableInterface;

class Documentable implements PrefixedDocumentableInterface
{
    use DocumentableDatasourceTrait;
    use DocumentableMappingProviderTrait;

    private string $indexCode;

    private string $sourceClass;

    private string $targetClass;

    /**
     * @var array<string, string>
     */
    private array $templates;

    private array $limits;

    private ?string $prefix = null;

    public function __construct(
        string $indexCode,
        string $sourceClass,
        string $targetClass,
        array $templates,
        array $limits
    ) {
        $this->indexCode = $indexCode;
        $this->sourceClass = $sourceClass;
        $this->targetClass = $targetClass;
        $this->templates = $templates;
        $this->limits = $limits;
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
        $interface = (array) class_implements($this->getSourceClass());

        return \in_array(TranslatableInterface::class, $interface, true)
            || \in_array(OldTranslatableInterface::class, $interface, true);
    }

    public function getTemplate(string $type): ?string
    {
        return $this->templates[$type] ?? null;
    }

    public function getLimits(?string $queryType = null): array
    {
        if (null == $queryType) {
            return $this->limits;
        }

        return $this->limits[$queryType] ?? [];
    }

    public function getPrefix(): string
    {
        return $this->prefix ?? '';
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }
}
