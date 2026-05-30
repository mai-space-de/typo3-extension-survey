<?php

declare(strict_types=1);

return [
    \Maispace\MaiSurvey\Domain\Model\Survey::class => [
        'tableName' => 'tx_maisurvey_survey',
    ],
    \Maispace\MaiSurvey\Domain\Model\Question::class => [
        'tableName' => 'tx_maisurvey_question',
    ],
    \Maispace\MaiSurvey\Domain\Model\Submission::class => [
        'tableName' => 'tx_maisurvey_submission',
    ],
    \Maispace\MaiSurvey\Domain\Model\Answer::class => [
        'tableName' => 'tx_maisurvey_answer',
    ],
];
