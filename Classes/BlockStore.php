<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks;

use Closure;
use InvalidArgumentException;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * @internal
 */
final class BlockStore implements SingletonInterface
{
    public const PLACEHOLDER_PATTERN = '/<!--fluid-blocks:([A-Za-z0-9_.\-]+)-->/';

    private const NAME_PATTERN = '/^[A-Za-z0-9_.\-]+$/';

    /**
     * @var array<string, string>
     */
    private array $blocks = [];

    /**
     * @var array<string, string>
     */
    private array $fallbacks = [];

    /**
     * @var array<string, true>
     */
    private array $emitted = [];

    /**
     * @template T
     *
     * @param Closure(): T $callback
     *
     * @return T
     */
    public function isolated(Closure $callback): mixed
    {
        [$blocks, $fallbacks, $emitted] = [$this->blocks, $this->fallbacks, $this->emitted];
        $this->reset();
        try {
            return $callback();
        } finally {
            [$this->blocks, $this->fallbacks, $this->emitted] = [$blocks, $fallbacks, $emitted];
        }
    }

    public function set(string $name, string $content): void
    {
        $this->blocks[$this->validName($name)] = $content;
    }

    public function push(string $name, string $content): void
    {
        $name = $this->validName($name);
        $this->blocks[$name] = ($this->blocks[$name] ?? '') . $content;
    }

    public function has(string $name): bool
    {
        return isset($this->blocks[$this->validName($name)]);
    }

    public function get(string $name): string
    {
        return $this->blocks[$name] ?? '';
    }

    public function rememberFallback(string $name, string $fallback): void
    {
        $this->fallbacks[$this->validName($name)] = $fallback;
    }

    public function placeholder(string $name): string
    {
        $name = $this->validName($name);
        $this->emitted[$name] = true;

        return '<!--fluid-blocks:' . $name . '-->';
    }

    /**
     * @return list<string>
     */
    public function emittedPlaceholders(): array
    {
        return array_map(strval(...), array_keys($this->emitted));
    }

    public function fallback(string $name): string
    {
        return $this->fallbacks[$name] ?? '';
    }

    public function reset(): void
    {
        $this->blocks = [];
        $this->fallbacks = [];
        $this->emitted = [];
    }

    private function validName(string $name): string
    {
        if (preg_match(self::NAME_PATTERN, $name) !== 1) {
            throw new InvalidArgumentException(sprintf('Block name "%s" is not valid. Use only letters, digits, dots, dashes and underscores.', $name), 1759800001);
        }

        return $name;
    }
}
