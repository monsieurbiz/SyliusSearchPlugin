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

namespace MonsieurBiz\SyliusSearchPlugin\Tests\Integration;

use Composer\InstalledVersions;
use Composer\Semver\Semver;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromIds;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

/** Runs against the generated application's own vendor, kernel, fixtures and Elasticsearch. */
final class ApplicationTest extends WebTestCase
{
    public function testTheInstalledApplicationMatchesTheRequestedVersions(): void
    {
        self::assertSame(getenv('SYLIUS_VERSION'), ltrim(InstalledVersions::getPrettyVersion('sylius/sylius'), 'v'));
        self::assertTrue(Semver::satisfies(InstalledVersions::getVersion('symfony/framework-bundle'), getenv('SYMFONY_VERSION')));
        self::assertTrue(Semver::satisfies(InstalledVersions::getVersion('symfony/serializer'), getenv('SYMFONY_VERSION')));
    }

    public function testShopSearchInstantTaxonAndReceivedReindexMessage(): void
    {
        $client = $this->shopClient();
        $product = self::getContainer()->get('sylius.repository.product')->findOneBy(['enabled' => true]);
        self::assertNotNull($product);
        $product->setCurrentLocale('en_US');
        $name = $product->getName();
        $taxon = $product->getMainTaxon();
        $taxon->setCurrentLocale('en_US');
        $taxonSlug = $taxon->getSlug();
        self::getContainer()->get('messenger.default_bus')->dispatch(new Envelope(
            new ProductReindexFromIds([$product->getId()]),
            [new ReceivedStamp('async_search')],
        ));

        $crawler = $client->request('GET', '/en_US/');
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.autocomplete-search'));
        self::assertCount(1, $crawler->filter('script[src*="monsieurbiz-search."]'));

        $client->request('POST', '/en_US/search', ['monsieurbiz_searchplugin_search' => ['query' => $name]]);
        self::assertTrue($client->getResponse()->isRedirection());
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#products', $name);
        self::assertSelectorExists('input[name="price[min]"]');

        $client->request('POST', '/en_US/instant', ['query' => $name]);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.result', $name);

        $client->request('GET', '/en_US/taxons/' . $taxonSlug . '?limit=18&sorting[price]=asc');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.sylius-paginate', '18');

        $client->request('GET', '/en_US/search/zzqaunmatched20260914');
        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('#products .card');
    }

    public function testTheShopApiRouteStillBoots(): void
    {
        $client = $this->shopClient();
        $client->request('GET', '/api/v2/shop/taxons', server: ['HTTP_ACCEPT' => 'application/ld+json']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    #[DataProvider('searchableResources')]
    public function testAdminSearchFieldsRenderAndPersist(string $resource, string $code, string $path): void
    {
        $client = $this->adminClient();
        $entity = self::getContainer()->get('sylius.repository.' . $resource)->findOneBy(['code' => $code]);
        self::assertNotNull($entity);
        $originalWeight = $entity->getSearchWeight();
        $updatedWeight = 1 === $originalWeight ? 2 : 1;
        $editPath = '/admin/' . $path . '/' . $entity->getId() . '/edit';
        $client->request('GET', '/admin/' . $path . '/');
        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
        }
        self::assertResponseIsSuccessful();

        try {
            $this->saveWeight($client, $editPath, $updatedWeight);
            $crawler = $client->request('GET', $editPath);
            $field = $crawler->filter('select[name$="[search_weight]"]')->attr('name');
            $form = $crawler->filterXPath('//form[.//select[contains(@name, "search_weight")]]')->first()->form();
            self::assertSame((string) $updatedWeight, $form[$field]->getValue());
        } finally {
            $this->saveWeight($client, $editPath, $originalWeight);
        }
    }

    public static function searchableResources(): iterable
    {
        yield 'attribute' => ['product_attribute', 't_shirt_brand', 'product-attributes'];
        yield 'option' => ['product_option', 't_shirt_size', 'product-options'];
    }

    public function testSettingsCollectionsRenderAndLimitsPersist(): void
    {
        $client = $this->adminClient();
        $client->request('GET', '/admin/settings');
        self::assertResponseIsSuccessful();
        $path = '/admin/settings/edit/monsieurbiz.search';
        $field = 'mbiz_settings[default-default][limits__monsieurbiz_product][search][0]';
        $crawler = $client->request('GET', $path);
        self::assertResponseIsSuccessful();
        self::assertGreaterThanOrEqual(6, $crawler->filter('[data-live-action-param="addCollectionItem"]')->count());
        $form = $crawler->filter('form[name="mbiz_settings"]')->form();
        $original = $form[$field]->getValue();

        try {
            $form[$field] = '12';
            $client->submit($form);
            self::assertTrue($client->getResponse()->isRedirection());
            $crawler = $client->request('GET', $path);
            self::assertSame('12', $crawler->filter('form[name="mbiz_settings"]')->form()[$field]->getValue());
        } finally {
            $form = $client->request('GET', $path)->filter('form[name="mbiz_settings"]')->form();
            $form[$field] = $original;
            $client->submit($form);
            self::assertTrue($client->getResponse()->isRedirection());
        }
    }

    private function shopClient(): KernelBrowser
    {
        $client = self::createClient(['environment' => 'dev', 'debug' => true]);
        $channel = self::getContainer()->get('sylius.repository.channel')->findOneBy(['code' => 'FASHION_WEB', 'enabled' => true]);
        self::assertNotNull($channel);
        $client->setServerParameter('HTTP_HOST', $channel->getHostname() ?: 'localhost');

        return $client;
    }

    private function adminClient(): KernelBrowser
    {
        $client = self::createClient(['environment' => 'dev', 'debug' => true]);
        $admin = self::getContainer()->get('sylius.repository.admin_user')->findOneBy([]);
        self::assertNotNull($admin);
        $client->loginUser($admin, 'admin');

        return $client;
    }

    private function saveWeight(KernelBrowser $client, string $path, int $weight): void
    {
        $crawler = $client->request('GET', $path);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name$="[searchable]"]');
        self::assertSelectorExists('input[name$="[filterable]"]');
        $field = $crawler->filter('select[name$="[search_weight]"]')->attr('name');
        $form = $crawler->filterXPath('//form[.//select[contains(@name, "search_weight")]]')->first()->form();
        $form[$field] = (string) $weight;
        $client->submit($form);
        self::assertTrue($client->getResponse()->isRedirection());
    }
}
