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

// Build an unpublished Flex endpoint from the owner's separate recipe checkout.
$recipeDirectory = $argv[1] ?? dirname(__DIR__, 2) . '/symfony-recipes/monsieurbiz/sylius-search-plugin/3.0';
$outputDirectory = __DIR__ . '/RecipeEndpoint';
$manifest = json_decode(file_get_contents($recipeDirectory . '/manifest.json'), true, 512, \JSON_THROW_ON_ERROR);
$files = [];
foreach (['config/packages/monsieurbiz_sylius_search_plugin.yaml', 'config/routes/monsieurbiz_sylius_search_plugin.yaml'] as $path) {
    $files[$path] = ['contents' => explode("\n", file_get_contents($recipeDirectory . '/' . $path)), 'executable' => false];
}
$recipe = ['manifest' => $manifest, 'files' => $files];
$recipe['ref'] = sha1(json_encode($recipe, \JSON_THROW_ON_ERROR));
$index = [
    'recipes' => ['monsieurbiz/sylius-search-plugin' => ['3.0']],
    'branch' => 'upgrade-sylius-search-plugin-2.x',
    'is_contrib' => true,
    '_links' => [
        'repository' => 'github.com/monsieurbiz/symfony-recipes',
        'origin_template' => '{package}:{version}@github.com/monsieurbiz/symfony-recipes:upgrade-sylius-search-plugin-2.x',
        'recipe_template_relative' => '{package_dotted}.{version}.json',
    ],
];
if (!is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0775, true);
}
file_put_contents($outputDirectory . '/index.json', json_encode($index, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));
file_put_contents($outputDirectory . '/monsieurbiz.sylius-search-plugin.3.0.json', json_encode(['manifests' => ['monsieurbiz/sylius-search-plugin' => $recipe]], \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));
echo "Unpublished recipe endpoint prepared.\n";
