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

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Normalizer;

use Jane\Component\JsonSchemaRuntime\Reference;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\CheckArray;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PricingDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use CheckArray;
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function supportsDenormalization($data, $type, $format = null)
    {
        return 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\PricingDTO' === $type;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (isset($data['$ref'])) {
            return new Reference($data['$ref'], $context['document-origin']);
        }
        if (isset($data['$recursiveRef'])) {
            return new Reference($data['$recursiveRef'], $context['document-origin']);
        }
        $object = new \MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO();
        if (null === $data || false === \is_array($data)) {
            return $object;
        }
        if (\array_key_exists('channel_code', $data)) {
            $object->setChannelCode($data['channel_code']);
        }
        if (\array_key_exists('price', $data) && null !== $data['price']) {
            $value = $data['price'];
            if (null === $data['price']) {
                $value = $data['price'];
            } elseif (\is_int($data['price'])) {
                $value = $data['price'];
            }
            $object->setPrice($value);
        } elseif (\array_key_exists('price', $data) && null === $data['price']) {
            $object->setPrice(null);
        }
        if (\array_key_exists('original_price', $data) && null !== $data['original_price']) {
            $value_1 = $data['original_price'];
            if (null === $data['original_price']) {
                $value_1 = $data['original_price'];
            } elseif (\is_int($data['original_price'])) {
                $value_1 = $data['original_price'];
            }
            $object->setOriginalPrice($value_1);
        } elseif (\array_key_exists('original_price', $data) && null === $data['original_price']) {
            $object->setOriginalPrice(null);
        }
        if (\array_key_exists('price_reduced', $data)) {
            $value_2 = $data['price_reduced'];
            if (\is_bool($data['price_reduced'])) {
                $value_2 = $data['price_reduced'];
            }
            $object->setPriceReduced($value_2);
        }

        return $object;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = [];
        if (null !== $object->getChannelCode()) {
            $data['channel_code'] = $object->getChannelCode();
        }
        if (null !== $object->getPrice()) {
            $value = $object->getPrice();
            if (null === $object->getPrice()) {
                $value = $object->getPrice();
            } elseif (\is_int($object->getPrice())) {
                $value = $object->getPrice();
            }
            $data['price'] = $value;
        }
        if (null !== $object->getOriginalPrice()) {
            $value_1 = $object->getOriginalPrice();
            if (null === $object->getOriginalPrice()) {
                $value_1 = $object->getOriginalPrice();
            } elseif (\is_int($object->getOriginalPrice())) {
                $value_1 = $object->getOriginalPrice();
            }
            $data['original_price'] = $value_1;
        }
        if (null !== $object->getPriceReduced()) {
            $value_2 = $object->getPriceReduced();
            if (\is_bool($object->getPriceReduced())) {
                $value_2 = $object->getPriceReduced();
            }
            $data['price_reduced'] = $value_2;
        }

        return $data;
    }
}
