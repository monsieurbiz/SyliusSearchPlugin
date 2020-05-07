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
class AttributesNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Attributes';
    }
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\generated\Model\Attributes;
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
        $object = new \MonsieurBiz\SyliusSearchPlugin\generated\Model\Attributes();
        if (property_exists($data, 'code') && $data->{'code'} !== null) {
            $object->setCode($data->{'code'});
        }
        elseif (property_exists($data, 'code') && $data->{'code'} === null) {
            $object->setCode(null);
        }
        if (property_exists($data, 'name') && $data->{'name'} !== null) {
            $object->setName($data->{'name'});
        }
        elseif (property_exists($data, 'name') && $data->{'name'} === null) {
            $object->setName(null);
        }
        if (property_exists($data, 'value') && $data->{'value'} !== null) {
            $values = array();
            foreach ($data->{'value'} as $value) {
                $values[] = $value;
            }
            $object->setValue($values);
        }
        elseif (property_exists($data, 'value') && $data->{'value'} === null) {
            $object->setValue(null);
        }
        if (property_exists($data, 'locale') && $data->{'locale'} !== null) {
            $object->setLocale($data->{'locale'});
        }
        elseif (property_exists($data, 'locale') && $data->{'locale'} === null) {
            $object->setLocale(null);
        }
        if (property_exists($data, 'score') && $data->{'score'} !== null) {
            $object->setScore($data->{'score'});
        }
        elseif (property_exists($data, 'score') && $data->{'score'} === null) {
            $object->setScore(null);
        }
        return $object;
    }
    public function normalize($object, $format = null, array $context = array())
    {
        $data = new \stdClass();
        if (null !== $object->getCode()) {
            $data->{'code'} = $object->getCode();
        }
        else {
            $data->{'code'} = null;
        }
        if (null !== $object->getName()) {
            $data->{'name'} = $object->getName();
        }
        else {
            $data->{'name'} = null;
        }
        if (null !== $object->getValue()) {
            $values = array();
            foreach ($object->getValue() as $value) {
                $values[] = $value;
            }
            $data->{'value'} = $values;
        }
        else {
            $data->{'value'} = null;
        }
        if (null !== $object->getLocale()) {
            $data->{'locale'} = $object->getLocale();
        }
        else {
            $data->{'locale'} = null;
        }
        if (null !== $object->getScore()) {
            $data->{'score'} = $object->getScore();
        }
        else {
            $data->{'score'} = null;
        }
        return $data;
    }
}