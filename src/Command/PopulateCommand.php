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

use MonsieurBiz\SyliusSearchPlugin\Exception\ReadOnlyIndexException;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\Index\Indexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'monsieurbiz:search:populate';

    /**
     * @var Indexer
     */
    protected $documentIndexer;

    /**
     * PopulateCommand constructor.
     */
    public function __construct(Indexer $documentIndexer)
    {
        $this->documentIndexer = $documentIndexer;
        parent::__construct(static::$defaultName);
    }

    /**
     * Populate ES.
     *
     * @return int 0 if everything went fine, or an exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating index');

        try {
            $this->documentIndexer->indexAll();
        } catch (ReadOnlyIndexException $exception) {
            $output->writeln('Cannot purge old index. Please to do it manually if needed.');
            // it's better to use return Command::FAILURE; in Symfony 5
            return 1;
        }
        $output->writeln('Generated index');
        // it's better to use return Command::SUCCESS; in Symfony 5
        return 0;
    }
}
