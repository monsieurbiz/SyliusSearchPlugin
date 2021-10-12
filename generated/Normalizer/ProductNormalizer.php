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
class ProductNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\Product';
    }
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\Product;
    }
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (isset($data['$ref'])) {
            return new Reference($data['$ref'], $context['document-origin']);
        }
        if (isset($data['$recursiveRef'])) {
            return new Reference($data['$recursiveRef'], $context['document-origin']);
        }
        $object = new \MonsieurBiz\SyliusSearchPlugin\Generated\Model\Product();
        if (null === $data || false === \is_array($data)) {
            return $object;
        }
        if (\array_key_exists('id', $data)) {
            $object->setId($data['id']);
        }
        if (\array_key_exists('code', $data)) {
            $object->setCode($data['code']);
        }
        if (\array_key_exists('enabled', $data)) {
            $object->setEnabled($data['enabled']);
        }
        if (\array_key_exists('slug', $data)) {
            $object->setSlug($data['slug']);
        }
        if (\array_key_exists('name', $data)) {
            $object->setName($data['name']);
        }
        if (\array_key_exists('main_taxon', $data)) {
            $object->setMainTaxon($this->denormalizer->denormalize($data['main_taxon'], 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\Taxon', 'json', $context));
        }
        if (\array_key_exists('product_taxons', $data)) {
            $values = array();
            foreach ($data['product_taxons'] as $value) {
                $values[] = $this->denormalizer->denormalize($value, 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\ProductTaxon', 'json', $context);
            }
            $object->setProductTaxons($values);
        }
        if (\array_key_exists('description', $data) && $data['description'] !== null) {
            $value_1 = $data['description'];
            if (is_null($data['description'])) {
                $value_1 = $data['description'];
            } elseif (is_string($data['description'])) {
                $value_1 = $data['description'];
            }
            $object->setDescription($value_1);
        }
        elseif (\array_key_exists('description', $data) && $data['description'] === null) {
            $object->setDescription(null);
        }
        if (\array_key_exists('images', $data) && $data['images'] !== null) {
            $value_2 = $data['images'];
            if (is_null($data['images'])) {
                $value_2 = $data['images'];
            } elseif (is_array($data['images']) && $this->isOnlyNumericKeys($data['images'])) {
                $values_1 = array();
                foreach ($data['images'] as $value_3) {
                    $values_1[] = $this->denormalizer->denormalize($value_3, 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\Image', 'json', $context);
                }
                $value_2 = $values_1;
            }
            $object->setImages($value_2);
        }
        elseif (\array_key_exists('images', $data) && $data['images'] === null) {
            $object->setImages(null);
        }
        if (\array_key_exists('channels', $data)) {
            $values_2 = array();
            foreach ($data['channels'] as $value_4) {
                $values_2[] = $this->denormalizer->denormalize($value_4, 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\Channel', 'json', $context);
            }
            $object->setChannels($values_2);
        }
        if (\array_key_exists('attributes', $data)) {
            $values_3 = array();
            foreach ($data['attributes'] as $value_5) {
                $values_3[] = $this->denormalizer->denormalize($value_5, 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\ProductAttribute', 'json', $context);
            }
            $object->setAttributes($values_3);
        }
        return $object;
    }
    public function normalize($object, $format = null, array $context = array())
    {
        $data = array();
        if (null !== $object->getId()) {
            $data['id'] = $object->getId();
        }
        if (null !== $object->getCode()) {
            $data['code'] = $object->getCode();
        }
        if (null !== $object->getEnabled()) {
            $data['enabled'] = $object->getEnabled();
        }
        if (null !== $object->getSlug()) {
            $data['slug'] = $object->getSlug();
        }
        if (null !== $object->getName()) {
            $data['name'] = $object->getName();
        }
        if (null !== $object->getMainTaxon()) {
            $data['main_taxon'] = $this->normalizer->normalize($object->getMainTaxon(), 'json', $context);
        }
        if (null !== $object->getProductTaxons()) {
            $values = array();
            foreach ($object->getProductTaxons() as $value) {
                $values[] = $this->normalizer->normalize($value, 'json', $context);
            }
            $data['product_taxons'] = $values;
        }
        if (null !== $object->getDescription()) {
            $value_1 = $object->getDescription();
            if (is_null($object->getDescription())) {
                $value_1 = $object->getDescription();
            } elseif (is_string($object->getDescription())) {
                $value_1 = $object->getDescription();
            }
            $data['description'] = $value_1;
        }
        if (null !== $object->getImages()) {
            $value_2 = $object->getImages();
            if (is_null($object->getImages())) {
                $value_2 = $object->getImages();
            } elseif (is_array($object->getImages())) {
                $values_1 = array();
                foreach ($object->getImages() as $value_3) {
                    $values_1[] = $this->normalizer->normalize($value_3, 'json', $context);
                }
                $value_2 = $values_1;
            }
            $data['images'] = $value_2;
        }
        if (null !== $object->getChannels()) {
            $values_2 = array();
            foreach ($object->getChannels() as $value_4) {
                $values_2[] = $this->normalizer->normalize($value_4, 'json', $context);
            }
            $data['channels'] = $values_2;
        }
        if (null !== $object->getAttributes()) {
            $values_3 = array();
            foreach ($object->getAttributes() as $value_5) {
                $values_3[] = $this->normalizer->normalize($value_5, 'json', $context);
            }
            $data['attributes'] = $values_3;
        }
        return $data;
    }
}