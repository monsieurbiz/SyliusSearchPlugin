<?php
declare(strict_types=1);

namespace Monsieurbiz\SyliusSearchPlugin\Controller;

use Monsieurbiz\SyliusSearchPlugin\Exception\NotSupportedTypeException;
use Monsieurbiz\SyliusSearchPlugin\Indexer\DocumentIndexer;
use Monsieurbiz\SyliusSearchPlugin\Model\DocumentResult;
use Monsieurbiz\SyliusSearchPlugin\Twig\Extension\RenderDocumentUrl;
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
    const DIPLAYED_PAGES_PAGER = 5;
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
        $page = $request->request->get('monsieurbiz_searchplugin_search')['page'] ?? null;

        return new RedirectResponse($this->generateUrl(
            'monsieurbiz_sylius_search_search',
            [
                'query' => urlencode($query),
                'page' => urlencode($page)
            ]
        ));
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
        $page = (int) htmlspecialchars(urldecode($request->get('page')));

        $searchResults = $this->documentIndexer->search($request->getLocale(), $query, self::MAX_DISPLAYED_ITEMS_PER_PAGE, $page);

        // Redirect to document if only one result
        if (count($searchResults) === 1) {
            /** @var DocumentResult $searchResult */
            $searchResult = current($searchResults);
            try {
                $renderDocumentUrl = new RenderDocumentUrl();
                $urlParams = $renderDocumentUrl->getUrlParams($searchResult);
                return new RedirectResponse($this->generateUrl($urlParams->getPath(), $urlParams->getParams()));
            } catch (NotSupportedTypeException $e) {
                // Return list of results if cannot redirect, so ignore Exception
            }
        }

        //Prepare pager
        $pager = $this->preparePager($page, $searchResults['total']);

        //Prepare page help with
        $pagingHelp = [
            'start' => ($page - 1) * self::MAX_DISPLAYED_ITEMS_PER_PAGE + 1,
            'end' => $searchResults['total'] < self::MAX_DISPLAYED_ITEMS_PER_PAGE ? $searchResults['total'] : $page * self::MAX_DISPLAYED_ITEMS_PER_PAGE,
            'total' => $searchResults['total']
        ];

        return $this->templatingEngine->renderResponse('@MonsieurbizSyliusSearchPlugin/Search/result.html.twig', [
            'query' => $query,
            'resultNumber' => count($searchResults),
            'results' => $searchResults['results'],
            'channel' => $this->channelContext->getChannel(),
            'currencyCode' => $this->currencyContext->getCurrencyCode(),
            'currentPage' => $page,
            'pager' => $pager,
            'pagingHelp' => $pagingHelp
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

        $searchResults = $this->documentIndexer->instant($request->getLocale(), $query, self::MAX_DISPLAYED_ITEMS_INSTANT);

        return $this->templatingEngine->renderResponse('@MonsieurbizSyliusSearchPlugin/Instant/result.html.twig', [
            'query' => $query,
            'resultNumber' => count($searchResults),
            'results' => $searchResults['results'],
            'channel' => $this->channelContext->getChannel(),
            'currencyCode' => $this->currencyContext->getCurrencyCode(),
        ]);
    }

    /**
     * @param int $page
     * @param $nbTotalItems
     * @return array
     */
    private function preparePager(int $page, int $nbTotalItems):array{
        $nbTotalPages = \ceil($nbTotalItems/self::MAX_DISPLAYED_ITEMS_PER_PAGE);
        if($nbTotalItems < self::MAX_DISPLAYED_ITEMS_PER_PAGE){
            return [1];
        } elseif($nbTotalPages <= self::DIPLAYED_PAGES_PAGER){
            return \range(1, $nbTotalPages);
        }else {
            $half = \ceil(self::DIPLAYED_PAGES_PAGER / 2);
            if($page >= $half && $page <= $nbTotalPages - $half){
                $start = $page - $half + 1;
                $end = $start + self::DIPLAYED_PAGES_PAGER - 1;
            }elseif($page < $half){
                $start = 1;
                $end = self::DIPLAYED_PAGES_PAGER;
            }elseif($page > $nbTotalPages - $half){
                $end = $nbTotalPages;
                $start = $nbTotalPages - self::DIPLAYED_PAGES_PAGER + 1;
            }
            return \range($start, $end);
        }
    }

}
