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

use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Search;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            'query' => $query,
            'result' => $result,
            'limits' => $requestConfiguration->getAvailableLimits(),
            'currencySymbol' => Currencies::getSymbol($this->currencyContext->getCurrencyCode(), $this->localeContext->getLocaleCode()),
        ]);
    }
}
