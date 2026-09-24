<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Normalizer;

use Jane\Component\JsonSchemaRuntime\Reference;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\CheckArray;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\ValidatorTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
class ImageDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO::class;
    }
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO;
    }
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO();
        if (null === $data || false === \is_array($data)) {
            return $object;
        }
        if (isset($data['$ref']) && !isset($data['type']) && !isset($data['properties']) && !isset($data['allOf'])) {
            return new Reference($data['$ref'], $context['document-origin']);
        }
        if (isset($data['$recursiveRef'])) {
            return new Reference($data['$recursiveRef'], $context['document-origin']);
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
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $dataArray = [];
        if ($data->isInitialized('path') && null !== $data->getPath()) {
            $value = $data->getPath();
            if (is_null($data->getPath())) {
                $value = $data->getPath();
            } elseif (is_string($data->getPath())) {
                $value = $data->getPath();
            }
            $dataArray['path'] = $value;
        }
        if ($data->isInitialized('type') && null !== $data->getType()) {
            $value_1 = $data->getType();
            if (is_null($data->getType())) {
                $value_1 = $data->getType();
            } elseif (is_string($data->getType())) {
                $value_1 = $data->getType();
            }
            $dataArray['type'] = $value_1;
        }
        return $dataArray;
    }
    public function getSupportedTypes(?string $format = null): array
    {
        return [\MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO::class => false];
    }
}