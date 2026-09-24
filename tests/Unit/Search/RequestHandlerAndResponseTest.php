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

namespace MonsieurBiz\SyliusSearchPlugin\Tests\Unit\Search;

use BadMethodCallException;
use Elastica\Query;
use Elastica\Response as ElasticaResponse;
use Elastica\ResultSet;
use MonsieurBiz\SyliusSearchPlugin\Exception\UnknownRequestTypeException;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Filter\Filter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestHandler;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterBuilders\FilterBuilderInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\ResponseFactory;
use MonsieurBiz\SyliusSearchPlugin\Tests\Support\SearchTestCase;
use Pagerfanta\Adapter\AdapterInterface;

final class RequestHandlerAndResponseTest extends SearchTestCase
{
    public function testRequestHandlerSelectsFirstSupportingRequestAndInjectsConfiguration(): void
    {
        $configuration = $this->configuration();
        $unsupported = new RecordingRequest(false);
        $supported = new RecordingRequest(true);
        $notVisited = new RecordingRequest(true);

        $request = (new RequestHandler([$unsupported, $supported, $notVisited]))->getRequest($configuration);

        self::assertSame($supported, $request);
        self::assertSame($configuration, $supported->configuration);
        self::assertNull($unsupported->configuration);
        self::assertNull($notVisited->configuration);
    }

    public function testRequestHandlerReportsUnknownTypeWhenNoRequestSupportsConfiguration(): void
    {
        $this->expectException(UnknownRequestTypeException::class);

        (new RequestHandler([new RecordingRequest(false)]))->getRequest($this->configuration());
    }

    public function testResponseFactoryBuildsPaginatorAndOrdersFiltersByBuilderPosition(): void
    {
        $configuration = $this->configuration(['limit' => '18', 'page' => '2']);
        $adapter = new ResultSetAdapter(['second' => ['doc_count' => 1], 'first' => ['doc_count' => 2]], 30);
        $firstFilter = new Filter($configuration, 'first', 'First', 2);
        $secondFilter = new Filter($configuration, 'second', 'Second', 1);

        $response = (new ResponseFactory([
            new StaticFilterBuilder('second', 20, [$secondFilter]),
            new StaticFilterBuilder('first', 10, [$firstFilter]),
        ]))->build($configuration, $adapter, $this->document());

        self::assertSame(30, $response->count());
        self::assertSame(18, $response->getPaginator()->getMaxPerPage());
        self::assertSame(2, $response->getPaginator()->getCurrentPage());
        self::assertSame([$firstFilter, $secondFilter], $response->getFilters());
        self::assertSame($this->document()->getIndexCode(), $response->getDocumentable()->getIndexCode());
    }

    public function testResponseDoesNotCallFilterBuildersWithoutAggregations(): void
    {
        $builder = $this->createMock(FilterBuilderInterface::class);
        $builder->expects(self::never())->method('build');

        $response = (new ResponseFactory([$builder]))->build($this->configuration(), new ResultSetAdapter([], 0), $this->document());

        self::assertSame([], $response->getFilters());
    }
}

final class RecordingRequest implements RequestInterface
{
    public ?RequestConfiguration $configuration = null;

    public function __construct(private readonly bool $supports)
    {
    }

    public function getType(): string
    {
        return RequestInterface::SEARCH_TYPE;
    }

    public function getDocumentable(): DocumentableInterface
    {
        throw new BadMethodCallException('Not used by this test.');
    }

    public function getQuery(): Query
    {
        throw new BadMethodCallException('Not used by this test.');
    }

    public function supports(string $type, string $documentableCode): bool
    {
        return $this->supports;
    }

    public function setConfiguration(RequestConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }
}

final class StaticFilterBuilder implements FilterBuilderInterface
{
    public function __construct(private readonly string $aggregationCode, private readonly int $position, private readonly array $filters)
    {
    }

    public function build(DocumentableInterface $documentable, RequestConfiguration $requestConfiguration, string $aggregationCode, array $aggregationData): ?array
    {
        return $this->aggregationCode === $aggregationCode ? $this->filters : null;
    }

    public function getPosition(): int
    {
        return $this->position;
    }
}

final class ResultSetAdapter implements AdapterInterface
{
    public function __construct(private readonly array $aggregations, private readonly int $total)
    {
    }

    public function getNbResults(): int
    {
        return $this->total;
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return new ResultSet(new ElasticaResponse(['hits' => ['total' => ['value' => $this->total]], 'aggregations' => $this->aggregations]), new Query(), []);
    }
}
