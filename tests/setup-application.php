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

// Install only this repository's test overlay. Never copy local environment or
// released Sylius 1 application migrations into the generated Sylius 2 app.
$root = dirname(__DIR__);
$app = $root . '/tests/Application';
if (!is_file($app . '/composer.json') || is_link($app)) {
    throw new RuntimeException('Create the Sylius 2 test application first.');
}
$copy = static function (string $source, string $target) use ($app): void {
    $directory = dirname($target);
    for ($parent = $directory; strlen($parent) >= strlen($app); $parent = dirname($parent)) {
        if (is_link($parent)) {
            throw new RuntimeException('Refusing a symlinked destination directory.');
        }
    }
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Cannot create overlay directory.');
    }
    if (is_link($target)) {
        unlink($target);
    }
    if (!copy($source, $target)) {
        throw new RuntimeException('Cannot copy test overlay.');
    }
};
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/dist', FilesystemIterator::SKIP_DOTS)) as $file) {
    $relative = substr($file->getPathname(), strlen($root . '/dist/'));
    if (!$file->isFile() || $file->isLink() || str_starts_with($file->getBasename(), '.env') || str_starts_with($relative, 'src/Migrations/')) {
        continue;
    }
    $copy($file->getPathname(), $app . '/' . $relative);
}
$copy($root . '/docker-compose.yaml.dist', $app . '/docker-compose.yaml');

// The published Search 2.0 recipe is for Sylius 1. Replace its bundle wiring
// locally until the independent Search 3.0 recipe has been approved/published.
$bundles = require $app . '/config/bundles.php';
unset($bundles['Jane\\Bundle\\AutoMapperBundle\\JaneAutoMapperBundle'], $bundles['AutoMapper\\Bundle\\AutoMapperBundle'], $bundles['AutoMapper\\Symfony\\Bundle\\AutoMapperBundle']);

$bundles['MonsieurBiz\\SyliusSettingsPlugin\\MonsieurBizSyliusSettingsPlugin'] = ['all' => true];
$bundles['MonsieurBiz\\SyliusSearchPlugin\\MonsieurBizSyliusSearchPlugin'] = ['all' => true];
if (is_link($app . '/config/bundles.php')) {
    throw new RuntimeException('Refusing a symlinked bundle configuration.');
}
file_put_contents($app . '/config/bundles.php', "<?php\n\nreturn " . var_export($bundles, true) . ";\n");

// Repair the old harness's shared dependency link without touching its target.
if (is_link($root . '/node_modules')) {
    unlink($root . '/node_modules');
}
