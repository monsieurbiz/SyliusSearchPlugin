<?php
declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Controller;

use MonsieurBiz\SyliusSearchPlugin\Exception\MissingLocaleException;
use MonsieurBiz\SyliusSearchPlugin\Exception\NotSupportedTypeException;
use MonsieurBiz\SyliusSearchPlugin\Document\DocumentSearch;
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
    /** @var EngineInterface */
    private $templatingEngine;

    /** @var DocumentSearch */
    private $documentSearch;

    /** @var ChannelContextInterface */
    private $channelContext;

    /** @var CurrencyContextInterface */
    private $currencyContext;

    /** @var int[] */
    private $limits;

    /** @var int */
    private $searchDefaultLimit;

    /** @var int */
    private $instantDefaultLimit;

    /**
     * SearchController constructor.
     * @param EngineInterface $templatingEngine
     * @param DocumentSearch $documentSearch
     * @param ChannelContextInterface $channelContext
     * @param CurrencyContextInterface $currencyContext
     * @param array $limits
     * @param int $searchDefaultLimit
     * @param int $instantDefaultLimit
     */
    public function __construct(
        EngineInterface $templatingEngine,
        DocumentSearch $documentSearch,
        ChannelContextInterface $channelContext,
        CurrencyContextInterface $currencyContext,
        array $limits,
        int $searchDefaultLimit,
        int $instantDefaultLimit
    ) {
        $this->templatingEngine = $templatingEngine;
        $this->documentSearch = $documentSearch;
        $this->channelContext = $channelContext;
        $this->currencyContext = $currencyContext;
        $this->limits = $limits;
        $this->searchDefaultLimit = $searchDefaultLimit;
        $this->instantDefaultLimit = $instantDefaultLimit;
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

        if (!in_array($limit, $this->limits)) {
            $limit = $this->searchDefaultLimit;
        }

        // Perform search
        /** @var ResultSet $resultSet */
        $resultSet = $this->documentSearch->search(
            $request->getLocale(),
            $query,
            $limit,
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
            'limits' => $this->limits,
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
        $resultSet = $this->documentSearch->instant(
            $request->getLocale(),
            $query,
            $this->instantDefaultLimit
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
