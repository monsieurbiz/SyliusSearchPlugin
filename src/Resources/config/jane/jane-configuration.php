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

return [
    'json-schema-file' => __DIR__ . '/json-schema.json',
    'root-class' => 'Model',
    'namespace' => 'MonsieurBiz\SyliusSearchPlugin\Generated',
    'directory' => __DIR__ . '/../../../../generated',
];
