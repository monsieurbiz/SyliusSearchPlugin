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

namespace MonsieurBiz\SyliusSearchPlugin\Tests\Unit;

use MonsieurBiz\SyliusSearchPlugin\Form\Type\Settings\SettingsSearchType;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Form\Forms;

final class SettingsSearchTypeTest extends TestCase
{
    public function testInheritedChannelLimitsHaveEditableDefaultsAndLiveCollectionControls(): void
    {
        $form = $this->createSettingsForm([]);
        $limits = $form->get('limits__products');
        self::assertSame([9, 18, 27], $limits->get('search')->getData());
        self::assertTrue($form->get('limits__products___default')->getData());
        $view = $limits->createView();
        foreach (['search', 'instant_search', 'taxon'] as $type) {
            self::assertSame('addCollectionItem', $view[$type]->vars['button_add']->vars['attr']['data-live-action-param']);
            self::assertSame('removeCollectionItem', $view[$type][0]->vars['button_delete']->vars['attr']['data-live-action-param']);
        }
    }

    public function testAnExistingChannelOverrideIsNotReplacedByDefaults(): void
    {
        $form = $this->createSettingsForm(['limits__products' => ['search' => [15], 'instant_search' => [3], 'taxon' => [6]]]);
        self::assertSame([15], $form->get('limits__products')->get('search')->getData());
        self::assertFalse($form->get('limits__products___default')->getData());
    }

    private function createSettingsForm(array $data): \Symfony\Component\Form\FormInterface
    {
        $document = $this->createMock(DocumentableInterface::class);
        $document->method('getIndexCode')->willReturn('products');
        $registry = $this->createMock(ServiceRegistryInterface::class);
        $registry->method('all')->willReturn([$document]);
        $settings = $this->createMock(SettingsInterface::class);
        $settings->method('getCurrentValue')->willReturn(['search' => [9, 18, 27], 'instant_search' => [5], 'taxon' => [9, 18, 27]]);
        $factory = Forms::createFormFactoryBuilder()->addType(new SettingsSearchType($registry))->getFormFactory();

        return $factory->create(SettingsSearchType::class, $data, ['settings' => $settings, 'channel' => new Channel()]);
    }
}
