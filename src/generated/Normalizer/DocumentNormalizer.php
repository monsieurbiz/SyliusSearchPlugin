<?php

namespace MonsieurBiz\SyliusSearchPlugin\generated\Normalizer;

use Jane\JsonSchemaRuntime\Reference;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
class DocumentNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Document';
    }
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\generated\Model\Document;
    }
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (!is_object($data)) {
            return null;
        }
        if (isset($data->{'$ref'})) {
            return new Reference($data->{'$ref'}, $context['document-origin']);
        }
        if (isset($data->{'$recursiveRef'})) {
            return new Reference($data->{'$recursiveRef'}, $context['document-origin']);
        }
        $object = new \MonsieurBiz\SyliusSearchPlugin\generated\Model\Document();
        if (property_exists($data, 'type') && $data->{'type'} !== null) {
            $object->setType($data->{'type'});
        }
        elseif (property_exists($data, 'type') && $data->{'type'} === null) {
            $object->setType(null);
        }
        if (property_exists($data, 'code') && $data->{'code'} !== null) {
            $object->setCode($data->{'code'});
        }
        elseif (property_exists($data, 'code') && $data->{'code'} === null) {
            $object->setCode(null);
        }
        if (property_exists($data, 'id') && $data->{'id'} !== null) {
            $object->setId($data->{'id'});
        }
        elseif (property_exists($data, 'id') && $data->{'id'} === null) {
            $object->setId(null);
        }
        if (property_exists($data, 'enabled') && $data->{'enabled'} !== null) {
            $object->setEnabled($data->{'enabled'});
        }
        elseif (property_exists($data, 'enabled') && $data->{'enabled'} === null) {
            $object->setEnabled(null);
        }
        if (property_exists($data, 'inStock') && $data->{'inStock'} !== null) {
            $object->setInStock($data->{'inStock'});
        }
        elseif (property_exists($data, 'inStock') && $data->{'inStock'} === null) {
            $object->setInStock(null);
        }
        if (property_exists($data, 'slug') && $data->{'slug'} !== null) {
            $object->setSlug($data->{'slug'});
        }
        elseif (property_exists($data, 'slug') && $data->{'slug'} === null) {
            $object->setSlug(null);
        }
        if (property_exists($data, 'image') && $data->{'image'} !== null) {
            $object->setImage($data->{'image'});
        }
        elseif (property_exists($data, 'image') && $data->{'image'} === null) {
            $object->setImage(null);
        }
        if (property_exists($data, 'channel') && $data->{'channel'} !== null) {
            $values = array();
            foreach ($data->{'channel'} as $value) {
                $values[] = $value;
            }
            $object->setChannel($values);
        }
        elseif (property_exists($data, 'channel') && $data->{'channel'} === null) {
            $object->setChannel(null);
        }
        if (property_exists($data, 'main_taxon') && $data->{'main_taxon'} !== null) {
            $object->setMainTaxon($this->denormalizer->denormalize($data->{'main_taxon'}, 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Taxon', 'json', $context));
        }
        elseif (property_exists($data, 'main_taxon') && $data->{'main_taxon'} === null) {
            $object->setMainTaxon(null);
        }
        if (property_exists($data, 'taxon') && $data->{'taxon'} !== null) {
            $values_1 = array();
            foreach ($data->{'taxon'} as $value_1) {
                $values_1[] = $this->denormalizer->denormalize($value_1, 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Taxon', 'json', $context);
            }
            $object->setTaxon($values_1);
        }
        elseif (property_exists($data, 'taxon') && $data->{'taxon'} === null) {
            $object->setTaxon(null);
        }
        if (property_exists($data, 'attributes') && $data->{'attributes'} !== null) {
            $values_2 = array();
            foreach ($data->{'attributes'} as $value_2) {
                $values_2[] = $this->denormalizer->denormalize($value_2, 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Attributes', 'json', $context);
            }
            $object->setAttributes($values_2);
        }
        elseif (property_exists($data, 'attributes') && $data->{'attributes'} === null) {
            $object->setAttributes(null);
        }
        if (property_exists($data, 'price') && $data->{'price'} !== null) {
            $values_3 = array();
            foreach ($data->{'price'} as $value_3) {
                $values_3[] = $this->denormalizer->denormalize($value_3, 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Price', 'json', $context);
            }
            $object->setPrice($values_3);
        }
        elseif (property_exists($data, 'price') && $data->{'price'} === null) {
            $object->setPrice(null);
        }
        if (property_exists($data, 'original_price') && $data->{'original_price'} !== null) {
            $values_4 = array();
            foreach ($data->{'original_price'} as $value_4) {
                $values_4[] = $this->denormalizer->denormalize($value_4, 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Price', 'json', $context);
            }
            $object->setOriginalPrice($values_4);
        }
        elseif (property_exists($data, 'original_price') && $data->{'original_price'} === null) {
            $object->setOriginalPrice(null);
        }
        return $object;
    }
    public function normalize($object, $format = null, array $context = array())
    {
        $data = new \stdClass();
        if (null !== $object->getType()) {
            $data->{'type'} = $object->getType();
        }
        else {
            $data->{'type'} = null;
        }
        if (null !== $object->getCode()) {
            $data->{'code'} = $object->getCode();
        }
        else {
            $data->{'code'} = null;
        }
        if (null !== $object->getId()) {
            $data->{'id'} = $object->getId();
        }
        else {
            $data->{'id'} = null;
        }
        if (null !== $object->getEnabled()) {
            $data->{'enabled'} = $object->getEnabled();
        }
        else {
            $data->{'enabled'} = null;
        }
        if (null !== $object->getInStock()) {
            $data->{'inStock'} = $object->getInStock();
        }
        else {
            $data->{'inStock'} = null;
        }
        if (null !== $object->getSlug()) {
            $data->{'slug'} = $object->getSlug();
        }
        else {
            $data->{'slug'} = null;
        }
        if (null !== $object->getImage()) {
            $data->{'image'} = $object->getImage();
        }
        else {
            $data->{'image'} = null;
        }
        if (null !== $object->getChannel()) {
            $values = array();
            foreach ($object->getChannel() as $value) {
                $values[] = $value;
            }
            $data->{'channel'} = $values;
        }
        else {
            $data->{'channel'} = null;
        }
        if (null !== $object->getMainTaxon()) {
            $data->{'main_taxon'} = $this->normalizer->normalize($object->getMainTaxon(), 'json', $context);
        }
        else {
            $data->{'main_taxon'} = null;
        }
        if (null !== $object->getTaxon()) {
            $values_1 = array();
            foreach ($object->getTaxon() as $value_1) {
                $values_1[] = $this->normalizer->normalize($value_1, 'json', $context);
            }
            $data->{'taxon'} = $values_1;
        }
        else {
            $data->{'taxon'} = null;
        }
        if (null !== $object->getAttributes()) {
            $values_2 = array();
            foreach ($object->getAttributes() as $value_2) {
                $values_2[] = $this->normalizer->normalize($value_2, 'json', $context);
            }
            $data->{'attributes'} = $values_2;
        }
        else {
            $data->{'attributes'} = null;
        }
        if (null !== $object->getPrice()) {
            $values_3 = array();
            foreach ($object->getPrice() as $value_3) {
                $values_3[] = $this->normalizer->normalize($value_3, 'json', $context);
            }
            $data->{'price'} = $values_3;
        }
        else {
            $data->{'price'} = null;
        }
        if (null !== $object->getOriginalPrice()) {
            $values_4 = array();
            foreach ($object->getOriginalPrice() as $value_4) {
                $values_4[] = $this->normalizer->normalize($value_4, 'json', $context);
            }
            $data->{'original_price'} = $values_4;
        }
        else {
            $data->{'original_price'} = null;
        }
        return $data;
    }
}
