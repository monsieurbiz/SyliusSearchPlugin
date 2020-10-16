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

namespace MonsieurBiz\SyliusSearchPlugin\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class TaxonNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('Taxon cannot be found.');
    }
}
