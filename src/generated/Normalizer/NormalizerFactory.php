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

@trigger_error('The "NormalizerFactory" class is deprecated since Jane 5.3, use "JaneObjectNormalizer" instead.', \E_USER_DEPRECATED);
/**
 * @deprecated The "NormalizerFactory" class is deprecated since Jane 5.3, use "JaneObjectNormalizer" instead.
 */
class NormalizerFactory
{
    public static function create()
    {
        $normalizers = [];
        $normalizers[] = new \Symfony\Component\Serializer\Normalizer\ArrayDenormalizer();
        $normalizers[] = new \MonsieurBiz\SyliusSearchPlugin\generated\Normalizer\JaneObjectNormalizer();

        return $normalizers;
    }
}
