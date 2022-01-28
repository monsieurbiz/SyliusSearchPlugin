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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore;

use Sylius\Component\Registry\ServiceRegistry;

final class ProductFunctionScoreRegistry extends ServiceRegistry implements FunctionScoreRegistryInterface
{
    public function __construct(array $functionsScore = [])
    {
        parent::__construct(FunctionScoreInterface::class, 'monsieurbiz.search');

        foreach ($functionsScore as $functionScore) {
            $this->register(\get_class($functionScore), $functionScore);
        }
    }
}
