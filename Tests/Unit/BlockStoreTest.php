<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Wazum\FluidBlocks\BlockStore;

final class BlockStoreTest extends TestCase
{
    #[Test]
    public function isolatedStartsEmptyAndRestoresTheOuterBlocksAfterwards(): void
    {
        $store = new BlockStore();
        $store->set('stage', 'outer');
        $store->rememberFallback('title', 'outer fallback');
        $store->placeholder('title');

        $inside = $store->isolated(function () use ($store): bool {
            $wasEmpty = !$store->has('stage') && $store->emittedPlaceholders() === [];
            $store->set('stage', 'inner');

            return $wasEmpty;
        });

        self::assertTrue($inside);
        self::assertSame('outer', $store->get('stage'));
        self::assertSame('outer fallback', $store->fallback('title'));
        self::assertSame(['title'], $store->emittedPlaceholders());
    }

    #[Test]
    public function isolatedRestoresTheOuterBlocksWhenTheCallbackThrows(): void
    {
        $store = new BlockStore();
        $store->set('stage', 'outer');

        try {
            $store->isolated(function () use ($store): void {
                $store->set('stage', 'inner');
                throw new RuntimeException('failed');
            });
        } catch (RuntimeException) {
        }

        self::assertSame('outer', $store->get('stage'));
    }

    #[Test]
    public function setReplacesPreviousContent(): void
    {
        $store = new BlockStore();
        $store->set('stage', 'first');
        $store->set('stage', 'second');

        self::assertTrue($store->has('stage'));
        self::assertSame('second', $store->get('stage'));
    }

    #[Test]
    public function pushAppendsInCallOrder(): void
    {
        $store = new BlockStore();
        $store->push('scripts', '<a>');
        $store->push('scripts', '<b>');

        self::assertSame('<a><b>', $store->get('scripts'));
    }

    #[Test]
    public function blockThatWasNeverSetIsMissingAndEmpty(): void
    {
        $store = new BlockStore();

        self::assertFalse($store->has('nothing'));
        self::assertSame('', $store->get('nothing'));
    }

    #[Test]
    public function placeholderIsAnHtmlCommentWithTheBlockName(): void
    {
        $store = new BlockStore();

        self::assertSame('<!--fluid-blocks:stage-->', $store->placeholder('stage'));
        self::assertSame(1, preg_match(BlockStore::PLACEHOLDER_PATTERN, $store->placeholder('stage')));
    }

    #[Test]
    public function tracksEmittedPlaceholderNamesOnceEach(): void
    {
        $store = new BlockStore();
        $store->placeholder('stage');
        $store->placeholder('scripts');
        $store->placeholder('stage');

        self::assertSame(['stage', 'scripts'], $store->emittedPlaceholders());
    }

    #[Test]
    public function numericNamesStayStringsInEmittedPlaceholders(): void
    {
        $store = new BlockStore();
        $store->placeholder('2024');

        self::assertSame(['2024'], $store->emittedPlaceholders());
    }

    #[Test]
    public function remembersFallbackPerBlock(): void
    {
        $store = new BlockStore();
        $store->rememberFallback('stage', 'default stage');

        self::assertSame('default stage', $store->fallback('stage'));
        self::assertSame('', $store->fallback('other'));
    }

    #[Test]
    public function resetForgetsEverything(): void
    {
        $store = new BlockStore();
        $store->set('stage', 'x');
        $store->rememberFallback('stage', 'y');
        $store->placeholder('stage');

        $store->reset();

        self::assertFalse($store->has('stage'));
        self::assertSame('', $store->fallback('stage'));
        self::assertSame([], $store->emittedPlaceholders());
    }

    #[Test]
    public function rejectsNamesWithCharactersThatAreNotAllowed(): void
    {
        $store = new BlockStore();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('my block');
        $store->set('my block', 'x');
    }

    #[Test]
    public function acceptsDotsDashesAndUnderscoresInNames(): void
    {
        $store = new BlockStore();
        $store->set('meta.og-title_v2', 'x');

        self::assertTrue($store->has('meta.og-title_v2'));
    }
}
