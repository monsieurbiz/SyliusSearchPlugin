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
    class TaxonDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
    {
        use DenormalizerAwareTrait;
        use NormalizerAwareTrait;
        use CheckArray;
        use ValidatorTrait;
        public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []) : bool
        {
            return $type === \MonsieurBiz\SyliusSearchPlugin\Generated\Model\TaxonDTO::class;
        }
        public function supportsNormalization(mixed $data, string $format = null, array $context = []) : bool
        {
            return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\TaxonDTO;
        }
        public function denormalize(mixed $data, string $type, string $format = null, array $context = []) : mixed
        {
            if (isset($data['$ref'])) {
                return new Reference($data['$ref'], $context['document-origin']);
            }
            if (isset($data['$recursiveRef'])) {
                return new Reference($data['$recursiveRef'], $context['document-origin']);
            }
            $object = new \MonsieurBiz\SyliusSearchPlugin\Generated\Model\TaxonDTO();
            if (null === $data || false === \is_array($data)) {
                return $object;
            }
            if (\array_key_exists('name', $data)) {
                $object->setName($data['name']);
            }
            if (\array_key_exists('code', $data)) {
                $object->setCode($data['code']);
            }
            if (\array_key_exists('position', $data)) {
                $object->setPosition($data['position']);
            }
            if (\array_key_exists('level', $data)) {
                $object->setLevel($data['level']);
            }
            return $object;
        }
        public function normalize(mixed $object, string $format = null, array $context = []) : array|string|int|float|bool|\ArrayObject|null
        {
            $data = [];
            if ($object->isInitialized('name') && null !== $object->getName()) {
                $data['name'] = $object->getName();
            }
            if ($object->isInitialized('code') && null !== $object->getCode()) {
                $data['code'] = $object->getCode();
            }
            if ($object->isInitialized('position') && null !== $object->getPosition()) {
                $data['position'] = $object->getPosition();
            }
            if ($object->isInitialized('level') && null !== $object->getLevel()) {
                $data['level'] = $object->getLevel();
            }
            return $data;
        }
        public function getSupportedTypes(?string $format = null) : array
        {
            return [\MonsieurBiz\SyliusSearchPlugin\Generated\Model\TaxonDTO::class => false];
        }
    }
} else {
    class TaxonDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
    {
        use DenormalizerAwareTrait;
        use NormalizerAwareTrait;
        use CheckArray;
        use ValidatorTrait;
        public function supportsDenormalization($data, $type, string $format = null, array $context = []) : bool
        {
            return $type === \MonsieurBiz\SyliusSearchPlugin\Generated\Model\TaxonDTO::class;
        }
        public function supportsNormalization($data, $format = null, array $context = []) : bool
        {
            return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\TaxonDTO;
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
            $object = new \MonsieurBiz\SyliusSearchPlugin\Generated\Model\TaxonDTO();
            if (null === $data || false === \is_array($data)) {
                return $object;
            }
            if (\array_key_exists('name', $data)) {
                $object->setName($data['name']);
            }
            if (\array_key_exists('code', $data)) {
                $object->setCode($data['code']);
            }
            if (\array_key_exists('position', $data)) {
                $object->setPosition($data['position']);
            }
            if (\array_key_exists('level', $data)) {
                $object->setLevel($data['level']);
            }
            return $object;
        }
        /**
         * @return array|string|int|float|bool|\ArrayObject|null
         */
        public function normalize($object, $format = null, array $context = [])
        {
            $data = [];
            if ($object->isInitialized('name') && null !== $object->getName()) {
                $data['name'] = $object->getName();
            }
            if ($object->isInitialized('code') && null !== $object->getCode()) {
                $data['code'] = $object->getCode();
            }
            if ($object->isInitialized('position') && null !== $object->getPosition()) {
                $data['position'] = $object->getPosition();
            }
            if ($object->isInitialized('level') && null !== $object->getLevel()) {
                $data['level'] = $object->getLevel();
            }
            return $data;
        }
        public function getSupportedTypes(?string $format = null) : array
        {
            return [\MonsieurBiz\SyliusSearchPlugin\Generated\Model\TaxonDTO::class => false];
        }
    }
}