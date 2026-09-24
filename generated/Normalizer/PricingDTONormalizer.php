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
class PricingDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === \MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO::class;
    }
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO;
    }
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $object = new \MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO();
        if (null === $data || false === \is_array($data)) {
            return $object;
        }
        if (isset($data['$ref']) && !isset($data['type']) && !isset($data['properties']) && !isset($data['allOf'])) {
            return new Reference($data['$ref'], $context['document-origin']);
        }
        if (isset($data['$recursiveRef'])) {
            return new Reference($data['$recursiveRef'], $context['document-origin']);
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
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $dataArray = [];
        if ($data->isInitialized('channelCode') && null !== $data->getChannelCode()) {
            $dataArray['channel_code'] = $data->getChannelCode();
        }
        if ($data->isInitialized('price') && null !== $data->getPrice()) {
            $value = $data->getPrice();
            if (is_null($data->getPrice())) {
                $value = $data->getPrice();
            } elseif (is_int($data->getPrice())) {
                $value = $data->getPrice();
            }
            $dataArray['price'] = $value;
        }
        if ($data->isInitialized('originalPrice') && null !== $data->getOriginalPrice()) {
            $value_1 = $data->getOriginalPrice();
            if (is_null($data->getOriginalPrice())) {
                $value_1 = $data->getOriginalPrice();
            } elseif (is_int($data->getOriginalPrice())) {
                $value_1 = $data->getOriginalPrice();
            }
            $dataArray['original_price'] = $value_1;
        }
        if ($data->isInitialized('priceReduced') && null !== $data->getPriceReduced()) {
            $value_2 = $data->getPriceReduced();
            if (is_bool($data->getPriceReduced())) {
                $value_2 = $data->getPriceReduced();
            }
            $dataArray['price_reduced'] = $value_2;
        }
        return $dataArray;
    }
    public function getSupportedTypes(?string $format = null): array
    {
        return [\MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO::class => false];
    }
}