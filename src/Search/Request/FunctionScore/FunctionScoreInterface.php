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

use Elastica\Query\FunctionScore;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

interface FunctionScoreInterface
{
    public function addFunctionScore(FunctionScore $functionScore, RequestConfiguration $requestConfiguration): void;
}
