<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Context;

use Sylius\Component\Core\Model\TaxonInterface;

interface TaxonContextInterface
{
    public function getTaxon(): TaxonInterface;
}
