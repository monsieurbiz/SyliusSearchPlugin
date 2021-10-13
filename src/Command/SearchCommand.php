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

use Elastica\Query;
use Elastica\Query\MultiMatch;
use JoliCode\Elastically\Factory;
use MonsieurBiz\SyliusSearchPlugin\Index\Indexer;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use Pagerfanta\Elastica\ElasticaAdapter;
use Pagerfanta\Pagerfanta;
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

    public function __construct(Indexer $indexer, SerializerInterface $serializer, $name = null)
    {
        parent::__construct($name);
        $this->indexer = $indexer;
        $this->serializer = $serializer;
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('query', InputArgument::REQUIRED, 'Search query');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $factory = new Factory([
            Factory::CONFIG_INDEX_CLASS_MAPPING => [
                'monsieurbiz_product_fr_fr' => ProductDTO::class,
            ],
            Factory::CONFIG_SERIALIZER => $this->serializer,
        ]);

        $client = $factory->buildClient();

        $query = $input->getArgument('query');

        $searchQuery = new MultiMatch();
        $searchQuery->setFields([
            'name^5',
            'description',
        ]);
        $searchQuery->setQuery($query);
        $searchQuery->setType(MultiMatch::TYPE_MOST_FIELDS);

        $foundPosts =  new Pagerfanta(new ElasticaAdapter($client->getIndex('monsieurbiz_product_fr_fr'), Query::create($searchQuery)));
        $io->title('Search result for: ' . $query);
        $io->section('Nb results: ' . $foundPosts->getNbResults());
        $documents = [];
        foreach ($foundPosts as $result) {
            /** @var ProductDTO $productDTO */
            $productDTO = $result->getModel();
            $documents[] = [$result->getScore(), $productDTO->getId()];
        }
        $io->table(['Score', 'Document ID'], $documents);

        return Command::SUCCESS;
    }
}
