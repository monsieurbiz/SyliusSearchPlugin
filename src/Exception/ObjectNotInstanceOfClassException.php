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

namespace MonsieurBiz\SyliusSearchPlugin\Exception;

use InvalidArgumentException;

class ObjectNotInstanceOfClassException extends InvalidArgumentException
{
    public static function fromClassName(string $className): self
    {
        return new self(\sprintf('Object is not instance of class "%s"', $className));
    }
}
