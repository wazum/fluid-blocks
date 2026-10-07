<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class PageRenderingTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['wazum/fluid-blocks'];

    #[Test]
    public function slotRenderedBeforeContentElementsReceivesTheirBlocks(): void
    {
        $body = (string)$this->executeFrontendSubRequest(new InternalRequest('https://example.com/'))->getBody();

        self::assertStringContainsString('[stage:Second]', $body);
        self::assertStringContainsString('[scripts:[1][2]]', $body);
        self::assertStringContainsString('[body:plain]', $body);
        self::assertStringNotContainsString('<!--fluid-blocks:', $body);
    }

    #[Test]
    public function cachedPageContainsResolvedContent(): void
    {
        $first = (string)$this->executeFrontendSubRequest(new InternalRequest('https://example.com/'))->getBody();
        $second = (string)$this->executeFrontendSubRequest(new InternalRequest('https://example.com/'))->getBody();

        self::assertSame($first, $second);
        self::assertStringContainsString('[stage:Second]', $second);
        self::assertStringNotContainsString('<!--fluid-blocks:', $second);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tt_content.csv');
        $this->get(SiteWriter::class)->write('test', [
            'rootPageId' => 1,
            'base' => 'https://example.com/',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'English',
                    'navigationTitle' => 'English',
                    'base' => '/',
                    'locale' => 'en_US.UTF-8',
                ],
            ],
        ]);
        $this->setUpFrontendRootPage(1, ['EXT:fluid_blocks/Tests/Functional/Fixtures/TypoScript/setup.typoscript']);
    }
}
