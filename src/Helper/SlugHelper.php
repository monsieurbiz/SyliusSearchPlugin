<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Helper;

class SlugHelper
{
    public static function toSlug($label): string
    {
        return urlencode($label);
    }

    public static function toLabel($slug): string
    {
        return urldecode($slug);
    }
}
