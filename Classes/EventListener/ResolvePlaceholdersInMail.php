<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Mail\Event\BeforeMailerSentMessageEvent;
use TYPO3\CMS\Core\Mail\FluidEmail;
use Wazum\FluidBlocks\BlockStore;
use Wazum\FluidBlocks\PlaceholderResolver;

/**
 * @internal
 */
#[AsEventListener]
final readonly class ResolvePlaceholdersInMail
{
    public function __construct(
        private BlockStore $store,
        private PlaceholderResolver $resolver,
    ) {
    }

    public function __invoke(BeforeMailerSentMessageEvent $event): void
    {
        $mail = $event->getMessage();
        if (!$mail instanceof FluidEmail) {
            return;
        }

        $this->store->isolated(function () use ($mail): void {
            $html = $mail->getHtmlBody();
            $text = $mail->getTextBody();
            if ($this->store->emittedPlaceholders() === []) {
                return;
            }
            if (is_string($html)) {
                $mail->html($this->resolver->resolve($html));
            }
            if (is_string($text)) {
                $mail->text($this->resolver->resolve($text));
            }
        });
    }
}
