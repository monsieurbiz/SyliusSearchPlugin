<?php
declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Controller;

use MonsieurBiz\SyliusSearchPlugin\Exception\MissingLocaleException;
use MonsieurBiz\SyliusSearchPlugin\Exception\NotSupportedTypeException;
use MonsieurBiz\SyliusSearchPlugin\Indexer\DocumentIndexer;
use MonsieurBiz\SyliusSearchPlugin\Model\DocumentResult;
use MonsieurBiz\SyliusSearchPlugin\Model\ResultSet;
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
    const MAX_DISPLAYED_ITEMS_PER_PAGE = 9;
    const DISPLAYED_PAGES_PAGER = 5;
    const MAX_DISPLAYED_ITEMS_INSTANT = 10;

    /** @var EngineInterface */
    private $templatingEngine;

    /** @var DocumentIndexer */
    private $documentIndexer;

    /** @var ChannelContextInterface */
    private $channelContext;

    /** @var CurrencyContextInterface */
    private $currencyContext;

    /**
     * SearchController constructor.
     * @param EngineInterface $templatingEngine
     * @param DocumentIndexer $documentIndexer
     * @param ChannelContextInterface $channelContext
     * @param CurrencyContextInterface $currencyContext
     */
    public function __construct(
        EngineInterface $templatingEngine,
        DocumentIndexer $documentIndexer,
        ChannelContextInterface $channelContext,
        CurrencyContextInterface $currencyContext
    ) {
        $this->templatingEngine = $templatingEngine;
        $this->documentIndexer = $documentIndexer;
        $this->channelContext = $channelContext;
        $this->currencyContext = $currencyContext;
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
        $page = (int) ($request->request->get('monsieurbiz_searchplugin_search')['page'] ?? null);

        $requestParam = [];
        $requestParam['query'] = urlencode($query);

        if ($page) {
            $requestParam['page'] = $page;
        }

        return new RedirectResponse($this->generateUrl('monsieurbiz_sylius_search_search', $requestParam));
    }

    /**
     * Perform the search action & display results.
     *
     * @param Request $request
     * @return Response
     */
    public function searchAction(Request $request): Response
    {
        $query = htmlspecialchars(urldecode($request->get('query')));
        $page = max(1, (int) $request->get('page'));

        // Perform search
        /** @var ResultSet $resultSet */
        $resultSet = $this->documentIndexer->search(
            $request->getLocale(),
            $query,
            self::MAX_DISPLAYED_ITEMS_PER_PAGE,
            $page
        );

        // Redirect to document if only one result
        if ($resultSet->getTotalHits() === 1) {
            /** @var DocumentResult $document */
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

        // Display result list
        return $this->templatingEngine->renderResponse('@MonsieurBizSyliusSearchPlugin/Search/result.html.twig', [
            'query' => $query,
            'resultSet' => $resultSet,
            'channel' => $this->channelContext->getChannel(),
            'currencyCode' => $this->currencyContext->getCurrencyCode(),
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
        $resultSet = $this->documentIndexer->instant(
            $request->getLocale(),
            $query,
            self::MAX_DISPLAYED_ITEMS_PER_PAGE
        );

        // Display instant result list
        return $this->templatingEngine->renderResponse('@MonsieurBizSyliusSearchPlugin/Instant/result.html.twig', [
            'query' => $query,
            'resultSet' => $resultSet,
            'channel' => $this->channelContext->getChannel(),
            'currencyCode' => $this->currencyContext->getCurrencyCode(),
        ]);
    }
}
