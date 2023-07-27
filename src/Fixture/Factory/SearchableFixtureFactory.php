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

namespace MonsieurBiz\SyliusSearchPlugin\Fixture\Factory;

use Exception;
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\OptionsResolver\LazyOption;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

class SearchableFixtureFactory extends AbstractExampleFactory implements SearchableFixtureFactoryInterface
{
    /**
     * @var RepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * @var RepositoryInterface
     */
    protected $productOptionRepository;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * SearchableFixtureFactory constructor.
     */
    public function __construct(
        RepositoryInterface $productAttributeRepository,
        RepositoryInterface $productOptionRepository
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productOptionRepository = $productOptionRepository;
        $this->optionsResolver = new OptionsResolver();
        $this->configureOptions($this->optionsResolver);
    }

    /**
     * @inheritdoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('attribute', null)
                ->setAllowedTypes('attribute', ['null', 'string', ProductAttributeInterface::class])
                ->setNormalizer('attribute', LazyOption::findOneBy($this->productAttributeRepository, 'code'))
            ->setDefault('option', null)
                ->setAllowedTypes('option', ['null', 'string', ProductOptionInterface::class])
                ->setNormalizer('option', LazyOption::findOneBy($this->productOptionRepository, 'code'))
            ->setDefault('filterable', false)
            ->setDefault('searchable', false)
        ;
    }

    /**
     * @throws Exception
     */
    public function create(array $options = []): SearchableInterface
    {
        $options = $this->optionsResolver->resolve($options);
        $object = $this->getSearchableObject($options);
        $object->setFilterable(((bool) $options['filterable']) ?? false);
        $object->setSearchable(((bool) $options['searchable']) ?? false);

        return $object;
    }

    private function getSearchableObject(array $options): SearchableInterface
    {
        $object = null;
        if (!empty($options['attribute'])) {
            $object = $options['attribute'];
        } elseif (!empty($options['option'])) {
            $object = $options['option'];
        }

        /** @var SearchableInterface $object */
        Assert::isInstanceOf($object, SearchableInterface::class);

        return $object;
    }
}
