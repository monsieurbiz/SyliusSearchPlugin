<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model\Product;

use Doctrine\ORM\Mapping as ORM;

trait FilterableTrait
{
    /**
     * @var bool
     * @ORM\Column(name="filterable", type="boolean", nullable=false)
     */
    protected $filterable = false;

    /**
     * @return bool
     */
    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    /**
     * @param bool $filterable
     */
    public function setFilterable(bool $filterable): void
    {
        $this->filterable = $filterable;
    }
}
