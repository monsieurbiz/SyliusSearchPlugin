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

namespace MonsieurBiz\SyliusSearchPlugin\Fixture\Factory;

use MonsieurBiz\SyliusSearchPlugin\Entity\Product\FilterableInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\OptionsResolver\LazyOption;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterableFixtureFactory extends AbstractExampleFactory implements FilterableFixtureFactoryInterface
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
     * FilterableFixtureFactory constructor.
     *
     * @param RepositoryInterface $productAttributeRepository
     * @param RepositoryInterface $productOptionRepository
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
     * {@inheritdoc}
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
            ->setDefault('filterable', true)
        ;
    }

    /**
     * @param array $options
     *
     * @throws \Exception
     *
     * @return object
     */
    public function create(array $options = [])
    {
        $options = $this->optionsResolver->resolve($options);

        if (isset($options['attribute']) && !empty($options['attribute'])) {
            $object = $options['attribute'];
        } elseif (isset($options['option']) && !empty($options['option'])) {
            $object = $options['option'];
        } else {
            throw new \Exception('You need to specify an attribute or an option to be filterable.');
        }

        if (!$object instanceof FilterableInterface) {
            throw new \Exception(sprintf('Your class "%s" is not an instance of %s', \get_class($object), FilterableInterface::class));
        }

        /** @var FilterableInterface $object */
        $object->setFilterable(((bool) $options['filterable']) ?? false);

        return $object;
    }
}
