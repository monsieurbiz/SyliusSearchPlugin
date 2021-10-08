<?php

namespace MonsieurBiz\SyliusSearchPlugin\Model\Documentable;

use JoliCode\Elastically\Mapping\MappingProviderInterface;

trait DocumentableMappingProviderTrait
{
    protected MappingProviderInterface $mappingProvider;

    public function setMappingProvider(MappingProviderInterface $mapping): void
    {
        $this->mappingProvider = $mapping;
    }

    public function getMappingProvider(): MappingProviderInterface
    {
        return $this->mappingProvider;
    }
}
