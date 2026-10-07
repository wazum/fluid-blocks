<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\View\TemplateView;
use Wazum\FluidBlocks\BlockStore;

final class CaptureViewHelpersTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private BlockStore $store;

    #[Test]
    public function setStoresRenderedChildrenAndOutputsNothing(): void
    {
        $output = $this->render('<block:set name="stage"><h1>{title}</h1></block:set>', ['title' => 'Hero']);

        self::assertSame('', $output);
        self::assertSame('<h1>Hero</h1>', $this->store->get('stage'));
    }

    #[Test]
    public function setEscapesInterpolatedVariables(): void
    {
        $this->render('<block:set name="stage">{title}</block:set>', ['title' => '<b>x</b>']);

        self::assertSame('&lt;b&gt;x&lt;/b&gt;', $this->store->get('stage'));
    }

    #[Test]
    public function laterSetReplacesEarlierSet(): void
    {
        $this->render('<block:set name="stage">one</block:set><block:set name="stage">two</block:set>');

        self::assertSame('two', $this->store->get('stage'));
    }

    #[Test]
    public function setWithoutChildrenStoresAnEmptyBlock(): void
    {
        $this->render('<block:set name="stage" />');

        self::assertTrue($this->store->has('stage'));
        self::assertSame('', $this->store->get('stage'));
    }

    #[Test]
    public function pushAppendsInRenderOrderAndOutputsNothing(): void
    {
        $output = $this->render('<block:push name="scripts">[a]</block:push><block:push name="scripts">[b]</block:push>');

        self::assertSame('', $output);
        self::assertSame('[a][b]', $this->store->get('scripts'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = new BlockStore();
        GeneralUtility::setSingletonInstance(BlockStore::class, $this->store);
    }

    /**
     * @param array<string, mixed> $variables
     */
    private function render(string $source, array $variables = []): string
    {
        $context = new RenderingContext();
        $context->getViewHelperResolver()->addNamespace('block', 'Wazum\\FluidBlocks\\ViewHelpers');
        $context->getTemplatePaths()->setTemplateSource($source);
        $view = new TemplateView($context);
        $view->assignMultiple($variables);

        return (string)$view->render();
    }
}
