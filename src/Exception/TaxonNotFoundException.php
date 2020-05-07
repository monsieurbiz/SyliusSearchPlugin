<?php

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
