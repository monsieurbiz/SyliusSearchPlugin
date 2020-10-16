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

namespace MonsieurBiz\SyliusSearchPlugin\Context;

use MonsieurBiz\SyliusSearchPlugin\Exception\TaxonNotFoundException;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class RequestTaxonContext implements TaxonContextInterface
{
    /** @var RequestStack */
    private $requestStack;

    /** @var TaxonRepositoryInterface */
    private $taxonRepository;

    /** @var LocaleContextInterface */
    private $localeContext;

    public function __construct(
        RequestStack $requestStack,
        TaxonRepositoryInterface $taxonRepository,
        LocaleContextInterface $localeContext
    ) {
        $this->requestStack = $requestStack;
        $this->taxonRepository = $taxonRepository;
        $this->localeContext = $localeContext;
    }

    public function getTaxon(): TaxonInterface
    {
        $slug = htmlspecialchars($this->requestStack->getCurrentRequest()->get('slug'));
        $localeCode = $this->localeContext->getLocaleCode();

        /** @var TaxonInterface $taxon */
        $taxon = $this->taxonRepository->findOneBySlug($slug, $localeCode);

        if (null === $slug || null === $taxon) {
            throw new TaxonNotFoundException();
        }

        return $taxon;
    }
}
