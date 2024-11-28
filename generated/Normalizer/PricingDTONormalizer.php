<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Normalizer;

use Jane\Component\JsonSchemaRuntime\Reference;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\CheckArray;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\ValidatorTrait;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\HttpKernel\Kernel;
if (!class_exists(Kernel::class) or (Kernel::MAJOR_VERSION >= 7 or Kernel::MAJOR_VERSION === 6 and Kernel::MINOR_VERSION === 4)) {
    class PricingDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
    {
        use DenormalizerAwareTrait;
        use NormalizerAwareTrait;
        use CheckArray;
        use ValidatorTrait;
        public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []) : bool
        {
            return $type === \MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO::class;
        }
        public function supportsNormalization(mixed $data, string $format = null, array $context = []) : bool
        {
            return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO;
        }
        public function denormalize(mixed $data, string $type, string $format = null, array $context = []) : mixed
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
        public function normalize(mixed $object, string $format = null, array $context = []) : array|string|int|float|bool|\ArrayObject|null
        {
            $data = [];
            if ($object->isInitialized('channelCode') && null !== $object->getChannelCode()) {
                $data['channel_code'] = $object->getChannelCode();
            }
            if ($object->isInitialized('price') && null !== $object->getPrice()) {
                $value = $object->getPrice();
                if (is_null($object->getPrice())) {
                    $value = $object->getPrice();
                } elseif (is_int($object->getPrice())) {
                    $value = $object->getPrice();
                }
                $data['price'] = $value;
            }
            if ($object->isInitialized('originalPrice') && null !== $object->getOriginalPrice()) {
                $value_1 = $object->getOriginalPrice();
                if (is_null($object->getOriginalPrice())) {
                    $value_1 = $object->getOriginalPrice();
                } elseif (is_int($object->getOriginalPrice())) {
                    $value_1 = $object->getOriginalPrice();
                }
                $data['original_price'] = $value_1;
            }
            if ($object->isInitialized('priceReduced') && null !== $object->getPriceReduced()) {
                $value_2 = $object->getPriceReduced();
                if (is_bool($object->getPriceReduced())) {
                    $value_2 = $object->getPriceReduced();
                }
                $data['price_reduced'] = $value_2;
            }
            return $data;
        }
        public function getSupportedTypes(?string $format = null) : array
        {
            return [\MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO::class => false];
        }
    }
} else {
    class PricingDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
    {
        use DenormalizerAwareTrait;
        use NormalizerAwareTrait;
        use CheckArray;
        use ValidatorTrait;
        public function supportsDenormalization($data, $type, string $format = null, array $context = []) : bool
        {
            return $type === \MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO::class;
        }
        public function supportsNormalization($data, $format = null, array $context = []) : bool
        {
            return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO;
        }
        /**
         * @return mixed
         */
        public function denormalize($data, $type, $format = null, array $context = [])
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
        /**
         * @return array|string|int|float|bool|\ArrayObject|null
         */
        public function normalize($object, $format = null, array $context = [])
        {
            $data = [];
            if ($object->isInitialized('channelCode') && null !== $object->getChannelCode()) {
                $data['channel_code'] = $object->getChannelCode();
            }
            if ($object->isInitialized('price') && null !== $object->getPrice()) {
                $value = $object->getPrice();
                if (is_null($object->getPrice())) {
                    $value = $object->getPrice();
                } elseif (is_int($object->getPrice())) {
                    $value = $object->getPrice();
                }
                $data['price'] = $value;
            }
            if ($object->isInitialized('originalPrice') && null !== $object->getOriginalPrice()) {
                $value_1 = $object->getOriginalPrice();
                if (is_null($object->getOriginalPrice())) {
                    $value_1 = $object->getOriginalPrice();
                } elseif (is_int($object->getOriginalPrice())) {
                    $value_1 = $object->getOriginalPrice();
                }
                $data['original_price'] = $value_1;
            }
            if ($object->isInitialized('priceReduced') && null !== $object->getPriceReduced()) {
                $value_2 = $object->getPriceReduced();
                if (is_bool($object->getPriceReduced())) {
                    $value_2 = $object->getPriceReduced();
                }
                $data['price_reduced'] = $value_2;
            }
            return $data;
        }
        public function getSupportedTypes(?string $format = null) : array
        {
            return [\MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO::class => false];
        }
    }
}