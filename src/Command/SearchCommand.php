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

namespace MonsieurBiz\SyliusSearchPlugin\Command;

use MonsieurBiz\SyliusSearchPlugin\Index\Indexer;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use MonsieurBiz\SyliusSearchPlugin\Search\RequestFactory;
use MonsieurBiz\SyliusSearchPlugin\Search\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Search;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

class SearchCommand extends Command
{
    protected static $defaultName = 'monsieurbiz:search:search';
    private Indexer $indexer;
    private SerializerInterface $serializer;
    private RequestFactory $requestFactory;
    private Search $search;

    public function __construct(
        Indexer $indexer, SerializerInterface $serializer, RequestFactory $requestFactory, Search $search, $name = null)
    {
        parent::__construct($name);
        $this->indexer = $indexer;
        $this->serializer = $serializer;
        $this->requestFactory = $requestFactory;
        $this->search = $search;
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('query', InputArgument::REQUIRED, 'Search query');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $query = $input->getArgument('query');
        $request = $this->requestFactory->create(RequestInterface::SEARCH_TYPE, 'monsieurbiz_product');
        $request->setQueryParameters(['query_text' => $query]);

        $result = $this->search->query($request);
        $io->title('Search result for: ' . $query);
        $io->section('Nb results: ' . $result->count());
        $documents = [];
        foreach ($result->getIterator() as $resultItem) {
            /** @var ProductDTO $productDTO */
            $productDTO = $resultItem->getModel();
            $documents[] = [$resultItem->getScore(), $productDTO->getId()];
        }
        $io->table(['Score', 'Document ID'], $documents);

        return Command::SUCCESS;
    }
}
