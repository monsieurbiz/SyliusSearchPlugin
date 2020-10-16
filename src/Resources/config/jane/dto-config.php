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

return [
    'json-schema-file' => 'src/Resources/config/jane/document.json',
    'root-class' => 'Model',
    'namespace' => 'MonsieurBiz\SyliusSearchPlugin\generated',
    'directory' => 'src/generated',
    'strict' => false,
];
