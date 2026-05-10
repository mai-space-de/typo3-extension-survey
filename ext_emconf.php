<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Mai Survey',
    'description' => 'Survey and Mitmach-Matrix extension for TYPO3',
    'category' => 'module',
    'author' => 'Maispace',
    'author_email' => '',
    'author_company' => 'Maispace',
    'state' => 'alpha',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
            'mai_base' => '',
        ],
        'conflicts' => [],
        'suggests' => [
            'mai_account' => '',
            'mai_mail' => '',
        ],
    ],
];
