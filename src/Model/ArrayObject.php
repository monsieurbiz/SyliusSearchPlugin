<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model;

class ArrayObject extends \ArrayObject
{
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }
}
