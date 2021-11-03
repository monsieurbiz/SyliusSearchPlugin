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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Documentable;

use MonsieurBiz\SyliusSearchPlugin\Model\Document\ResultInterface;

interface DocumentableInterface
{
    public function getDocumentType(): string;

    public function convertToDocument(string $locale): ResultInterface;
}
