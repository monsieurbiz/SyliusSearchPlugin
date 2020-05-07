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
class PriceNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Price';
    }
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\generated\Model\Price;
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
        $object = new \MonsieurBiz\SyliusSearchPlugin\generated\Model\Price();
        if (property_exists($data, 'channel') && $data->{'channel'} !== null) {
            $object->setChannel($data->{'channel'});
        }
        elseif (property_exists($data, 'channel') && $data->{'channel'} === null) {
            $object->setChannel(null);
        }
        if (property_exists($data, 'currency') && $data->{'currency'} !== null) {
            $object->setCurrency($data->{'currency'});
        }
        elseif (property_exists($data, 'currency') && $data->{'currency'} === null) {
            $object->setCurrency(null);
        }
        if (property_exists($data, 'value') && $data->{'value'} !== null) {
            $object->setValue($data->{'value'});
        }
        elseif (property_exists($data, 'value') && $data->{'value'} === null) {
            $object->setValue(null);
        }
        return $object;
    }
    public function normalize($object, $format = null, array $context = array())
    {
        $data = new \stdClass();
        if (null !== $object->getChannel()) {
            $data->{'channel'} = $object->getChannel();
        }
        else {
            $data->{'channel'} = null;
        }
        if (null !== $object->getCurrency()) {
            $data->{'currency'} = $object->getCurrency();
        }
        else {
            $data->{'currency'} = null;
        }
        if (null !== $object->getValue()) {
            $data->{'value'} = $object->getValue();
        }
        else {
            $data->{'value'} = null;
        }
        return $data;
    }
}