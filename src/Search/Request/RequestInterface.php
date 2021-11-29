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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request;

use Elastica\Query;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;

interface RequestInterface
{
    public const SEARCH_TYPE = 'search';
    public const TAXON_TYPE = 'taxon';
    public const INSTANT_TYPE = 'instant';

    public function getType(): string;

    public function getDocumentable(): DocumentableInterface;

    public function getQuery(): Query;

    public function supports(string $type, string $documentableCode): bool;

    public function setConfiguration(RequestConfiguration $configuration): void;
}
