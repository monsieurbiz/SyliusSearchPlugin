<?php

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
            'attributes' => $this->request->get('attributes', []),
            'options' => $this->request->get('options', []),
        ];

        return null !== $type ? ($appliedFilters[$type] ?? []) : $appliedFilters;
    }
}
