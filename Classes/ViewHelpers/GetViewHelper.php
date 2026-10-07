<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Wazum\FluidBlocks\BlockStore;

final class GetViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'Block name', true);
        $this->registerArgument('default', 'string', 'Printed if the block is not written and the tag has no children', false, '');
    }

    public function render(): string
    {
        $store = GeneralUtility::makeInstance(BlockStore::class);
        $name = (string)$this->arguments['name'];
        if ($store->has($name)) {
            return $store->get($name);
        }

        $fallback = (string)($this->renderChildren() ?? $this->arguments['default']);
        if (!$this->isFrontendRequest()) {
            return $fallback;
        }

        $store->rememberFallback($name, $fallback);

        return $store->placeholder($name);
    }

    private function isFrontendRequest(): bool
    {
        if ($this->renderingContext === null || !$this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
            return false;
        }
        $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);

        return $request->getAttribute('applicationType') !== null
            && ApplicationType::fromRequest($request)->isFrontend();
    }
}
