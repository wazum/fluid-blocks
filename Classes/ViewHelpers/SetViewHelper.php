<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\ViewHelpers;

use Wazum\FluidBlocks\BlockStore;

final class SetViewHelper extends AbstractCaptureViewHelper
{
    protected function store(BlockStore $store, string $name, string $content): void
    {
        $store->set($name, $content);
    }
}
