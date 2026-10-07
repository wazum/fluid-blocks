<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\Tests\Unit\ViewHelpers;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\View\TemplateView;
use Wazum\FluidBlocks\BlockStore;

final class GetViewHelperTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private BlockStore $store;

    #[Test]
    public function returnsBlockWhenAlreadySet(): void
    {
        $this->store->set('stage', '<h1>Hero</h1>');

        self::assertSame('<h1>Hero</h1>', $this->render('<block:get name="stage">fallback</block:get>'));
    }

    #[Test]
    public function returnsAllPushedContent(): void
    {
        $this->store->push('scripts', '[a]');
        $this->store->push('scripts', '[b]');

        self::assertSame('[a][b]', $this->render('<block:get name="scripts" />'));
    }

    #[Test]
    public function returnsFallbackDirectlyWithoutRequest(): void
    {
        $output = $this->render('<block:get name="stage"><h1>{title}</h1></block:get>', ['title' => 'Default']);

        self::assertSame('<h1>Default</h1>', $output);
        self::assertSame([], $this->store->emittedPlaceholders());
    }

    #[Test]
    public function emitsPlaceholderAndRemembersFallbackInFrontendRequest(): void
    {
        $output = $this->render('<block:get name="stage">Default</block:get>', [], SystemEnvironmentBuilder::REQUESTTYPE_FE);

        self::assertSame('<!--fluid-blocks:stage-->', $output);
        self::assertSame('Default', $this->store->fallback('stage'));
        self::assertSame(['stage'], $this->store->emittedPlaceholders());
    }

    #[Test]
    public function returnsBlockDirectlyInFrontendRequestWhenAlreadySet(): void
    {
        $this->store->set('stage', 'set');

        self::assertSame('set', $this->render('<block:get name="stage">Default</block:get>', [], SystemEnvironmentBuilder::REQUESTTYPE_FE));
        self::assertSame([], $this->store->emittedPlaceholders());
    }

    #[Test]
    public function returnsFallbackDirectlyInBackendRequest(): void
    {
        $output = $this->render('<block:get name="stage">Default</block:get>', [], SystemEnvironmentBuilder::REQUESTTYPE_BE);

        self::assertSame('Default', $output);
        self::assertSame([], $this->store->emittedPlaceholders());
    }

    #[Test]
    public function rejectsInvalidNameAlsoWithoutRequest(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->render('<block:get name="my block">Default</block:get>');
    }

    #[Test]
    public function inlineFormUsesDefaultArgumentAsFallback(): void
    {
        $output = $this->render('<body class="{block:get(name: \'bodyClass\', default: \'page\')}">');

        self::assertSame('<body class="page">', $output);
    }

    #[Test]
    public function inlineFormRemembersDefaultArgumentAsFallbackInFrontendRequest(): void
    {
        $this->render('{block:get(name: \'bodyClass\', default: \'page\')}', [], SystemEnvironmentBuilder::REQUESTTYPE_FE);

        self::assertSame('page', $this->store->fallback('bodyClass'));
    }

    #[Test]
    public function tagWithoutChildrenUsesDefaultArgumentAsFallback(): void
    {
        self::assertSame('page', $this->render('<block:get name="bodyClass" default="page" />'));
    }

    #[Test]
    public function tagWithoutBlockChildrenAndDefaultReturnsEmptyString(): void
    {
        self::assertSame('', $this->render('<block:get name="scripts" />'));
    }

    #[Test]
    public function doesNotEscapeTheBlockAgain(): void
    {
        $this->store->set('stage', '&lt;already escaped&gt;');

        self::assertSame('&lt;already escaped&gt;', $this->render('<block:get name="stage" />'));
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
    private function render(string $source, array $variables = [], ?int $applicationType = null): string
    {
        $context = new RenderingContext();
        $context->getViewHelperResolver()->addNamespace('block', 'Wazum\\FluidBlocks\\ViewHelpers');
        $context->getTemplatePaths()->setTemplateSource($source);
        if ($applicationType !== null) {
            $request = (new ServerRequest('https://example.com/'))
                ->withAttribute('applicationType', $applicationType);
            $context->setAttribute(ServerRequestInterface::class, $request);
        }
        $view = new TemplateView($context);
        $view->assignMultiple($variables);

        return (string)$view->render();
    }
}
