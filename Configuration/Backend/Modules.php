<?php

declare(strict_types=1);

use Maispace\MaiSurvey\Controller\Backend\SurveyResultsController;

return [
    'maispace_survey_results' => [
        'parent' => 'web',
        'access' => 'user',
        'iconIdentifier' => 'ext-maispace-mai_survey',
        'labels' => 'LLL:EXT:mai_survey/Resources/Private/Language/Default/locallang_module.xlf',
        'extensionName' => 'MaiSurvey',
        'controllerActions' => [
            SurveyResultsController::class => ['list', 'show', 'export'],
        ],
    ],
];
