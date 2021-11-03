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

namespace MonsieurBiz\SyliusSearchPlugin\generated\Normalizer;

use Jane\JsonSchemaRuntime\Reference;
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
        return 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Document' === $type;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\generated\Model\Document;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!\is_object($data)) {
            return null;
        }
        if (isset($data->{'$ref'})) {
            return new Reference($data->{'$ref'}, $context['document-origin']);
        }
        if (isset($data->{'$recursiveRef'})) {
            return new Reference($data->{'$recursiveRef'}, $context['document-origin']);
        }
        $object = new \MonsieurBiz\SyliusSearchPlugin\generated\Model\Document();
        if (property_exists($data, 'type') && null !== $data->{'type'}) {
            $object->setType($data->{'type'});
        } elseif (property_exists($data, 'type') && null === $data->{'type'}) {
            $object->setType(null);
        }
        if (property_exists($data, 'code') && null !== $data->{'code'}) {
            $object->setCode($data->{'code'});
        } elseif (property_exists($data, 'code') && null === $data->{'code'}) {
            $object->setCode(null);
        }
        if (property_exists($data, 'id') && null !== $data->{'id'}) {
            $object->setId($data->{'id'});
        } elseif (property_exists($data, 'id') && null === $data->{'id'}) {
            $object->setId(null);
        }
        if (property_exists($data, 'enabled') && null !== $data->{'enabled'}) {
            $object->setEnabled($data->{'enabled'});
        } elseif (property_exists($data, 'enabled') && null === $data->{'enabled'}) {
            $object->setEnabled(null);
        }
        if (property_exists($data, 'inStock') && null !== $data->{'inStock'}) {
            $object->setInStock($data->{'inStock'});
        } elseif (property_exists($data, 'inStock') && null === $data->{'inStock'}) {
            $object->setInStock(null);
        }
        if (property_exists($data, 'slug') && null !== $data->{'slug'}) {
            $object->setSlug($data->{'slug'});
        } elseif (property_exists($data, 'slug') && null === $data->{'slug'}) {
            $object->setSlug(null);
        }
        if (property_exists($data, 'image') && null !== $data->{'image'}) {
            $object->setImage($data->{'image'});
        } elseif (property_exists($data, 'image') && null === $data->{'image'}) {
            $object->setImage(null);
        }
        if (property_exists($data, 'channel') && null !== $data->{'channel'}) {
            $values = [];
            foreach ($data->{'channel'} as $value) {
                $values[] = $value;
            }
            $object->setChannel($values);
        } elseif (property_exists($data, 'channel') && null === $data->{'channel'}) {
            $object->setChannel(null);
        }
        if (property_exists($data, 'main_taxon') && null !== $data->{'main_taxon'}) {
            $object->setMainTaxon($this->denormalizer->denormalize($data->{'main_taxon'}, 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Taxon', 'json', $context));
        } elseif (property_exists($data, 'main_taxon') && null === $data->{'main_taxon'}) {
            $object->setMainTaxon(null);
        }
        if (property_exists($data, 'taxon') && null !== $data->{'taxon'}) {
            $values_1 = [];
            foreach ($data->{'taxon'} as $value_1) {
                $values_1[] = $this->denormalizer->denormalize($value_1, 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Taxon', 'json', $context);
            }
            $object->setTaxon($values_1);
        } elseif (property_exists($data, 'taxon') && null === $data->{'taxon'}) {
            $object->setTaxon(null);
        }
        if (property_exists($data, 'attributes') && null !== $data->{'attributes'}) {
            $values_2 = [];
            foreach ($data->{'attributes'} as $value_2) {
                $values_2[] = $this->denormalizer->denormalize($value_2, 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Attributes', 'json', $context);
            }
            $object->setAttributes($values_2);
        } elseif (property_exists($data, 'attributes') && null === $data->{'attributes'}) {
            $object->setAttributes(null);
        }
        if (property_exists($data, 'price') && null !== $data->{'price'}) {
            $values_3 = [];
            foreach ($data->{'price'} as $value_3) {
                $values_3[] = $this->denormalizer->denormalize($value_3, 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Price', 'json', $context);
            }
            $object->setPrice($values_3);
        } elseif (property_exists($data, 'price') && null === $data->{'price'}) {
            $object->setPrice(null);
        }
        if (property_exists($data, 'original_price') && null !== $data->{'original_price'}) {
            $values_4 = [];
            foreach ($data->{'original_price'} as $value_4) {
                $values_4[] = $this->denormalizer->denormalize($value_4, 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Price', 'json', $context);
            }
            $object->setOriginalPrice($values_4);
        } elseif (property_exists($data, 'original_price') && null === $data->{'original_price'}) {
            $object->setOriginalPrice(null);
        }

        return $object;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = new \stdClass();
        if (null !== $object->getType()) {
            $data->{'type'} = $object->getType();
        } else {
            $data->{'type'} = null;
        }
        if (null !== $object->getCode()) {
            $data->{'code'} = $object->getCode();
        } else {
            $data->{'code'} = null;
        }
        if (null !== $object->getId()) {
            $data->{'id'} = $object->getId();
        } else {
            $data->{'id'} = null;
        }
        if (null !== $object->getEnabled()) {
            $data->{'enabled'} = $object->getEnabled();
        } else {
            $data->{'enabled'} = null;
        }
        if (null !== $object->getInStock()) {
            $data->{'inStock'} = $object->getInStock();
        } else {
            $data->{'inStock'} = null;
        }
        if (null !== $object->getSlug()) {
            $data->{'slug'} = $object->getSlug();
        } else {
            $data->{'slug'} = null;
        }
        if (null !== $object->getImage()) {
            $data->{'image'} = $object->getImage();
        } else {
            $data->{'image'} = null;
        }
        if (null !== $object->getChannel()) {
            $values = [];
            foreach ($object->getChannel() as $value) {
                $values[] = $value;
            }
            $data->{'channel'} = $values;
        } else {
            $data->{'channel'} = null;
        }
        if (null !== $object->getMainTaxon()) {
            $data->{'main_taxon'} = $this->normalizer->normalize($object->getMainTaxon(), 'json', $context);
        } else {
            $data->{'main_taxon'} = null;
        }
        if (null !== $object->getTaxon()) {
            $values_1 = [];
            foreach ($object->getTaxon() as $value_1) {
                $values_1[] = $this->normalizer->normalize($value_1, 'json', $context);
            }
            $data->{'taxon'} = $values_1;
        } else {
            $data->{'taxon'} = null;
        }
        if (null !== $object->getAttributes()) {
            $values_2 = [];
            foreach ($object->getAttributes() as $value_2) {
                $values_2[] = $this->normalizer->normalize($value_2, 'json', $context);
            }
            $data->{'attributes'} = $values_2;
        } else {
            $data->{'attributes'} = null;
        }
        if (null !== $object->getPrice()) {
            $values_3 = [];
            foreach ($object->getPrice() as $value_3) {
                $values_3[] = $this->normalizer->normalize($value_3, 'json', $context);
            }
            $data->{'price'} = $values_3;
        } else {
            $data->{'price'} = null;
        }
        if (null !== $object->getOriginalPrice()) {
            $values_4 = [];
            foreach ($object->getOriginalPrice() as $value_4) {
                $values_4[] = $this->normalizer->normalize($value_4, 'json', $context);
            }
            $data->{'original_price'} = $values_4;
        } else {
            $data->{'original_price'} = null;
        }

        return $data;
    }
}
