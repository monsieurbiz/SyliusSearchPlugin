<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model\Config;

use MonsieurBiz\SyliusSearchPlugin\Exception\UnknownGridConfigType;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\HttpFoundation\Request;

class GridConfig
{
    const SEARCH_TYPE = 'search';
    const TAXON_TYPE = 'taxon';
    const INSTANT_TYPE = 'instant';

    const SORT_ASC = 'asc';
    const SORT_DESC = 'desc';

    const FALLBACK_LIMIT = 10;

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

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $type
     * @param Request $request
     * @param TaxonInterface|null $taxon
     */
    public function init(string $type, Request $request, ?TaxonInterface $taxon = null)
    {
        if ($this->isInitialized) {
            return;
        }

        switch ($type) {
            case self::SEARCH_TYPE :
                // Set type, locale, page and query
                $this->type = $type;
                $this->locale = $request->getLocale();
                $this->page = max(1, (int) $request->get('page'));
                $this->query = htmlspecialchars(urldecode($request->get('query')));

                // Set sorting
                $availableSorting = $this->config['sorting']['search'] ?? [];
                $this->sorting = $this->cleanSorting($request->get('sorting'), $availableSorting);
                if (!is_array($this->sorting) || empty($this->sorting)) {
                    $this->sorting['dummy'] = self::SORT_DESC; // Not existing field to have null in ES so use the score
                }

                // Set limit
                $this->limit = max(1, (int) $request->get('limit'));
                $this->limits = $this->config['limits']['search'] ?? [];
                if (!in_array($this->limit, $this->limits)) {
                    $this->limit = $this->config['default_limit']['search'] ?? self::FALLBACK_LIMIT;
                }

                // Set applied filters
                $this->appliedFilters = $request->get('attribute') ?? [];
                $priceFilter = $request->get('price') ?? [];

                $this->isInitialized = true;
                break;
            case self::TAXON_TYPE :
                // Set type, locale, page and taxon
                $this->type = $type;
                $this->locale = $request->getLocale();
                $this->page = max(1, (int) $request->get('page'));
                $this->taxon = $taxon;

                // Set sorting
                $availableSorting = $this->config['sorting']['taxon'] ?? [];
                $this->sorting = $this->cleanSorting($request->get('sorting'), $availableSorting);
                if (!is_array($this->sorting) || empty($this->sorting)) {
                    $this->sorting['dummy'] = self::SORT_DESC; // Not existing field to have null in ES so use the score
                }
                
                // Set applied filters
                $this->appliedFilters = $request->get('attribute') ?? [];
                $priceFilter = $request->get('price') ?? [];

                // Set limit
                $this->limit = max(1, (int) $request->get('limit'));
                $this->limits = $this->config['limits']['taxon'] ?? [];
                if (!in_array($this->limit, $this->limits)) {
                    $this->limit = $this->config['default_limit']['taxon'] ?? self::FALLBACK_LIMIT;
                }
                $this->isInitialized = true;
                break;
            case self::INSTANT_TYPE :
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
        return $this->config['filters']['attributes'] ?? [];
    }

    /**
     * @return string[]
     */
    public function getOptionFilters(): array
    {
        return $this->config['filters']['options'] ?? [];
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
     * Be sure given sort in available
     * @param $sorting
     * @param $availableSorting
     * @return array
     */
    private function cleanSorting(?array $sorting, array $availableSorting): array
    {
        if (!is_array($sorting)) {
            return  [];
        }

        foreach ($sorting as $field => $order) {
            if (!in_array($field, $availableSorting) || !in_array($order, [self::SORT_ASC, self::SORT_DESC])) {
                unset($sorting[$field]);
            }
        }
        return $sorting;
    }
}

