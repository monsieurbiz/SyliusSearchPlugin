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

require __DIR__ . '/Application/vendor/autoload.php';

// Bootstrap the generated application's normal environment, just like bin/console.
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(__DIR__ . '/Application/.env');
