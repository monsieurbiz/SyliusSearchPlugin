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

use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\CheckArray;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JaneObjectNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use CheckArray;
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    protected $normalizers = ['MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\ImageDTO' => 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Normalizer\\ImageDTONormalizer', 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\ChannelDTO' => 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Normalizer\\ChannelDTONormalizer', 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\ProductTaxonDTO' => 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Normalizer\\ProductTaxonDTONormalizer', 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\TaxonDTO' => 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Normalizer\\TaxonDTONormalizer', 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\ProductAttributeDTO' => 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Normalizer\\ProductAttributeDTONormalizer', 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Model\\PricingDTO' => 'MonsieurBiz\\SyliusSearchPlugin\\Generated\\Normalizer\\PricingDTONormalizer', '\\Jane\\Component\\JsonSchemaRuntime\\Reference' => '\\MonsieurBiz\\SyliusSearchPlugin\\Generated\\Runtime\\Normalizer\\ReferenceNormalizer'];

    protected $normalizersCache = [];

    public function supportsDenormalization($data, $type, $format = null)
    {
        return \array_key_exists($type, $this->normalizers);
    }

    public function supportsNormalization($data, $format = null)
    {
        return \is_object($data) && \array_key_exists($data::class, $this->normalizers);
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $normalizerClass = $this->normalizers[$object::class];
        $normalizer = $this->getNormalizer($normalizerClass);

        return $normalizer->normalize($object, $format, $context);
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $denormalizerClass = $this->normalizers[$class];
        $denormalizer = $this->getNormalizer($denormalizerClass);

        return $denormalizer->denormalize($data, $class, $format, $context);
    }

    private function getNormalizer(string $normalizerClass)
    {
        return $this->normalizersCache[$normalizerClass] ?? $this->initNormalizer($normalizerClass);
    }

    private function initNormalizer(string $normalizerClass)
    {
        $normalizer = new $normalizerClass();
        $normalizer->setNormalizer($this->normalizer);
        $normalizer->setDenormalizer($this->denormalizer);
        $this->normalizersCache[$normalizerClass] = $normalizer;

        return $normalizer;
    }
}
