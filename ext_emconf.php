<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Fluid Blocks',
    'description' => 'Send HTML from content element and plugin templates to named slots in the page template, in any render order',
    'category' => 'fe',
    'author' => 'Wolfgang Klinger',
    'author_email' => 'wolfgang@wazum.com',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
        ],
    ],
];
