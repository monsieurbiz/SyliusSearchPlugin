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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Config;

use MonsieurBiz\SyliusSearchPlugin\Exception\UnknownGridConfigType;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Product\Model\ProductAttribute;
use Sylius\Component\Product\Model\ProductOption;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

class GridConfig
{
    public const SEARCH_TYPE = 'search';
    public const TAXON_TYPE = 'taxon';
    public const INSTANT_TYPE = 'instant';

    public const SORT_ASC = 'asc';
    public const SORT_DESC = 'desc';

    public const FALLBACK_LIMIT = 10;

    /** @var array */
    private $config;

    /** @var string[] */
    private $isInitialized = false;

    /** @var string */
    private $type;

    /** @var string */
    private $locale;

    /** @var string */
    private $query;

    /** @var int */
    private $page;

    /** @var int[] */
    private $limits;

    /** @var int */
    private $limit;

    /** @var string[] */
    private $sorting;

    /** @var TaxonInterface|null */
    private $taxon;

    /** @var array */
    private $appliedFilters;

    /**
     * @var array|null
     */
    private $filterableAttributes;

    /**
     * @var array|null
     */
    private $filterableOptions;

    /**
     * @var RepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var RepositoryInterface
     */
    private $productOptionRepository;

    /**
     * GridConfig constructor.
     *
     * @param array $config
     * @param RepositoryInterface $productAttributeRepository
     * @param RepositoryInterface $productOptionRepository
     */
    public function __construct(array $config, RepositoryInterface $productAttributeRepository, RepositoryInterface $productOptionRepository)
    {
        $this->config = $config;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productOptionRepository = $productOptionRepository;
    }

    /**
     * @param string $type
     * @param Request $request
     * @param TaxonInterface|null $taxon
     */
    public function init(string $type, Request $request, ?TaxonInterface $taxon = null): void
    {
        if ($this->isInitialized) {
            return;
        }

        switch ($type) {
            case self::SEARCH_TYPE:
                // Set type, locale, page and query
                $this->type = $type;
                $this->locale = $request->getLocale();
                $this->page = max(1, (int) $request->get('page'));
                $this->query = htmlspecialchars(urldecode($request->get('query')));

                // Set sorting
                $availableSorting = $this->config['sorting']['search'] ?? [];
                $this->sorting = $this->cleanSorting($request->get('sorting'), $availableSorting);
                if (!\is_array($this->sorting) || empty($this->sorting)) {
                    $this->sorting['dummy'] = self::SORT_DESC; // Not existing field to have null in ES so use the score
                }

                // Set limit
                $this->limit = max(1, (int) $request->get('limit'));
                $this->limits = $this->config['limits']['search'] ?? [];
                if (!\in_array($this->limit, $this->limits, true)) {
                    $this->limit = $this->config['default_limit']['search'] ?? self::FALLBACK_LIMIT;
                }

                // Set applied filters
                $this->appliedFilters = $request->get('attribute') ?? [];
                if ($priceFilter = $request->get('price')) {
                    $this->appliedFilters['price'] = $priceFilter;
                }

                $this->isInitialized = true;
                break;
            case self::TAXON_TYPE:
                // Set type, locale, page and taxon
                $this->type = $type;
                $this->locale = $request->getLocale();
                $this->page = max(1, (int) $request->get('page'));
                $this->taxon = $taxon;

                // Set sorting
                $availableSorting = $this->config['sorting']['taxon'] ?? [];
                $this->sorting = $this->cleanSorting($request->get('sorting'), $availableSorting);
                if (!\is_array($this->sorting) || empty($this->sorting)) {
                    $this->sorting['position'] = self::SORT_ASC;
                }

                // Set applied filters
                $this->appliedFilters = $request->get('attribute') ?? [];
                if ($priceFilter = $request->get('price')) {
                    $this->appliedFilters['price'] = $priceFilter;
                }

                // Set limit
                $this->limit = max(1, (int) $request->get('limit'));
                $this->limits = $this->config['limits']['taxon'] ?? [];
                if (!\in_array($this->limit, $this->limits, true)) {
                    $this->limit = $this->config['default_limit']['taxon'] ?? self::FALLBACK_LIMIT;
                }
                $this->isInitialized = true;
                break;
            case self::INSTANT_TYPE:
                // Set type, locale, page and query
                $this->type = $type;
                $this->locale = $request->getLocale();
                $this->page = 1;
                $this->query = htmlspecialchars(urldecode($request->get('query')));

                // Set limit
                $this->limit = $this->config['default_limit']['instant'] ?? self::FALLBACK_LIMIT;
                $this->isInitialized = true;
                break;
            default:
                throw new UnknownGridConfigType();
        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int[]
     */
    public function getLimits(): array
    {
        return $this->limits;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return string[]
     */
    public function getSorting(): array
    {
        return $this->sorting;
    }

    /**
     * @return string[]
     */
    public function getAttributeFilters(): array
    {
        if (null === $this->filterableAttributes) {
            $attributes = $this->productAttributeRepository->findBy([
                'filterable' => true,
            ]);
            $this->filterableAttributes = [];
            /** @var ProductAttribute $attribute */
            foreach ($attributes as $attribute) {
                $this->filterableAttributes[] = $attribute->getCode();
            }
        }

        return $this->filterableAttributes;
    }

    /**
     * @return string[]
     */
    public function getOptionFilters(): array
    {
        if (null === $this->filterableOptions) {
            $options = $this->productOptionRepository->findBy([
                'filterable' => true,
            ]);
            $this->filterableOptions = [];
            /** @var ProductOption $option */
            foreach ($options as $option) {
                $this->filterableOptions[] = $option->getCode();
            }
        }

        return $this->filterableOptions;
    }

    /**
     * @return bool
     */
    public function haveToApplyManuallyFilters(): bool
    {
        return $this->config['filters']['apply_manually'] ?? false;
    }

    /**
     * @return bool
     */
    public function useMainTaxonForFilter(): bool
    {
        return $this->config['filters']['use_main_taxon'] ?? false;
    }

    /**
     * @return string[]
     */
    public function getFilters(): array
    {
        return array_merge($this->getAttributeFilters(), $this->getOptionFilters());
    }

    /**
     * @return array
     */
    public function getAppliedFilters(): array
    {
        return $this->appliedFilters;
    }

    /**
     * @return TaxonInterface|null
     */
    public function getTaxon(): ?TaxonInterface
    {
        return $this->taxon;
    }

    /**
     * Be sure given sort in available.
     *
     * @param $sorting
     * @param $availableSorting
     *
     * @return array
     */
    private function cleanSorting(?array $sorting, array $availableSorting): array
    {
        if (!\is_array($sorting)) {
            return  [];
        }

        foreach ($sorting as $field => $order) {
            if (!\in_array($field, $availableSorting, true) || !\in_array($order, [self::SORT_ASC, self::SORT_DESC], true)) {
                unset($sorting[$field]);
            }
        }

        return $sorting;
    }
}
