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
class ProductTaxonDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\ProductTaxonDTO';
    }
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO;
    }
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (isset($data['$ref'])) {
            return new Reference($data['$ref'], $context['document-origin']);
        }
        if (isset($data['$recursiveRef'])) {
            return new Reference($data['$recursiveRef'], $context['document-origin']);
        }
        $object = new \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO();
        if (null === $data || false === \is_array($data)) {
            return $object;
        }
        if (\array_key_exists('taxon', $data)) {
            $object->setTaxon($this->denormalizer->denormalize($data['taxon'], 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\TaxonDTO', 'json', $context));
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
    public function normalize($object, $format = null, array $context = array())
    {
        $data = array();
        if (null !== $object->getTaxon()) {
            $data['taxon'] = $this->normalizer->normalize($object->getTaxon(), 'json', $context);
        }
        if (null !== $object->getPosition()) {
            $value = $object->getPosition();
            if (is_null($object->getPosition())) {
                $value = $object->getPosition();
            } elseif (is_int($object->getPosition())) {
                $value = $object->getPosition();
            }
            $data['position'] = $value;
        }
        return $data;
    }
}