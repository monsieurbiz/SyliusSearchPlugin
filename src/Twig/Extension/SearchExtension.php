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

namespace MonsieurBiz\SyliusSearchPlugin\Twig\Extension;

use MonsieurBiz\SyliusSearchPlugin\Checker\ElasticsearchCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SearchExtension extends AbstractExtension
{
    public function __construct(
        private ElasticsearchCheckerInterface $elasticsearchChecker,
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('is_elasticsearch_available', [$this, 'isElasticsearchAvailable']),
        ];
    }

    public function isElasticsearchAvailable(): bool
    {
        return $this->elasticsearchChecker->check();
    }
}
