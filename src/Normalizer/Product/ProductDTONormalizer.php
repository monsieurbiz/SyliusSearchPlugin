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

namespace MonsieurBiz\SyliusSearchPlugin\Normalizer\Product;

use MonsieurBiz\SyliusSearchPlugin\Generated\Model\Channel;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\Image;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductAttribute;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxon;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\Taxon;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class ProductDTONormalizer extends ObjectNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        /** @var ProductDTO $object */
        $object = parent::denormalize($data, $type, $format, $context);

        if (\array_key_exists('main_taxon', $data)) {
            $object->setMainTaxon($this->denormalizer->denormalize($data['main_taxon'], Taxon::class, 'json', $context));
            unset($data['main_taxon']);
        }

        if (\array_key_exists('product_taxons', $data)) {
            $values = [];
            foreach ($data['product_taxons'] as $value) {
                $values[] = $this->denormalizer->denormalize($value, ProductTaxon::class, 'json', $context);
            }
            $object->setProductTaxons($values);
            unset($data['product_taxons']);
        }

        if (\array_key_exists('images', $data) && null !== $data['images']) {
            $values = [];
            foreach ($data['images'] as $value) {
                $values[] = $this->denormalizer->denormalize($value, Image::class, 'json', $context);
            }
            $object->setImages($values);
            unset($data['product_taxons']);
        }

        if (\array_key_exists('channels', $data)) {
            $values = [];
            foreach ($data['channels'] as $value) {
                $values[] = $this->denormalizer->denormalize($value, Channel::class, 'json', $context);
            }
            $object->setChannels($values);
            unset($data['channels']);
        }

        if (\array_key_exists('attributes', $data)) {
            $values = [];
            foreach ($data['attributes'] as $key => $value) {
                $values[$key] = $this->denormalizer->denormalize($value, ProductAttribute::class, 'json', $context);
            }
            $object->setAttributes($values);
            unset($data['channels']);
        }

        if (\array_key_exists('prices', $data)) {
            $values = [];
            foreach ($data['prices'] as $key => $value) {
                $values[$key] = $this->denormalizer->denormalize($value, PricingDTO::class, 'json', $context);
            }
            $object->setPrices($values);
            unset($data['channels']);
        }

        return $object;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return ProductDTO::class === $type;
    }

    public function supportsNormalization($data, string $format = null)
    {
        return false;
    }
}
