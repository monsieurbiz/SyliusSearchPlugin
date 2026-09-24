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

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaValidator;
use Sylius\Component\Shipping\Model\ShipmentUnit;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:search:validate-mappings', description: 'Validate mappings, reporting the known unused Sylius shipping superclass mismatch.')]
final class ValidateSearchMappingsCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $validator = new SchemaValidator($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $errors = $validator->validateMapping();
        $knownError = 'The association Sylius\\Component\\Shipping\\Model\\ShipmentUnit#shipment refers to the inverse side App\\Entity\\Shipping\\Shipment#units which targets a different entity (App\\Entity\\Order\\OrderItemUnit).';
        $shippingSuperclass = $this->entityManager->getClassMetadata(ShipmentUnit::class);
        $descendants = array_filter($metadata, static fn ($class): bool => !$class->isMappedSuperclass && is_a($class->name, ShipmentUnit::class, true));
        if ($shippingSuperclass->isMappedSuperclass && [] === $descendants && [$knownError] === ($errors[ShipmentUnit::class] ?? [])) {
            $io->warning('Upstream Sylius standalone Shipping ShipmentUnit is not used by any concrete entity. Its known inverse-target diagnostic remains present in doctrine:schema:validate.');
            unset($errors[ShipmentUnit::class]);
        }
        if ([] !== $errors) {
            foreach ($errors as $messages) {
                $io->error($messages);
            }

            return Command::FAILURE;
        }
        $io->success('All remaining mappings, including every concrete entity, are valid.');

        return Command::SUCCESS;
    }
}
