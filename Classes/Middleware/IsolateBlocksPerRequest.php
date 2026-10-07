<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wazum\FluidBlocks\BlockStore;

/**
 * @internal
 */
final readonly class IsolateBlocksPerRequest implements MiddlewareInterface
{
    public function __construct(
        private BlockStore $store,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->store->isolated(fn (): ResponseInterface => $handler->handle($request));
    }
}
