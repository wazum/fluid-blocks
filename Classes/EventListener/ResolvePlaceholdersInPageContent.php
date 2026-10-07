<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;
use Wazum\FluidBlocks\BlockStore;
use Wazum\FluidBlocks\PlaceholderResolver;

/**
 * @internal
 */
#[AsEventListener]
final readonly class ResolvePlaceholdersInPageContent
{
    public function __construct(
        private BlockStore $store,
        private PlaceholderResolver $resolver,
    ) {
    }

    public function __invoke(AfterCacheableContentIsGeneratedEvent $event): void
    {
        if ($this->store->emittedPlaceholders() !== []) {
            $this->replaceContent($event);
        }
        $this->store->reset();
    }

    private function replaceContent(AfterCacheableContentIsGeneratedEvent $event): void
    {
        // @phpstan-ignore function.alreadyNarrowedType (TYPO3 14 has setContent(), TYPO3 13 does not)
        if (method_exists($event, 'setContent')) {
            $event->setContent($this->resolver->resolve($event->getContent()));

            return;
        }

        // @phpstan-ignore-next-line (TYPO3 13 only)
        $event->getController()->content = $this->resolver->resolve((string)$event->getController()->content);
    }
}
