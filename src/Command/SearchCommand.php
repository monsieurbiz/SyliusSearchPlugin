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

namespace MonsieurBiz\SyliusSearchPlugin\Command;

use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Search;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SearchCommand extends Command
{
    protected static $defaultName = 'monsieurbiz:search:search';

    private Search $search;

    private RequestStack $requestStack;

    private ChannelContextInterface $channelContext;

    private SettingsInterface $searchSettings;

    private ServiceRegistryInterface $documentableRegistry;

    public function __construct(
        Search $search,
        RequestStack $requestStack,
        ChannelContextInterface $channelContext,
        SettingsInterface $searchSettings,
        ServiceRegistryInterface $documentableRegistry,
        $name = null
    ) {
        parent::__construct($name);
        $this->search = $search;
        $this->requestStack = $requestStack;
        $this->channelContext = $channelContext;
        $this->searchSettings = $searchSettings;
        $this->documentableRegistry = $documentableRegistry;
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('query', InputArgument::REQUIRED, 'Search query');
        $this->addOption('channel', 'c', InputOption::VALUE_OPTIONAL, 'Channel code', 'FASHION_WEB');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $query = $input->getArgument('query');
        $request = new Request(['query' => $query, '_channel_code' => $input->getOption('channel')]);
        $this->requestStack->push($request);
        /** @var DocumentableInterface $documentable */
        $documentable = $this->documentableRegistry->get('search.documentable.monsieurbiz_product');
        $requestConfiguration = new RequestConfiguration(
            $request,
            RequestInterface::SEARCH_TYPE,
            $documentable,
            $this->searchSettings,
            $this->channelContext
        );

        $result = $this->search->search($requestConfiguration);

        $io->title('Search result for: ' . $query);
        $io->section('Nb results: ' . $result->count());
        $documents = [];
        foreach ($result->getIterator() as $resultItem) {
            /** @var ProductDTO $productDTO */
            $productDTO = $resultItem->getModel();
            $documents[] = [$resultItem->getScore(), $productDTO->getData('id')];
        }
        $io->table(['Score', 'Document ID'], $documents);

        return Command::SUCCESS;
    }
}
