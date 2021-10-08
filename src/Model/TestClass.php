<?php

namespace MonsieurBiz\SyliusSearchPlugin\Model;

use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableMappingProviderTrait;

class TestClass
{
    use DocumentableMappingProviderTrait;

    private string $name;

    public function getIndexCode(): string
    {
        return 'test_class';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
