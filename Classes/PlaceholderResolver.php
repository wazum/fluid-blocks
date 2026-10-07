<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks;

use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final readonly class PlaceholderResolver
{
    private const MAXIMUM_PASSES = 3;

    public function __construct(
        private BlockStore $store,
        private LoggerInterface $logger,
    ) {
    }

    public function resolve(string $html): string
    {
        $resolvedNames = [];
        for ($pass = 0; $pass < self::MAXIMUM_PASSES && str_contains($html, '<!--fluid-blocks:'); ++$pass) {
            $html = preg_replace_callback(
                BlockStore::PLACEHOLDER_PATTERN,
                function (array $match) use (&$resolvedNames): string {
                    $name = $match[1];
                    $resolvedNames[] = $name;

                    return $this->store->has($name) ? $this->store->get($name) : $this->store->fallback($name);
                },
                $html,
            ) ?? $html;
        }

        $missing = array_values(array_diff($this->store->emittedPlaceholders(), $resolvedNames));
        if ($missing !== []) {
            $this->logger->warning(
                'fluid-blocks: the placeholders for the blocks "' . implode('", "', $missing) . '" are missing in the page. A ViewHelper probably changed the output of block:get.',
                ['blocks' => $missing],
            );
        }

        return $html;
    }
}
