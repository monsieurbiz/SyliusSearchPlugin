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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Documentable;

use MonsieurBiz\SyliusSearchPlugin\Model\Datasource\DatasourceInterface;

trait DocumentableDatasourceTrait
{
    protected DatasourceInterface $datasource;

    public function setDatasource(DatasourceInterface $datasource): void
    {
        $this->datasource = $datasource;
    }

    public function getDatasource(): DatasourceInterface
    {
        return $this->datasource;
    }
}
