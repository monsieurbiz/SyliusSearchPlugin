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

namespace MonsieurBiz\SyliusSearchPlugin\Routing;

use Exception;
use MonsieurBiz\SyliusSearchPlugin\Checker\ElasticsearchCheckerInterface;
use Symfony\Component\Routing\RequestContext as BaseRequestContext;

class RequestContext extends BaseRequestContext
{
    private BaseRequestContext $decorated;

    private ElasticsearchCheckerInterface $elasticsearchChecker;

    public function __construct(
        BaseRequestContext $decorated,
        ElasticsearchCheckerInterface $elasticsearchChecker
    ) {
        parent::__construct(
            $decorated->getBaseUrl(),
            $decorated->getMethod(),
            $decorated->getHost(),
            $decorated->getScheme(),
            $decorated->getHttpPort(),
            $decorated->getHttpsPort(),
            $decorated->getPathInfo(),
            $decorated->getQueryString()
        );
        $this->decorated = $decorated;
        $this->elasticsearchChecker = $elasticsearchChecker;
    }

    public function checkElasticsearch(): bool
    {
        return $this->elasticsearchChecker->check();
    }

    /**
     * @throws Exception
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $callback = [$this->decorated, $name];
        if (\is_callable($callback)) {
            return \call_user_func($callback, ...$arguments);
        }

        throw new Exception(\sprintf('Method %s not found for class "%s"', $name, \get_class($this->decorated)));
    }
}
