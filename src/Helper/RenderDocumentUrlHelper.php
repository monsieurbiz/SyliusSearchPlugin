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

namespace MonsieurBiz\SyliusSearchPlugin\Helper;

use MonsieurBiz\SyliusSearchPlugin\Exception\MissingLocaleException;
use MonsieurBiz\SyliusSearchPlugin\Exception\NotSupportedTypeException;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\Result;
use MonsieurBiz\SyliusSearchPlugin\Provider\UrlParamsProvider;

class RenderDocumentUrlHelper
{
    /**
     * @throws MissingLocaleException
     * @throws NotSupportedTypeException
     */
    public function getUrlParams(Result $document): UrlParamsProvider
    {
        switch ($document->getType()) {
            case 'product':
                return new UrlParamsProvider('sylius_shop_product_show', ['slug' => $document->getSlug(), '_locale' => $document->getLocale()]);

                break;
        }

        throw new NotSupportedTypeException(sprintf('Object type "%s" not supported to get URL', $document->getType()));
    }
}
