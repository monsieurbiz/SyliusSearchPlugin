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

namespace MonsieurBiz\SyliusSearchPlugin\Manager;

class AutomaticProductReindexManager implements AutomaticReindexManagerInterface
{
    private bool $shouldAutomaticallyReindex = true;

    public function shouldAutomaticallyReindex(bool $shouldAutomaticallyReindex): void
    {
        $this->shouldAutomaticallyReindex = $shouldAutomaticallyReindex;
    }

    public function shouldBeAutomaticallyReindex(): bool
    {
        return $this->shouldAutomaticallyReindex;
    }
}
