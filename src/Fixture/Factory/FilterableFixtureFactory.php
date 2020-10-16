<?php
declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Fixture\Factory;

use MonsieurBiz\SyliusSearchPlugin\Entity\Product\FilterableInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Bundle\CoreBundle\Fixture\OptionsResolver\LazyOption;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FilterableFixtureFactory extends AbstractExampleFactory implements ExampleFactoryInterface
{

    /**
     * @var RepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * @var RepositoryInterface
     */
    protected $productOptionRepository;

    /** @var OptionsResolver */
    private $optionsResolver;

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
            // Attribute
            ->setDefault('attribute', null)
            ->setAllowedTypes('attribute', ['null', 'string', ProductAttributeInterface::class])
            ->setNormalizer('attribute', LazyOption::findOneBy($this->productAttributeRepository, 'code'))
            // Option
            ->setDefault('option', null)
            ->setAllowedTypes('option', ['null', 'string', ProductOptionInterface::class])
            ->setNormalizer('option', LazyOption::findOneBy($this->productOptionRepository, 'code'))
            // Filterable
            ->setDefault('filterable', true);
    }

    /**
     * @param array $options
     * @return object
     * @throws \Exception
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
            throw new \Exception(sprintf(
                'Your class "%s" is not an instance of %s',
                get_class($object),
                FilterableInterface::class
            ));
        }

        /** @var FilterableInterface $object */
        $object->setFilterable(((bool) $options['filterable']) ?? false);

        return $object;
    }

}
