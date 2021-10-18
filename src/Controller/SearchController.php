<?php

namespace MonsieurBiz\SyliusSearchPlugin\Controller;

use MonsieurBiz\SyliusSearchPlugin\Search\RequestFactory;
use MonsieurBiz\SyliusSearchPlugin\Search\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{
    private RequestFactory $requestFactory;
    private Search $search;

    public function __construct(RequestFactory $requestFactory, Search $search)
    {
        $this->requestFactory = $requestFactory;
        $this->search = $search;
    }

    // TODO add an optional parameter $documentType (nullable => get the default document type)
    public function searchAction(Request $request, string $query): Response
    {
        // TODO create a requestConfiguration (like \Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration without metadata)
        $elasticsearchRequest = $this->requestFactory->create(RequestInterface::SEARCH_TYPE, 'monsieurbiz_product');
        $elasticsearchRequest->setQueryParameters(['query_text' => $query]);

        $result = $this->search->query($elasticsearchRequest);

        return $this->render('@MonsieurBizSyliusSearchPlugin/Search/result.html.twig', [
            'documentable' => $elasticsearchRequest->getDocumentable(),
            'query' => $query,
            'result' => $result,
        ]);
    }
}
