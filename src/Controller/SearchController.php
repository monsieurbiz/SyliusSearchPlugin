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

use MonsieurBiz\SyliusSearchPlugin\Exception\UnknownRequestTypeException;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Search;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Bundle\ResourceBundle\Controller\Parameters;
use Sylius\Bundle\ResourceBundle\Controller\ParametersParserInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Registry\NonExistingServiceException;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Intl\Currencies;

class SearchController extends AbstractController
{
    protected Search $search;

    protected CurrencyContextInterface $currencyContext;

    protected LocaleContextInterface $localeContext;

    protected ChannelContextInterface $channelContext;

    protected SettingsInterface $searchSettings;

    protected ServiceRegistryInterface $documentableRegistry;

    protected ParametersParserInterface $parametersParser;

    public function __construct(
        Search $search,
        CurrencyContextInterface $currencyContext,
        LocaleContextInterface $localeContext,
        ChannelContextInterface $channelContext,
        SettingsInterface $searchSettings,
        ServiceRegistryInterface $documentableRegistry,
        ParametersParserInterface $parametersParser
    ) {
        $this->search = $search;
        $this->currencyContext = $currencyContext;
        $this->localeContext = $localeContext;
        $this->channelContext = $channelContext;
        $this->searchSettings = $searchSettings;
        $this->documentableRegistry = $documentableRegistry;
        $this->parametersParser = $parametersParser;
    }

    public function searchAction(
        Request $request,
        string $query
    ): Response {
        $documentType = ((string) $request->query->get('document_type')) ?: null;
        $documentable = $this->getDocumentable($documentType);
        $requestConfiguration = new RequestConfiguration(
            $request,
            RequestInterface::SEARCH_TYPE,
            $documentable,
            $this->searchSettings,
            $this->channelContext
        );
        $result = $this->search->search($requestConfiguration);

        return $this->render('@MonsieurBizSyliusSearchPlugin/Search/result.html.twig', [
            'documentableRegistries' => $this->getSearchEnabledDocumentables(),
            'documentable' => $result->getDocumentable(),
            'requestConfiguration' => $requestConfiguration,
            'query' => urldecode($query),
            'query_url' => $query,
            'result' => $result,
            'currencySymbol' => Currencies::getSymbol($this->currencyContext->getCurrencyCode(), $this->localeContext->getLocaleCode()),
        ]);
    }

    /**
     * Post search.
     */
    public function postAction(Request $request): RedirectResponse
    {
        $query = (array) ($request->request->all()['monsieurbiz_searchplugin_search'] ?? []);
        $query = $query['query'] ?? '';

        // With Apache a URL with a encoded slash (%2F) is provoking a 404 error on the server level
        return $this->redirect(
            $this->generateUrl(
                'monsieurbiz_search_search',
                ['query' => str_replace('%2F', '/', urlencode($query))]
            )
        );
    }

    /**
     * Perform the instant search action & display results.
     */
    public function instantAction(Request $request): Response
    {
        $results = [];
        /** @var DocumentableInterface $documentable */
        foreach ($this->getInstantSearchEnabledDocumentables() as $documentable) {
            $requestConfiguration = new RequestConfiguration(
                $request,
                RequestInterface::INSTANT_TYPE,
                $documentable,
                $this->searchSettings,
                $this->channelContext
            );

            try {
                $results[$documentable->getIndexCode()] = $this->search->search($requestConfiguration);
            } catch (UnknownRequestTypeException $e) {
                continue;
            }
        }

        return $this->render('@MonsieurBizSyliusSearchPlugin/Instant/result.html.twig', [
            'results' => $results,
        ]);
    }

    public function taxonAction(
        Request $request,
        string $documentType = 'monsieurbiz_product'
    ): Response {
        $documentable = $this->getDocumentable($documentType);
        /** @var array $syliusAttribute */
        $syliusAttribute = $request->attributes->get('_sylius', []);
        $requestConfiguration = new RequestConfiguration(
            $request,
            RequestInterface::TAXON_TYPE,
            $documentable,
            $this->searchSettings,
            $this->channelContext,
            new Parameters($this->parametersParser->parseRequestValues($syliusAttribute, $request))
        );
        $result = $this->search->search($requestConfiguration);

        return $this->render('@MonsieurBizSyliusSearchPlugin/Taxon/result.html.twig', [
            'requestConfiguration' => $requestConfiguration,
            'result' => $result,
            'currencySymbol' => Currencies::getSymbol($this->currencyContext->getCurrencyCode(), $this->localeContext->getLocaleCode()),
        ]);
    }

    protected function getDocumentable(?string $documentType): DocumentableInterface
    {
        if (null === $documentType) {
            $documentables = $this->getSearchEnabledDocumentables();

            return reset($documentables);
        }

        try {
            /** @phpstan-ignore-next-line */
            return $this->documentableRegistry->get('search.documentable.' . $documentType);
        } catch (NonExistingServiceException $exception) {
            throw new NotFoundHttpException(\sprintf('Documentable "%s" not found', $documentType));
        }
    }

    protected function getSearchEnabledDocumentables(): array
    {
        return array_filter($this->documentableRegistry->all(), function (DocumentableInterface $documentable) {
            return (bool) $this->searchSettings->getCurrentValue($this->channelContext->getChannel(), null, 'search_enabled__' . $documentable->getIndexCode());
        });
    }

    protected function getInstantSearchEnabledDocumentables(): array
    {
        return array_filter($this->documentableRegistry->all(), function (DocumentableInterface $documentable) {
            return (bool) $this->searchSettings->getCurrentValue($this->channelContext->getChannel(), null, 'instant_search_enabled__' . $documentable->getIndexCode());
        });
    }
}
