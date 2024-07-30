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

namespace MonsieurBiz\SyliusSearchPlugin\Twig\Extension;

use MonsieurBiz\SyliusSearchPlugin\Checker\ElasticsearchCheckerInterface;
use MonsieurBiz\SyliusSearchPlugin\Form\Type\SearchType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class RenderSearchForm extends AbstractExtension
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private Environment $templatingEngine,
        private RequestStack $requestStack,
        private ElasticsearchCheckerInterface $elasticsearchChecker
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('search_form', [$this, 'createForm']),
        ];
    }

    public function createForm(?string $template = null): Markup
    {
        if (false === $this->elasticsearchChecker->check()) {
            return new Markup('', 'UTF-8');
        }

        $request = $this->requestStack->getCurrentRequest();
        $template = $template ?? '@MonsieurBizSyliusSearchPlugin/Search/_form.html.twig';
        /** @var string $query */
        $query = null !== $request ? $request->get('query', '') : '';

        return new Markup($this->templatingEngine->render($template, [
            'form' => $this->formFactory->create(SearchType::class)->createView(),
            'query' => urldecode($query),
        ]), 'UTF-8');
    }
}
