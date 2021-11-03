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

class TaxonNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    use NormalizerAwareTrait;

    public function supportsDenormalization($data, $type, $format = null)
    {
        return 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Taxon' === $type;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\generated\Model\Taxon;
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
        $object = new \MonsieurBiz\SyliusSearchPlugin\generated\Model\Taxon();
        if (property_exists($data, 'name') && null !== $data->{'name'}) {
            $object->setName($data->{'name'});
        } elseif (property_exists($data, 'name') && null === $data->{'name'}) {
            $object->setName(null);
        }
        if (property_exists($data, 'code') && null !== $data->{'code'}) {
            $object->setCode($data->{'code'});
        } elseif (property_exists($data, 'code') && null === $data->{'code'}) {
            $object->setCode(null);
        }
        if (property_exists($data, 'position') && null !== $data->{'position'}) {
            $object->setPosition($data->{'position'});
        } elseif (property_exists($data, 'position') && null === $data->{'position'}) {
            $object->setPosition(null);
        }
        if (property_exists($data, 'level') && null !== $data->{'level'}) {
            $object->setLevel($data->{'level'});
        } elseif (property_exists($data, 'level') && null === $data->{'level'}) {
            $object->setLevel(null);
        }
        if (property_exists($data, 'product_position') && null !== $data->{'product_position'}) {
            $object->setProductPosition($data->{'product_position'});
        } elseif (property_exists($data, 'product_position') && null === $data->{'product_position'}) {
            $object->setProductPosition(null);
        }

        return $object;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = new \stdClass();
        if (null !== $object->getName()) {
            $data->{'name'} = $object->getName();
        } else {
            $data->{'name'} = null;
        }
        if (null !== $object->getCode()) {
            $data->{'code'} = $object->getCode();
        } else {
            $data->{'code'} = null;
        }
        if (null !== $object->getPosition()) {
            $data->{'position'} = $object->getPosition();
        } else {
            $data->{'position'} = null;
        }
        if (null !== $object->getLevel()) {
            $data->{'level'} = $object->getLevel();
        } else {
            $data->{'level'} = null;
        }
        if (null !== $object->getProductPosition()) {
            $data->{'product_position'} = $object->getProductPosition();
        } else {
            $data->{'product_position'} = null;
        }

        return $data;
    }
}
