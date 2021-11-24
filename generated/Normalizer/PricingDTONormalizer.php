<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Normalizer;

use Jane\Component\JsonSchemaRuntime\Reference;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\CheckArray;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
class PricingDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\PricingDTO';
    }
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO;
    }
    public function denormalize($data, $class, $format = null, array $context = array())
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
        if (\array_key_exists('price', $data) && $data['price'] !== null) {
            $value = $data['price'];
            if (is_null($data['price'])) {
                $value = $data['price'];
            } elseif (is_int($data['price'])) {
                $value = $data['price'];
            }
            $object->setPrice($value);
        }
        elseif (\array_key_exists('price', $data) && $data['price'] === null) {
            $object->setPrice(null);
        }
        if (\array_key_exists('original_price', $data) && $data['original_price'] !== null) {
            $value_1 = $data['original_price'];
            if (is_null($data['original_price'])) {
                $value_1 = $data['original_price'];
            } elseif (is_int($data['original_price'])) {
                $value_1 = $data['original_price'];
            }
            $object->setOriginalPrice($value_1);
        }
        elseif (\array_key_exists('original_price', $data) && $data['original_price'] === null) {
            $object->setOriginalPrice(null);
        }
        if (\array_key_exists('price_reduced', $data)) {
            $value_2 = $data['price_reduced'];
            if (is_bool($data['price_reduced'])) {
                $value_2 = $data['price_reduced'];
            }
            $object->setPriceReduced($value_2);
        }
        return $object;
    }
    public function normalize($object, $format = null, array $context = array())
    {
        $data = array();
        if (null !== $object->getChannelCode()) {
            $data['channel_code'] = $object->getChannelCode();
        }
        if (null !== $object->getPrice()) {
            $value = $object->getPrice();
            if (is_null($object->getPrice())) {
                $value = $object->getPrice();
            } elseif (is_int($object->getPrice())) {
                $value = $object->getPrice();
            }
            $data['price'] = $value;
        }
        if (null !== $object->getOriginalPrice()) {
            $value_1 = $object->getOriginalPrice();
            if (is_null($object->getOriginalPrice())) {
                $value_1 = $object->getOriginalPrice();
            } elseif (is_int($object->getOriginalPrice())) {
                $value_1 = $object->getOriginalPrice();
            }
            $data['original_price'] = $value_1;
        }
        if (null !== $object->getPriceReduced()) {
            $value_2 = $object->getPriceReduced();
            if (is_bool($object->getPriceReduced())) {
                $value_2 = $object->getPriceReduced();
            }
            $data['price_reduced'] = $value_2;
        }
        return $data;
    }
}