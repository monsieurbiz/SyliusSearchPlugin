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

namespace MonsieurBiz\SyliusSearchPlugin\Twig\Extension;

use MonsieurBiz\SyliusSearchPlugin\Helper\RenderDocumentUrlHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RenderDocumentUrl extends AbstractExtension
{
    /**
     * @var RenderDocumentUrlHelper
     */
    private $helper;

    /**
     * RenderDocumentUrl constructor.
     *
     * @param RenderDocumentUrlHelper $helper
     */
    public function __construct(
        RenderDocumentUrlHelper $helper
    ) {
        $this->helper = $helper;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('search_result_url_param', [$this->helper, 'getUrlParams']),
        ];
    }
}
