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

namespace MonsieurBiz\SyliusSearchPlugin\Adapter;

use MonsieurBiz\SyliusSearchPlugin\Model\Document\ResultSet;
use Pagerfanta\Adapter\AdapterInterface;

class ResultSetAdapter implements AdapterInterface
{
    /** @var ResultSet */
    private $resultSet;

    /**
     * Constructor.
     *
     * @param ResultSet $resultSet
     */
    public function __construct(ResultSet $resultSet)
    {
        $this->resultSet = $resultSet;
    }

    /**
     * Returns the array.
     *
     * @return ResultSet
     */
    public function getResultSet()
    {
        return $this->resultSet;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        return $this->resultSet->getTotalHits();
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        return \array_slice($this->resultSet->getResults(), $offset, $length);
    }
}
