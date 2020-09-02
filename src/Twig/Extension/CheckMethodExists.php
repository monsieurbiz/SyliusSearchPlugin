<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckMethodExists extends AbstractExtension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('bundle_exists', array($this, 'bundleExists')),
        );
    }

    public function bundleExists($bundle) {
        return array_key_exists(
            $bundle,
            $this->container->getParameter('kernel.bundles')
        );
    }
}
