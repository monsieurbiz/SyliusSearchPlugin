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

namespace MonsieurBiz\SyliusSearchPlugin\Event;

use ArrayObject;
use Symfony\Contracts\EventDispatcher\Event;

class MappingProviderEvent extends Event
{
    public const EVENT_NAME = 'monsieurbiz.search.mapping.provider';

    private string $indexCode;

    /**
     * @var ArrayObject<string, array>|null
     */
    private ?ArrayObject $mapping;

    /**
     * @param string $indexCode
     * @param ArrayObject<string, array>|null $mapping
     */
    public function __construct(string $indexCode, ?ArrayObject $mapping)
    {
        $this->indexCode = $indexCode;
        $this->mapping = $mapping;
    }

    public function getIndexCode(): string
    {
        return $this->indexCode;
    }

    /**
     * @return ArrayObject<string, array>|null
     */
    public function getMapping(): ?ArrayObject
    {
        return $this->mapping;
    }
}
