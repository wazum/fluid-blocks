<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\Tests\Unit\EventListener;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;
use Wazum\FluidBlocks\BlockStore;
use Wazum\FluidBlocks\EventListener\ResolvePlaceholdersInPageContent;
use Wazum\FluidBlocks\PlaceholderResolver;

final class ResolvePlaceholdersInPageContentTest extends TestCase
{
    #[Test]
    public function replacesPlaceholdersInThePageContentAndResetsTheStore(): void
    {
        $store = new BlockStore();
        $store->set('stage', '<h1>Hero</h1>');
        $event = $this->eventWithContent('<header>' . $store->placeholder('stage') . '</header>');

        (new ResolvePlaceholdersInPageContent($store, new PlaceholderResolver($store, new NullLogger())))($event);

        self::assertSame('<header><h1>Hero</h1></header>', $this->contentOf($event));
        self::assertFalse($store->has('stage'));
    }

    #[Test]
    public function leavesContentUntouchedWhenNoPlaceholderWasEmittedAndResetsTheStore(): void
    {
        $store = new BlockStore();
        $store->set('stage', 'unused');
        $event = $this->eventWithContent('<p>untouched <!--fluid-blocks:stage--></p>');

        (new ResolvePlaceholdersInPageContent($store, new PlaceholderResolver($store, new NullLogger())))($event);

        self::assertSame('<p>untouched <!--fluid-blocks:stage--></p>', $this->contentOf($event));
        self::assertFalse($store->has('stage'));
    }

    private function eventWithContent(string $content): AfterCacheableContentIsGeneratedEvent
    {
        $request = new ServerRequest('https://example.com/');
        if (method_exists(AfterCacheableContentIsGeneratedEvent::class, 'getContent')) {
            return new AfterCacheableContentIsGeneratedEvent($request, $content, 'cache-identifier', true);
        }
        $controller = $this->createStub(TypoScriptFrontendController::class);
        $controller->content = $content;

        return new AfterCacheableContentIsGeneratedEvent($request, $controller, 'cache-identifier', true);
    }

    private function contentOf(AfterCacheableContentIsGeneratedEvent $event): string
    {
        if (method_exists($event, 'getContent')) {
            return $event->getContent();
        }

        return $event->getController()->content;
    }
}
