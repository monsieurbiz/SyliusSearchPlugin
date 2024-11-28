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
    class ProductTaxonDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
    {
        use DenormalizerAwareTrait;
        use NormalizerAwareTrait;
        use CheckArray;
        use ValidatorTrait;
        public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []) : bool
        {
            return $type === \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO::class;
        }
        public function supportsNormalization(mixed $data, string $format = null, array $context = []) : bool
        {
            return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO;
        }
        public function denormalize(mixed $data, string $type, string $format = null, array $context = []) : mixed
        {
            if (isset($data['$ref'])) {
                return new Reference($data['$ref'], $context['document-origin']);
            }
            if (isset($data['$recursiveRef'])) {
                return new Reference($data['$recursiveRef'], $context['document-origin']);
            }
            $object = new \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO();
            if (null === $data || false === \is_array($data)) {
                return $object;
            }
            if (\array_key_exists('taxon', $data)) {
                $object->setTaxon($this->denormalizer->denormalize($data['taxon'], \MonsieurBiz\SyliusSearchPlugin\Generated\Model\TaxonDTO::class, 'json', $context));
            }
            if (\array_key_exists('position', $data) && $data['position'] !== null) {
                $value = $data['position'];
                if (is_null($data['position'])) {
                    $value = $data['position'];
                } elseif (is_int($data['position'])) {
                    $value = $data['position'];
                }
                $object->setPosition($value);
            }
            elseif (\array_key_exists('position', $data) && $data['position'] === null) {
                $object->setPosition(null);
            }
            return $object;
        }
        public function normalize(mixed $object, string $format = null, array $context = []) : array|string|int|float|bool|\ArrayObject|null
        {
            $data = [];
            if ($object->isInitialized('taxon') && null !== $object->getTaxon()) {
                $data['taxon'] = $this->normalizer->normalize($object->getTaxon(), 'json', $context);
            }
            if ($object->isInitialized('position') && null !== $object->getPosition()) {
                $value = $object->getPosition();
                if (is_null($object->getPosition())) {
                    $value = $object->getPosition();
                } elseif (is_int($object->getPosition())) {
                    $value = $object->getPosition();
                }
                $data['position'] = $value;
            }
            return $data;
        }
        public function getSupportedTypes(?string $format = null) : array
        {
            return [\MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO::class => false];
        }
    }
} else {
    class ProductTaxonDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
    {
        use DenormalizerAwareTrait;
        use NormalizerAwareTrait;
        use CheckArray;
        use ValidatorTrait;
        public function supportsDenormalization($data, $type, string $format = null, array $context = []) : bool
        {
            return $type === \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO::class;
        }
        public function supportsNormalization($data, $format = null, array $context = []) : bool
        {
            return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO;
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
            $object = new \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO();
            if (null === $data || false === \is_array($data)) {
                return $object;
            }
            if (\array_key_exists('taxon', $data)) {
                $object->setTaxon($this->denormalizer->denormalize($data['taxon'], \MonsieurBiz\SyliusSearchPlugin\Generated\Model\TaxonDTO::class, 'json', $context));
            }
            if (\array_key_exists('position', $data) && $data['position'] !== null) {
                $value = $data['position'];
                if (is_null($data['position'])) {
                    $value = $data['position'];
                } elseif (is_int($data['position'])) {
                    $value = $data['position'];
                }
                $object->setPosition($value);
            }
            elseif (\array_key_exists('position', $data) && $data['position'] === null) {
                $object->setPosition(null);
            }
            return $object;
        }
        /**
         * @return array|string|int|float|bool|\ArrayObject|null
         */
        public function normalize($object, $format = null, array $context = [])
        {
            $data = [];
            if ($object->isInitialized('taxon') && null !== $object->getTaxon()) {
                $data['taxon'] = $this->normalizer->normalize($object->getTaxon(), 'json', $context);
            }
            if ($object->isInitialized('position') && null !== $object->getPosition()) {
                $value = $object->getPosition();
                if (is_null($object->getPosition())) {
                    $value = $object->getPosition();
                } elseif (is_int($object->getPosition())) {
                    $value = $object->getPosition();
                }
                $data['position'] = $value;
            }
            return $data;
        }
        public function getSupportedTypes(?string $format = null) : array
        {
            return [\MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO::class => false];
        }
    }
}