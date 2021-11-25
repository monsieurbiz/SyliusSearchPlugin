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

use Symfony\Component\HttpFoundation\Request;

final class RequestConfiguration
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getQueryText(): string
    {
        return $this->request->get('query', '');
    }

    public function getAppliedFilters($type = null): array
    {
        $appliedFilters = [
            'taxon' => $this->request->get('taxon', []),
            'attributes' => $this->request->get('attributes', []),
            'options' => $this->request->get('options', []),
        ];

        return null !== $type ? ($appliedFilters[$type] ?? []) : $appliedFilters;
    }

    public function getSorting(): array
    {
        return $this->request->get('sorting', []);
    }

    public function getPage(): int
    {
        return (int) $this->request->get('page', 1);
    }
}
