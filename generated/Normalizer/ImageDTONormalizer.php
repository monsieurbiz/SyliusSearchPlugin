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

class ImageDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use CheckArray;
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function supportsDenormalization($data, $type, $format = null)
    {
        return 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\ImageDTO' === $type;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (isset($data['$ref'])) {
            return new Reference($data['$ref'], $context['document-origin']);
        }
        if (isset($data['$recursiveRef'])) {
            return new Reference($data['$recursiveRef'], $context['document-origin']);
        }
        $object = new \MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO();
        if (null === $data || false === \is_array($data)) {
            return $object;
        }
        if (\array_key_exists('path', $data) && null !== $data['path']) {
            $value = $data['path'];
            if (null === $data['path']) {
                $value = $data['path'];
            } elseif (\is_string($data['path'])) {
                $value = $data['path'];
            }
            $object->setPath($value);
        } elseif (\array_key_exists('path', $data) && null === $data['path']) {
            $object->setPath(null);
        }

        return $object;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = [];
        if (null !== $object->getPath()) {
            $value = $object->getPath();
            if (null === $object->getPath()) {
                $value = $object->getPath();
            } elseif (\is_string($object->getPath())) {
                $value = $object->getPath();
            }
            $data['path'] = $value;
        }

        return $data;
    }
}
