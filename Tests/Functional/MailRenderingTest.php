<?php

declare(strict_types=1);

namespace Wazum\FluidBlocks\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Wazum\FluidBlocks\BlockStore;

final class MailRenderingTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['wazum/fluid-blocks'];

    protected array $configurationToUseInTestInstance = [
        'MAIL' => [
            'transport' => 'null',
        ],
    ];

    #[Test]
    public function mailSentDuringPageRenderingResolvesItsOwnSlotsAndKeepsThePageBlocks(): void
    {
        $store = $this->get(BlockStore::class);
        $store->push('scripts', 'page script');
        $store->placeholder('title');

        $mailer = $this->get(MailerInterface::class);
        $mailer->send($this->mailFromTemplate('Blocks'));
        $sentMail = (string)$mailer->getSentMessage()?->toString();

        self::assertStringContainsString('[stage:mail stage][scripts:]', $sentMail);
        self::assertStringNotContainsString('fluid-blocks:', $sentMail);
        self::assertSame('page script', $store->get('scripts'));
        self::assertSame(['title'], $store->emittedPlaceholders());
    }

    #[Test]
    public function plainTextPartOfTheMailIsResolvedToo(): void
    {
        $mailer = $this->get(MailerInterface::class);
        $mailer->send($this->mailFromTemplate('Blocks')->format(FluidEmail::FORMAT_BOTH));
        $sentMail = (string)$mailer->getSentMessage()?->toString();

        self::assertStringContainsString('[text stage:mail stage]', $sentMail);
        self::assertStringContainsString('[stage:mail stage]', $sentMail);
        self::assertStringNotContainsString('fluid-blocks:', $sentMail);
    }

    private function mailFromTemplate(string $templateName): FluidEmail
    {
        $templatePaths = new TemplatePaths();
        $templatePaths->setTemplateRootPaths([__DIR__ . '/Fixtures/Mail/Templates/']);
        $request = (new ServerRequest('https://example.com/'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);

        return (new FluidEmail($templatePaths))
            ->setRequest($request)
            ->setTemplate($templateName)
            ->format(FluidEmail::FORMAT_HTML)
            ->to('reader@example.com')
            ->from('site@example.com');
    }
}
