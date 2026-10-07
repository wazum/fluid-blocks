<?php

declare(strict_types=1);

namespace Wazum\FluidBlocksE2e;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Attribute\AsAllowedCallable;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;

final class PageNotFound
{
    #[AsAllowedCallable]
    public function render(string $content, array $configuration, ServerRequestInterface $request): string
    {
        throw new ImmediateResponseException(GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction($request, 'Page not found'), 1759820001);
    }
}
