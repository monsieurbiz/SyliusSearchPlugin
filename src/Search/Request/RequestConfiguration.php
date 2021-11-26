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
    private string $type;
    private string $documentType;

    public function __construct(Request $request, string $type, string $documentType)
    {
        $this->request = $request;
        $this->type = $type;
        $this->documentType = $documentType;
    }

    public function getQueryText(): string
    {
        return $this->request->get('query', '');
    }

    public function getAppliedFilters($type = null): array
    {
        $appliedFilters = [
            'taxon' => $this->request->get('taxon', []),
            'price' =>$this->request->get('price', []),
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

    public function getLimit(): int
    {
        $limit = (int)  $this->request->get('limit');
        $availableLimits = $this->getAvailableLimits();

        if (!\in_array($limit, $availableLimits, true)) {
            $limit = reset($availableLimits);
        }

        return $limit;
    }

    public function getAvailableLimits(): array
    {
        // TODO define this in config (by query type?)
        return [9, 18, 27];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDocumentType(): string
    {
        return $this->documentType;
    }
}
