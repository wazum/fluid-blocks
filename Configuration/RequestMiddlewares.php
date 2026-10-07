<?php

declare(strict_types=1);

use Wazum\FluidBlocks\Middleware\IsolateBlocksPerRequest;

return [
    'frontend' => [
        'wazum/fluid-blocks/isolate-blocks-per-request' => [
            'target' => IsolateBlocksPerRequest::class,
            'before' => [
                'typo3/cms-frontend/timetracker',
            ],
        ],
    ],
];
