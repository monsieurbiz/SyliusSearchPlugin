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

namespace MonsieurBiz\SyliusSearchPlugin\Checker;

use MonsieurBiz\SyliusSearchPlugin\Search\ClientFactory;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

class ElasticsearchChecker
{
    private ClientFactory $clientFactory;

    private ServiceRegistryInterface $documentableRegistry;

    private LocaleContextInterface $localeContext;

    private ?bool $isAvailable = null;

    public function __construct(
        ClientFactory $clientFactory,
        ServiceRegistryInterface $documentableRegistry,
        LocaleContextInterface $localeContext
    ) {
        $this->clientFactory = $clientFactory;
        $this->documentableRegistry = $documentableRegistry;
        $this->localeContext = $localeContext;
    }

    public function check(): bool
    {
        if (null === $this->isAvailable) {
            $documentables = $this->documentableRegistry->all();
            $documentable = reset($documentables);
            $client = $this->clientFactory->getClient($documentable, $this->localeContext->getLocaleCode());

            try {
                $client->getStatus()->getResponse();
            } catch (\Exception $e) {
                $this->isAvailable = false;

                return $this->isAvailable;
            }

            $this->isAvailable = true;
        }

        return $this->isAvailable;
    }
}
