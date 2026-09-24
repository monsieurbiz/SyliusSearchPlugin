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

namespace MonsieurBiz\SyliusSearchPlugin\Normalizer\Product;

use MonsieurBiz\SyliusSearchPlugin\AutoMapper\Configuration;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class ProductDTONormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private Configuration $automapperConfiguration;

    public function __construct(
        Configuration $automapperConfiguration,
        private ObjectNormalizer $objectNormalizer
    ) {
        $this->automapperConfiguration = $automapperConfiguration;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): ProductDTO
    {
        /** @var ProductDTO $object */
        $object = $this->objectNormalizer->denormalize($data, $type, $format, $context);

        if (\is_array($data) && \array_key_exists('main_taxon', $data) && null !== $data['main_taxon']) {
            $taxonDTOClass = $this->automapperConfiguration->getTargetClass('taxon');
            $object->setData('main_taxon', $this->denormalizer->denormalize($data['main_taxon'], $taxonDTOClass, 'json', $context));
            unset($data['main_taxon']);
        }

        if (\is_array($data) && \array_key_exists('product_taxons', $data) && null !== $data['product_taxons']) {
            $values = [];
            $productTaxonDTOClass = $this->automapperConfiguration->getTargetClass('product_taxon');
            foreach ($data['product_taxons'] as $value) {
                $values[] = $this->denormalizer->denormalize($value, $productTaxonDTOClass, 'json', $context);
            }
            $object->setData('product_taxons', $values);
            unset($data['product_taxons']);
        }

        if (\is_array($data) && \array_key_exists('images', $data) && null !== $data['images']) {
            $values = [];
            $imageDTOClass = $this->automapperConfiguration->getTargetClass('image');
            foreach ($data['images'] as $value) {
                $values[] = $this->denormalizer->denormalize($value, $imageDTOClass, 'json', $context);
            }
            $object->setData('images', $values);
            unset($data['product_taxons']);
        }

        if (\is_array($data) && \array_key_exists('channels', $data) && null !== $data['channels']) {
            $values = [];
            $channelDTOClass = $this->automapperConfiguration->getTargetClass('channel');
            foreach ($data['channels'] as $value) {
                $values[] = $this->denormalizer->denormalize($value, $channelDTOClass, 'json', $context);
            }
            $object->setData('channels', $values);
            unset($data['channels']);
        }

        if (\is_array($data) && \array_key_exists('attributes', $data) && null !== $data['attributes']) {
            $values = [];
            $productAttributeDTOClass = $this->automapperConfiguration->getTargetClass('product_attribute');
            foreach ($data['attributes'] as $key => $value) {
                $values[$key] = $this->denormalizer->denormalize($value, $productAttributeDTOClass, 'json', $context);
            }
            $object->setData('attributes', $values);
            unset($data['channels']);
        }

        if (\is_array($data) && \array_key_exists('prices', $data) && null !== $data['prices']) {
            $values = [];
            $pricingDTOClass = $this->automapperConfiguration->getTargetClass('pricing');
            foreach ($data['prices'] as $key => $value) {
                $values[$key] = $this->denormalizer->denormalize($value, $pricingDTOClass, 'json', $context);
            }
            $object->setData('prices', $values);
            unset($data['channels']);
        }

        return $object;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $this->automapperConfiguration->getTargetClass('product') === $type;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getSupportedTypes(?string $format): array
    {
        return [$this->automapperConfiguration->getTargetClass('product') => true];
    }
}
