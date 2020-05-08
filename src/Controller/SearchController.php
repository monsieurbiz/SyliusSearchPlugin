<?php
declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Controller;

use MonsieurBiz\SyliusSearchPlugin\Context\TaxonContextInterface;
use MonsieurBiz\SyliusSearchPlugin\Exception\MissingLocaleException;
use MonsieurBiz\SyliusSearchPlugin\Exception\NotSupportedTypeException;
use MonsieurBiz\SyliusSearchPlugin\Model\Config\GridConfig;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\Index\Search;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\Result;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\ResultSet;
use MonsieurBiz\SyliusSearchPlugin\Twig\Extension\RenderDocumentUrl;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{
    const SORT_ASC = 'asc';
    const SORT_DESC = 'desc';

    /** @var EngineInterface */
    private $templatingEngine;

    /** @var Search */
    private $documentSearch;

    /** @var ChannelContextInterface */
    private $channelContext;

    /** @var CurrencyContextInterface */
    private $currencyContext;

    /** @var TaxonContextInterface */
    private $taxonContext;

    /** @var GridConfig */
    private $gridConfig;

    /**
     * SearchController constructor.
     * @param EngineInterface $templatingEngine
     * @param Search $documentSearch
     * @param ChannelContextInterface $channelContext
     * @param CurrencyContextInterface $currencyContext
     * @param TaxonContextInterface $taxonContext
     * @param array $gridConfig
     */
    public function __construct(
        EngineInterface $templatingEngine,
        Search $documentSearch,
        ChannelContextInterface $channelContext,
        CurrencyContextInterface $currencyContext,
        TaxonContextInterface $taxonContext,
        array $gridConfig
    ) {
        $this->templatingEngine = $templatingEngine;
        $this->documentSearch = $documentSearch;
        $this->channelContext = $channelContext;
        $this->currencyContext = $currencyContext;
        $this->taxonContext = $taxonContext;
        $this->gridConfig = new GridConfig($gridConfig);
    }

    /**
     * Post search
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function postAction(Request $request)
    {
        $query = $request->request->get('monsieurbiz_searchplugin_search')['query'] ?? null;

        return new RedirectResponse(
            $this->generateUrl('monsieurbiz_sylius_search_search',
                ['query' => urlencode($query)])
        );
    }

    /**
     * Perform the search action & display results. User can add page, limit or sorting.
     *
     * @param Request $request
     * @return Response
     */
    public function searchAction(Request $request): Response
    {
        $query = htmlspecialchars(urldecode($request->get('query')));
        $page = max(1, (int) $request->get('page'));
        $limit = max(1, (int) $request->get('limit'));
        $sorting = $this->cleanSorting($request->get('sorting'), $this->gridConfig->getSearchSorting());

        if (!is_array($sorting) || empty($sorting)) {
            $sorting['dummy'] = self::SORT_DESC; // Not existing field to have null in ES so use the score
        }

        if (!in_array($limit, $this->gridConfig->getSearchLimits())) {
            $limit = $this->gridConfig->getSearchDefaultLimit();
        }

        // Perform search
        /** @var ResultSet $resultSet */
        $resultSet = $this->documentSearch->search(
            $request->getLocale(),
            $query,
            $limit,
            $page,
            $sorting,
            $this->gridConfig->getFilters()
        );

        // Redirect to document if only one result
        if ($resultSet->getTotalHits() === 1) {
            /** @var Result $document */
            $document = current($resultSet->getResults());
            try {
                $renderDocumentUrl = new RenderDocumentUrl();
                $urlParams = $renderDocumentUrl->getUrlParams($document);
                return new RedirectResponse($this->generateUrl($urlParams->getPath(), $urlParams->getParams()));
            } catch (NotSupportedTypeException $e) {
                // Return list of results if cannot redirect, so ignore Exception
            } catch (MissingLocaleException $e) {
                // Return list of results if locale is missing
            }
        }

        // Get number formatter for currency
        $currencyCode = $this->currencyContext->getCurrencyCode();
        $formatter = new \NumberFormatter($request->getLocale() . '@currency=' . $currencyCode, \NumberFormatter::CURRENCY);

        // Display result list
        return $this->templatingEngine->renderResponse('@MonsieurBizSyliusSearchPlugin/Search/result.html.twig', [
            'query' => $query,
            'limits' => $this->gridConfig->getSearchLimits(),
            'resultSet' => $resultSet,
            'channel' => $this->channelContext->getChannel(),
            'currencyCode' => $this->currencyContext->getCurrencyCode(),
            'moneySymbol' => $formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL)
        ]);
    }

    /**
     * Perform the instant search action & display results.
     *
     * @param Request $request
     * @return Response
     */
    public function instantAction(Request $request): Response
    {
        $query = $request->request->get('query') ?? null;
        $query = htmlspecialchars($query);

        // Perform instant search
        /** @var ResultSet $resultSet */
        $resultSet = $this->documentSearch->instant(
            $request->getLocale(),
            $query,
            $this->gridConfig->getInstantDefaultLimit()
        );

        // Display instant result list
        return $this->templatingEngine->renderResponse('@MonsieurBizSyliusSearchPlugin/Instant/result.html.twig', [
            'query' => $query,
            'resultSet' => $resultSet,
            'channel' => $this->channelContext->getChannel(),
            'currencyCode' => $this->currencyContext->getCurrencyCode(),
        ]);
    }

    /**
     * Perform the taxon action & display results.
     *
     * @param Request $request
     * @return Response
     */
    public function taxonAction(Request $request): Response
    {
        $taxon = $this->taxonContext->getTaxon();

        $page = max(1, (int) $request->get('page'));
        $limit = max(1, (int) $request->get('limit'));
        $sorting = $this->cleanSorting($request->get('sorting'), $this->gridConfig->getTaxonSorting());

        if (!is_array($sorting) || empty($sorting)) {
            $sorting['position'] = self::SORT_ASC; // Product position in taxon
        }

        if (!in_array($limit, $this->gridConfig->getTaxonLimits())) {
            $limit = $this->gridConfig->getTaxonDefaultLimit();
        }

        // Perform search
        /** @var ResultSet $resultSet */
        $resultSet = $this->documentSearch->taxon(
            $request->getLocale(),
            $taxon,
            $limit,
            $page,
            $sorting,
            $this->gridConfig->getFilters()
        );

        // Get number formatter for currency
        $currencyCode = $this->currencyContext->getCurrencyCode();
        $formatter = new \NumberFormatter($request->getLocale() . '@currency=' . $currencyCode, \NumberFormatter::CURRENCY);

        // Display result list
        return $this->templatingEngine->renderResponse('@MonsieurBizSyliusSearchPlugin/Taxon/result.html.twig', [
            'taxon' => $taxon,
            'limits' => $this->gridConfig->getTaxonLimits(),
            'resultSet' => $resultSet,
            'channel' => $this->channelContext->getChannel(),
            'currencyCode' => $this->currencyContext->getCurrencyCode(),
            'moneySymbol' => $formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL)
        ]);
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
