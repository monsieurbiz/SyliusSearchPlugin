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
    class ImageDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
    {
        use DenormalizerAwareTrait;
        use NormalizerAwareTrait;
        use CheckArray;
        use ValidatorTrait;
        public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []) : bool
        {
            return $type === \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO::class;
        }
        public function supportsNormalization(mixed $data, string $format = null, array $context = []) : bool
        {
            return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO;
        }
        public function denormalize(mixed $data, string $type, string $format = null, array $context = []) : mixed
        {
            if (isset($data['$ref'])) {
                return new Reference($data['$ref'], $context['document-origin']);
            }
            if (isset($data['$recursiveRef'])) {
                return new Reference($data['$recursiveRef'], $context['document-origin']);
            }
            $object = new \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO();
            if (null === $data || false === \is_array($data)) {
                return $object;
            }
            if (\array_key_exists('path', $data) && $data['path'] !== null) {
                $value = $data['path'];
                if (is_null($data['path'])) {
                    $value = $data['path'];
                } elseif (is_string($data['path'])) {
                    $value = $data['path'];
                }
                $object->setPath($value);
            }
            elseif (\array_key_exists('path', $data) && $data['path'] === null) {
                $object->setPath(null);
            }
            if (\array_key_exists('type', $data) && $data['type'] !== null) {
                $value_1 = $data['type'];
                if (is_null($data['type'])) {
                    $value_1 = $data['type'];
                } elseif (is_string($data['type'])) {
                    $value_1 = $data['type'];
                }
                $object->setType($value_1);
            }
            elseif (\array_key_exists('type', $data) && $data['type'] === null) {
                $object->setType(null);
            }
            return $object;
        }
        public function normalize(mixed $object, string $format = null, array $context = []) : array|string|int|float|bool|\ArrayObject|null
        {
            $data = [];
            if ($object->isInitialized('path') && null !== $object->getPath()) {
                $value = $object->getPath();
                if (is_null($object->getPath())) {
                    $value = $object->getPath();
                } elseif (is_string($object->getPath())) {
                    $value = $object->getPath();
                }
                $data['path'] = $value;
            }
            if ($object->isInitialized('type') && null !== $object->getType()) {
                $value_1 = $object->getType();
                if (is_null($object->getType())) {
                    $value_1 = $object->getType();
                } elseif (is_string($object->getType())) {
                    $value_1 = $object->getType();
                }
                $data['type'] = $value_1;
            }
            return $data;
        }
        public function getSupportedTypes(?string $format = null) : array
        {
            return [\MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO::class => false];
        }
    }
} else {
    class ImageDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
    {
        use DenormalizerAwareTrait;
        use NormalizerAwareTrait;
        use CheckArray;
        use ValidatorTrait;
        public function supportsDenormalization($data, $type, string $format = null, array $context = []) : bool
        {
            return $type === \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO::class;
        }
        public function supportsNormalization($data, $format = null, array $context = []) : bool
        {
            return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO;
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
            $object = new \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO();
            if (null === $data || false === \is_array($data)) {
                return $object;
            }
            if (\array_key_exists('path', $data) && $data['path'] !== null) {
                $value = $data['path'];
                if (is_null($data['path'])) {
                    $value = $data['path'];
                } elseif (is_string($data['path'])) {
                    $value = $data['path'];
                }
                $object->setPath($value);
            }
            elseif (\array_key_exists('path', $data) && $data['path'] === null) {
                $object->setPath(null);
            }
            if (\array_key_exists('type', $data) && $data['type'] !== null) {
                $value_1 = $data['type'];
                if (is_null($data['type'])) {
                    $value_1 = $data['type'];
                } elseif (is_string($data['type'])) {
                    $value_1 = $data['type'];
                }
                $object->setType($value_1);
            }
            elseif (\array_key_exists('type', $data) && $data['type'] === null) {
                $object->setType(null);
            }
            return $object;
        }
        /**
         * @return array|string|int|float|bool|\ArrayObject|null
         */
        public function normalize($object, $format = null, array $context = [])
        {
            $data = [];
            if ($object->isInitialized('path') && null !== $object->getPath()) {
                $value = $object->getPath();
                if (is_null($object->getPath())) {
                    $value = $object->getPath();
                } elseif (is_string($object->getPath())) {
                    $value = $object->getPath();
                }
                $data['path'] = $value;
            }
            if ($object->isInitialized('type') && null !== $object->getType()) {
                $value_1 = $object->getType();
                if (is_null($object->getType())) {
                    $value_1 = $object->getType();
                } elseif (is_string($object->getType())) {
                    $value_1 = $object->getType();
                }
                $data['type'] = $value_1;
            }
            return $data;
        }
        public function getSupportedTypes(?string $format = null) : array
        {
            return [\MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO::class => false];
        }
    }
}