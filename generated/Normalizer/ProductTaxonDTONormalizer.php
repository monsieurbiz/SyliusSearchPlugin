<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Normalizer;

use Jane\Component\JsonSchemaRuntime\Reference;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\CheckArray;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductTaxonDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use CheckArray;
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function supportsDenormalization($data, $type, $format = null)
    {
        return 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\ProductTaxonDTO' === $type;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
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
        if (\array_key_exists('position', $data) && null !== $data['position']) {
            $value = $data['position'];
            if (null === $data['position']) {
                $value = $data['position'];
            } elseif (\is_int($data['position'])) {
                $value = $data['position'];
            }
            $object->setPosition($value);
        } elseif (\array_key_exists('position', $data) && null === $data['position']) {
            $object->setPosition(null);
        }

        return $object;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = [];
        if (null !== $object->getTaxon()) {
            $data['taxon'] = $this->normalizer->normalize($object->getTaxon(), 'json', $context);
        }
        if (null !== $object->getPosition()) {
            $value = $object->getPosition();
            if (null === $object->getPosition()) {
                $value = $object->getPosition();
            } elseif (\is_int($object->getPosition())) {
                $value = $object->getPosition();
            }
            $data['position'] = $value;
        }

        return $data;
    }
}
