<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Helper;

class SlugHelper
{
    public static function toSlug(string $label): string
    {
        return urlencode($label);
    }

    public static function toLabel(string $slug): string
    {
        return urldecode($slug);
    }
}
