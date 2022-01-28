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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request;

use MonsieurBiz\SyliusSearchPlugin\Exception\ObjectNotInstanceOfClassException;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Bundle\ResourceBundle\Controller\Parameters;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\HttpFoundation\Request;

final class RequestConfiguration
{
    public const FALLBACK_LIMIT = 9;

    private Request $request;

    private string $type;

    private DocumentableInterface $documentable;

    private SettingsInterface $searchSettings;

    private ChannelContextInterface $channelContext;

    private Parameters $parameters;

    public function __construct(
        Request $request,
        string $type,
        DocumentableInterface $documentable,
        SettingsInterface $searchSettings,
        ChannelContextInterface $channelContext,
        ?Parameters $parameters = null
    ) {
        $this->request = $request;
        $this->type = $type;
        $this->documentable = $documentable;
        $this->searchSettings = $searchSettings;
        $this->channelContext = $channelContext;
        $this->parameters = $parameters ?? new Parameters();
    }

    public function getQueryText(): string
    {
        return $this->request->get('query', '');
    }

    public function getAppliedFilters(string $type = null): array
    {
        $requestQuery = $this->request->query->all();
        $requestQuery = array_map(function ($query) {
            return \is_array($query) ? array_filter($query) : $query;
        }, $requestQuery);

        return null !== $type ? ($requestQuery[$type] ?? []) : $requestQuery;
    }

    public function getSorting(): array
    {
        return $this->request->get('sorting', []);
    }

    public function getPage(): int
    {
        return (int) $this->request->get('page', 1);
    }

    public function getLimit(): int
    {
        $limit = (int) $this->request->get('limit', self::FALLBACK_LIMIT);
        $availableLimits = $this->getAvailableLimits();

        if (!\in_array($limit, $availableLimits, true)) {
            $limit = reset($availableLimits);
        }

        return $limit;
    }

    public function getAvailableLimits(): array
    {
        $configLimits = $this->searchSettings->getCurrentValue(
            $this->channelContext->getChannel(),
            null,
            'limits__' . $this->getDocumentType()
        );

        return $configLimits[$this->getType()] ?? $this->documentable->getLimits($this->getType());
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDocumentType(): string
    {
        return $this->documentable->getIndexCode();
    }

    public function getTaxon(): TaxonInterface
    {
        if (!$this->parameters->has('taxon')) {
            throw new ParameterNotFoundException('taxon');
        }
        $taxon = $this->parameters->get('taxon');
        if (!$taxon instanceof TaxonInterface) {
            throw ObjectNotInstanceOfClassException::fromClassName(TaxonInterface::class);
        }

        return $this->parameters->get('taxon');
    }
}
