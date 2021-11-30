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

namespace MonsieurBiz\SyliusSearchPlugin\Controller;

use MonsieurBiz\SyliusSearchPlugin\Exception\UnknownRequestTypeException;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Search;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Currencies;

class SearchController extends AbstractController
{
    private Search $search;
    private CurrencyContextInterface $currencyContext;
    private LocaleContextInterface $localeContext;

    public function __construct(Search $search, CurrencyContextInterface $currencyContext, LocaleContextInterface $localeContext)
    {
        $this->search = $search;
        $this->currencyContext = $currencyContext;
        $this->localeContext = $localeContext;
    }

    // TODO add an optional parameter $documentType (nullable => get the default document type)
    public function searchAction(Request $request, string $query): Response
    {
        $requestConfiguration = new RequestConfiguration($request, RequestInterface::SEARCH_TYPE, 'monsieurbiz_product');
        $result = $this->search->search($requestConfiguration);

        return $this->render('@MonsieurBizSyliusSearchPlugin/Search/result.html.twig', [
            'documentable' => $result->getDocumentable(),
            'requestConfiguration' => $requestConfiguration,
            'query' => $query,
            'result' => $result,
            'limits' => $requestConfiguration->getAvailableLimits(),
            'currencySymbol' => Currencies::getSymbol($this->currencyContext->getCurrencyCode(), $this->localeContext->getLocaleCode()),
        ]);
    }

    /**
     * Post search.
     */
    public function postAction(Request $request): RedirectResponse
    {
        $query = $request->request->get('monsieurbiz_searchplugin_search')['query'] ?? '';

        return $this->redirect(
            $this->generateUrl(
                'monsieurbiz_search_search',
                ['query' => urlencode($query)]
            )
        );
    }

    /**
     * Perform the instant search action & display results.
     */
    public function instantAction(Request $request, ServiceRegistryInterface $documentableRegistry): Response
    {
        $results = [];
        /** @var DocumentableInterface $documentable */
        foreach ($documentableRegistry->all() as $documentable) {
            $requestConfiguration = new RequestConfiguration(
                $request,
                RequestInterface::INSTANT_TYPE,
                $documentable->getIndexCode()
            );
            try {
                $results[] = $this->search->search($requestConfiguration);
            } catch (UnknownRequestTypeException $e) {
                continue;
            }
        }

        return $this->render('@MonsieurBizSyliusSearchPlugin/Instant/result.html.twig', [
            'results' => $results,
        ]);
    }
}
