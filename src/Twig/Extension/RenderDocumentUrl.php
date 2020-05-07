<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Twig\Extension;

use MonsieurBiz\SyliusSearchPlugin\Exception\MissingLocaleException;
use MonsieurBiz\SyliusSearchPlugin\Exception\NotSupportedTypeException;
use MonsieurBiz\SyliusSearchPlugin\Model\Document\Result;
use MonsieurBiz\SyliusSearchPlugin\Provider\UrlParamsProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RenderDocumentUrl extends AbstractExtension
{

    public function getFunctions()
    {
        return array(
            new TwigFunction('search_result_url_param', array($this, 'getUrlParams')),
        );
    }

    /**
     * @param Result $document
     * @return UrlParamsProvider
     * @throws MissingLocaleException
     * @throws NotSupportedTypeException
     */
    public function getUrlParams(Result $document): UrlParamsProvider {
        switch ($document->getType()) {
            case "product" :
                return new UrlParamsProvider('sylius_shop_product_show', ['slug' => $document->getSlug(), '_locale' => $document->getLocale()]);
                break;
        }

        throw new NotSupportedTypeException(sprintf('Object type "%s" not supported to get URL', $this->getType()));
    }
}
