<?php

namespace MonsieurBiz\SyliusSearchPlugin\Model\Documentable;

use JoliCode\Elastically\Mapping\MappingProviderInterface;

interface DocumentableInterface
{
    public function getIndexCode(): string;

    // TODO move it in CustomMappingProviderInterface
    public function setMappingProvider(MappingProviderInterface $mapping): void;

    public function getMappingProvider(): MappingProviderInterface;
}
