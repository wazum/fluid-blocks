<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Wazum\FluidBlocks\BlockStore;

/**
 * @internal
 */
abstract class AbstractCaptureViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'Block name', true);
    }

    public function render(): string
    {
        $this->store(
            GeneralUtility::makeInstance(BlockStore::class),
            (string)$this->arguments['name'],
            (string)$this->renderChildren(),
        );

        return '';
    }

    abstract protected function store(BlockStore $store, string $name, string $content): void;
}
