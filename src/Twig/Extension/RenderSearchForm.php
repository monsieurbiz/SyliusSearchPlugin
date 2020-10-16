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

namespace MonsieurBiz\SyliusSearchPlugin\Twig\Extension;

use MonsieurBiz\SyliusSearchPlugin\Form\Type\SearchType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Templating\EngineInterface;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class RenderSearchForm extends AbstractExtension
{
    private $formFactory;

    private $templatingEngine;

    private $requestStack;

    public function __construct(FormFactoryInterface $formFactory, EngineInterface $templatingEngine, RequestStack $requestStack)
    {
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('search_form', [$this, 'createForm']),
        ];
    }

    public function createForm($template = null)
    {
        $template = $template ?? '@MonsieurBizSyliusSearchPlugin/form.html.twig';

        return new Markup($this->templatingEngine->render($template, [
            'form' => $this->formFactory->create(SearchType::class)->createView(),
            'query' => urldecode($this->requestStack->getCurrentRequest()->get('query') ?? ''),
        ]), 'UTF-8');
    }
}
