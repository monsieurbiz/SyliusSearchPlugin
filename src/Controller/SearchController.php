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

namespace MonsieurBiz\SyliusSearchPlugin\Controller;

use MonsieurBiz\SyliusSearchPlugin\Context\TaxonContextInterface;
use MonsieurBiz\SyliusSearchPlugin\Exception\MissingLocaleException;
use MonsieurBiz\SyliusSearchPlugin\Exception\NotSupportedTypeException;
use MonsieurBiz\SyliusSearchPlugin\Helper\RenderDocumentUrlHelper;
use MonsieurBiz\SyliusSearchPlugin\Model\Config\GridConfig;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\Index\Search;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\Result;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\ResultSet;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class SearchController extends AbstractController
{
    public const SORT_ASC = 'asc';

    public const SORT_DESC = 'desc';

    /** @var Environment */
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

    /** @var RenderDocumentUrlHelper */
    private $renderDocumentUrlHelper;

    public function __construct(
        Environment $templatingEngine,
        Search $documentSearch,
        ChannelContextInterface $channelContext,
        CurrencyContextInterface $currencyContext,
        TaxonContextInterface $taxonContext,
        GridConfig $gridConfig,
        RenderDocumentUrlHelper $renderDocumentUrlHelper
    ) {
        $this->templatingEngine = $templatingEngine;
        $this->documentSearch = $documentSearch;
        $this->channelContext = $channelContext;
        $this->currencyContext = $currencyContext;
        $this->taxonContext = $taxonContext;
        $this->gridConfig = $gridConfig;
        $this->renderDocumentUrlHelper = $renderDocumentUrlHelper;
    }

    /**
     * Post search.
     *
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
     */
    public function searchAction(Request $request): Response
    {
        // Init grid config depending on request
        $this->gridConfig->init(GridConfig::SEARCH_TYPE, $request);

        // Perform search
        /** @var ResultSet $resultSet */
        $resultSet = $this->documentSearch->search($this->gridConfig);

        // Redirect to document if only one result and no filter applied
        $appliedFilters = $this->gridConfig->getAppliedFilters();
        if (1 === $resultSet->getTotalHits() && empty($appliedFilters)) {
            /** @var Result $document */
            $document = current($resultSet->getResults());

            try {
                $urlParams = $this->renderDocumentUrlHelper->getUrlParams($document);

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
        return new Response($this->templatingEngine->render('@MonsieurBizSyliusSearchPlugin/Search/result.html.twig', [
            'query' => $this->gridConfig->getQuery(),
            'limits' => $this->gridConfig->getLimits(),
            'resultSet' => $resultSet,
            'channel' => $this->channelContext->getChannel(),
            'currencyCode' => $this->currencyContext->getCurrencyCode(),
            'moneySymbol' => $formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL),
            'gridConfig' => $this->gridConfig,
        ]));
    }

    /**
     * Perform the instant search action & display results.
     */
    public function instantAction(Request $request): Response
    {
        // Init grid config depending on request
        $this->gridConfig->init(GridConfig::INSTANT_TYPE, $request);

        // Perform instant search
        /** @var ResultSet $resultSet */
        $resultSet = $this->documentSearch->instant($this->gridConfig);

        // Display instant result list
        return new Response($this->templatingEngine->render('@MonsieurBizSyliusSearchPlugin/Instant/result.html.twig', [
            'query' => $this->gridConfig->getQuery(),
            'resultSet' => $resultSet,
            'channel' => $this->channelContext->getChannel(),
            'currencyCode' => $this->currencyContext->getCurrencyCode(),
            'gridConfig' => $this->gridConfig,
        ]));
    }

    /**
     * Perform the taxon action & display results.
     */
    public function taxonAction(Request $request): Response
    {
        // Init grid config depending on request
        $this->gridConfig->init(GridConfig::TAXON_TYPE, $request, $this->taxonContext->getTaxon());

        // Perform search
        /** @var ResultSet $resultSet */
        $resultSet = $this->documentSearch->taxon($this->gridConfig);

        // Get number formatter for currency
        $currencyCode = $this->currencyContext->getCurrencyCode();
        $formatter = new \NumberFormatter($request->getLocale() . '@currency=' . $currencyCode, \NumberFormatter::CURRENCY);

        // Display result list
        return new Response($this->templatingEngine->render('@MonsieurBizSyliusSearchPlugin/Taxon/result.html.twig', [
            'taxon' => $this->gridConfig->getTaxon(),
            'limits' => $this->gridConfig->getLimits(),
            'resultSet' => $resultSet,
            'channel' => $this->channelContext->getChannel(),
            'currencyCode' => $this->currencyContext->getCurrencyCode(),
            'moneySymbol' => $formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL),
            'gridConfig' => $this->gridConfig,
        ]));
    }
}
