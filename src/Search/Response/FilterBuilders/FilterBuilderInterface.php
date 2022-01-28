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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterBuilders;

use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterInterface;

interface FilterBuilderInterface
{
    /**
     * @return FilterInterface[]|null
     */
    public function build(
        DocumentableInterface $documentable,
        RequestConfiguration $requestConfiguration,
        string $aggregationCode,
        array $aggregationData
    ): ?array;

    public function getPosition(): int;
}
