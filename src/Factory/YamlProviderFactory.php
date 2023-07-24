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

namespace MonsieurBiz\SyliusSearchPlugin\Factory;

use JoliCode\Elastically\Mapping\YamlProvider;
use Symfony\Component\Yaml\Parser;

class YamlProviderFactory
{
    public function create(string $configurationDirectory, Parser $parser): YamlProvider
    {
        return new YamlProvider($configurationDirectory, $parser);
    }
}
