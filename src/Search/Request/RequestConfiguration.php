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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
        $query = $this->request->get('query', '');
        if (!\is_string($query)) {
            throw new BadRequestHttpException('The search query must be a string.');
        }

        return trim(urldecode($query));
    }

    public function getAppliedFilters(string $type = null): array
    {
        $this->manageRangeField('price');
        $requestQuery = $this->buildRequestQuery();

        if (null === $type) {
            return $requestQuery;
        }
        $filters = $requestQuery[$type] ?? [];
        if (!\is_array($filters)) {
            throw new BadRequestHttpException('Search filters must be an array.');
        }

        return $filters;
    }

    public function getSorting(): array
    {
        $sorting = $this->request->get('sorting', []);
        if (!\is_array($sorting)) {
            throw new BadRequestHttpException('Sorting must be an array.');
        }
        foreach ($sorting as $direction) {
            if (!\in_array($direction, ['asc', 'desc'], true)) {
                throw new BadRequestHttpException('Sort directions must be asc or desc.');
            }
        }

        return $sorting;
    }

    public function getPage(): int
    {
        return $this->getPositiveInteger('page', 1);
    }

    public function manageRangeField(string $field): void
    {
        $range = $this->request->get($field, []);
        if (!\is_array($range)) {
            throw new BadRequestHttpException('Range bounds must be an array.');
        }
        if (empty($range)) {
            return;
        }

        $range = $this->sanitizeRange($range);

        /** @var array $range */
        $range = $this->sortRangeBounds($range);

        // Negative prices are not useful; zero remains a valid bound for free products.
        $range = $this->removeNegativeRangeBounds($range);

        $this->request->query->set($field, $range);
    }

    public function getLimit(): int
    {
        $limit = $this->getPositiveInteger('limit', self::FALLBACK_LIMIT);
        $availableLimits = $this->getAvailableLimits();

        if (0 < \count($availableLimits) && !\in_array($limit, $availableLimits, true)) {
            $limit = reset($availableLimits);
        }

        return $limit;
    }

    public function getAvailableLimits(): array
    {
        /** @var array $configLimits */
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

        /** @phpstan-ignore-next-line */
        return $this->parameters->get('taxon');
    }

    public function getParameters(): Parameters
    {
        return $this->parameters;
    }

    private function getPositiveInteger(string $name, int $default): int
    {
        $value = filter_var($this->request->get($name, $default), \FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (false === $value) {
            throw new BadRequestHttpException(\sprintf('%s must be a positive integer.', $name));
        }

        return $value;
    }

    private function buildRequestQuery(): array
    {
        $requestQuery = [];
        foreach ($this->request->query->all() as $key => $query) {
            $requestQuery[$key] = \is_array($query) ? $this->removeEmptyValues($query) : $query;
        }

        return $requestQuery;
    }

    private function removeEmptyValues(array $values): array
    {
        return array_filter($values, static fn ($value): bool => '' !== $value && null !== $value && [] !== $value);
    }

    private function sanitizeRange(array $range): array
    {
        // Empty inputs represent an open bound, not zero. Normalize before swapping.
        $range = $this->removeEmptyValues($range);
        foreach ($range as $bound) {
            if ($this->isInvalidRangeBound($bound)) {
                throw new BadRequestHttpException('Range bounds must be finite numbers.');
            }
        }

        return $range;
    }

    private function sortRangeBounds(array $range): array
    {
        if (!isset($range['min'], $range['max'])) {
            return $range;
        }

        if ((float) $range['min'] <= (float) $range['max']) {
            return $range;
        }

        $min = $range['min']; // Take the original value, not casted
        $range['min'] = $range['max'];
        $range['max'] = $min;

        return $range;
    }

    private function isInvalidRangeBound(mixed $bound): bool
    {
        return !is_numeric($bound) || !is_finite((float) $bound * 100);
    }

    private function removeNegativeRangeBounds(array $range): array
    {
        foreach (['min', 'max'] as $bound) {
            if (isset($range[$bound]) && 0 > (float) $range[$bound]) {
                unset($range[$bound]);
            }
        }

        return $range;
    }
}
