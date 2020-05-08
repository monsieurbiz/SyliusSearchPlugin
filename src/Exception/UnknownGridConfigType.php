<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Exception;

use MonsieurBiz\SyliusSearchPlugin\Model\Config\GridConfig;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UnknownGridConfigType extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct(sprintf(
            'Unknown GridConfig type, available are "%s", "%s" and "%s"',
            GridConfig::SEARCH_TYPE, GridConfig::TAXON_TYPE, GridConfig::INSTANT_TYPE
        ));
    }
}
