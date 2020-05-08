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
class TaxonNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Taxon';
    }
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\generated\Model\Taxon;
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
        $object = new \MonsieurBiz\SyliusSearchPlugin\generated\Model\Taxon();
        if (property_exists($data, 'name') && $data->{'name'} !== null) {
            $object->setName($data->{'name'});
        }
        elseif (property_exists($data, 'name') && $data->{'name'} === null) {
            $object->setName(null);
        }
        if (property_exists($data, 'code') && $data->{'code'} !== null) {
            $object->setCode($data->{'code'});
        }
        elseif (property_exists($data, 'code') && $data->{'code'} === null) {
            $object->setCode(null);
        }
        if (property_exists($data, 'position') && $data->{'position'} !== null) {
            $object->setPosition($data->{'position'});
        }
        elseif (property_exists($data, 'position') && $data->{'position'} === null) {
            $object->setPosition(null);
        }
        if (property_exists($data, 'level') && $data->{'level'} !== null) {
            $object->setLevel($data->{'level'});
        }
        elseif (property_exists($data, 'level') && $data->{'level'} === null) {
            $object->setLevel(null);
        }
        if (property_exists($data, 'product_position') && $data->{'product_position'} !== null) {
            $object->setProductPosition($data->{'product_position'});
        }
        elseif (property_exists($data, 'product_position') && $data->{'product_position'} === null) {
            $object->setProductPosition(null);
        }
        return $object;
    }
    public function normalize($object, $format = null, array $context = array())
    {
        $data = new \stdClass();
        if (null !== $object->getName()) {
            $data->{'name'} = $object->getName();
        }
        else {
            $data->{'name'} = null;
        }
        if (null !== $object->getCode()) {
            $data->{'code'} = $object->getCode();
        }
        else {
            $data->{'code'} = null;
        }
        if (null !== $object->getPosition()) {
            $data->{'position'} = $object->getPosition();
        }
        else {
            $data->{'position'} = null;
        }
        if (null !== $object->getLevel()) {
            $data->{'level'} = $object->getLevel();
        }
        else {
            $data->{'level'} = null;
        }
        if (null !== $object->getProductPosition()) {
            $data->{'product_position'} = $object->getProductPosition();
        }
        else {
            $data->{'product_position'} = null;
        }
        return $data;
    }
}