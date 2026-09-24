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
class ProductTaxonDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO::class;
    }
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO;
    }
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO();
        if (null === $data || false === \is_array($data)) {
            return $object;
        }
        if (isset($data['$ref']) && !isset($data['type']) && !isset($data['properties']) && !isset($data['allOf'])) {
            return new Reference($data['$ref'], $context['document-origin']);
        }
        if (isset($data['$recursiveRef'])) {
            return new Reference($data['$recursiveRef'], $context['document-origin']);
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
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $dataArray = [];
        if ($data->isInitialized('taxon') && null !== $data->getTaxon()) {
            $dataArray['taxon'] = $data->getTaxon() === null ? null : new \MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\JsonObject($this->normalizer->normalize($data->getTaxon(), 'json', $context));
        }
        if ($data->isInitialized('position') && null !== $data->getPosition()) {
            $value = $data->getPosition();
            if (is_null($data->getPosition())) {
                $value = $data->getPosition();
            } elseif (is_int($data->getPosition())) {
                $value = $data->getPosition();
            }
            $dataArray['position'] = $value;
        }
        return $dataArray;
    }
    public function getSupportedTypes(?string $format = null): array
    {
        return [\MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO::class => false];
    }
}