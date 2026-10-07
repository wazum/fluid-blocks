<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Wazum\FluidBlocks\BlockStore;
use Wazum\FluidBlocks\PlaceholderResolver;

final class PlaceholderResolverTest extends TestCase
{
    #[Test]
    public function replacesPlaceholderWithBlockContent(): void
    {
        $store = new BlockStore();
        $store->set('stage', '<h1>Hero</h1>');
        $html = '<header>' . $store->placeholder('stage') . '</header>';

        $resolved = (new PlaceholderResolver($store, new NullLogger()))->resolve($html);

        self::assertSame('<header><h1>Hero</h1></header>', $resolved);
    }

    #[Test]
    public function usesFallbackWhenBlockWasNeverSet(): void
    {
        $store = new BlockStore();
        $store->rememberFallback('stage', '<h1>Default</h1>');
        $html = $store->placeholder('stage');

        self::assertSame('<h1>Default</h1>', (new PlaceholderResolver($store, new NullLogger()))->resolve($html));
    }

    #[Test]
    public function resolvesPlaceholdersInsideBlockContent(): void
    {
        $store = new BlockStore();
        $store->rememberFallback('inner', 'inner fallback');
        $store->set('outer', '[' . $store->placeholder('inner') . ']');
        $html = $store->placeholder('outer');

        self::assertSame('[inner fallback]', (new PlaceholderResolver($store, new NullLogger()))->resolve($html));
    }

    #[Test]
    public function stopsAfterThreePassesWhenContentReferencesItself(): void
    {
        $store = new BlockStore();
        $store->set('loop', 'x' . $store->placeholder('loop'));

        $resolved = (new PlaceholderResolver($store, new NullLogger()))->resolve($store->placeholder('loop'));

        self::assertSame('xxx<!--fluid-blocks:loop-->', $resolved);
    }

    #[Test]
    public function leavesHtmlWithoutPlaceholdersUntouched(): void
    {
        $store = new BlockStore();
        $html = '<p>plain</p>';

        self::assertSame($html, (new PlaceholderResolver($store, new NullLogger()))->resolve($html));
    }

    #[Test]
    public function warnsWhenAnEmittedPlaceholderIsMissingFromTheOutput(): void
    {
        $store = new BlockStore();
        $store->placeholder('stage');
        $store->placeholder('scripts');
        $html = 'The placeholder for stage was cropped away ' . $store->placeholder('scripts');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('warning')
            ->with(self::stringContains('stage'), ['blocks' => ['stage']]);

        (new PlaceholderResolver($store, $logger))->resolve($html);
    }

    #[Test]
    public function doesNotWarnWhenEveryPlaceholderWasResolved(): void
    {
        $store = new BlockStore();
        $html = $store->placeholder('stage');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        (new PlaceholderResolver($store, $logger))->resolve($html);
    }
}
