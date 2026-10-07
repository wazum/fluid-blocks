<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\Tests\Unit\Middleware;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use Wazum\FluidBlocks\BlockStore;
use Wazum\FluidBlocks\Middleware\IsolateBlocksPerRequest;

final class IsolateBlocksPerRequestTest extends TestCase
{
    #[Test]
    public function subRequestStartsWithoutTheBlocksOfTheOuterRequest(): void
    {
        $store = new BlockStore();
        $store->set('stage', 'outer');
        $handler = new class($store) implements RequestHandlerInterface {
            public bool $sawOuterBlock = true;

            public function __construct(private readonly BlockStore $store)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->sawOuterBlock = $this->store->has('stage');

                return new Response();
            }
        };

        (new IsolateBlocksPerRequest($store))->process(new ServerRequest('https://example.com/'), $handler);

        self::assertFalse($handler->sawOuterBlock);
        self::assertSame('outer', $store->get('stage'));
    }
}
