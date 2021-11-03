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

class AttributesNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    use NormalizerAwareTrait;

    public function supportsDenormalization($data, $type, $format = null)
    {
        return 'MonsieurBiz\\SyliusSearchPlugin\\generated\\Model\\Attributes' === $type;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\generated\Model\Attributes;
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
        $object = new \MonsieurBiz\SyliusSearchPlugin\generated\Model\Attributes();
        if (property_exists($data, 'code') && null !== $data->{'code'}) {
            $object->setCode($data->{'code'});
        } elseif (property_exists($data, 'code') && null === $data->{'code'}) {
            $object->setCode(null);
        }
        if (property_exists($data, 'name') && null !== $data->{'name'}) {
            $object->setName($data->{'name'});
        } elseif (property_exists($data, 'name') && null === $data->{'name'}) {
            $object->setName(null);
        }
        if (property_exists($data, 'value') && null !== $data->{'value'}) {
            $values = [];
            foreach ($data->{'value'} as $value) {
                $values[] = $value;
            }
            $object->setValue($values);
        } elseif (property_exists($data, 'value') && null === $data->{'value'}) {
            $object->setValue(null);
        }
        if (property_exists($data, 'locale') && null !== $data->{'locale'}) {
            $object->setLocale($data->{'locale'});
        } elseif (property_exists($data, 'locale') && null === $data->{'locale'}) {
            $object->setLocale(null);
        }
        if (property_exists($data, 'score') && null !== $data->{'score'}) {
            $object->setScore($data->{'score'});
        } elseif (property_exists($data, 'score') && null === $data->{'score'}) {
            $object->setScore(null);
        }

        return $object;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = new \stdClass();
        if (null !== $object->getCode()) {
            $data->{'code'} = $object->getCode();
        } else {
            $data->{'code'} = null;
        }
        if (null !== $object->getName()) {
            $data->{'name'} = $object->getName();
        } else {
            $data->{'name'} = null;
        }
        if (null !== $object->getValue()) {
            $values = [];
            foreach ($object->getValue() as $value) {
                $values[] = $value;
            }
            $data->{'value'} = $values;
        } else {
            $data->{'value'} = null;
        }
        if (null !== $object->getLocale()) {
            $data->{'locale'} = $object->getLocale();
        } else {
            $data->{'locale'} = null;
        }
        if (null !== $object->getScore()) {
            $data->{'score'} = $object->getScore();
        } else {
            $data->{'score'} = null;
        }

        return $data;
    }
}
