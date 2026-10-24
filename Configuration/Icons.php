<?php

declare(strict_types=1);

$allIcons = 'EXT:mai_base/Resources/Public/Icons/all-icons/';

return [
    'ext-maispace-mai_survey' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:mai_survey/Resources/Public/Icons/Extension.svg',
    ],
    'mai-survey' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'interface-file-clipboard-text--edition-form-task-checklist-edit-clipboard--Streamline-Core.svg',
    ],
    'mai-survey-module' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'interface-file-clipboard-check--checkmark-edit-task-edition-checklist-check-success-clipboard-form--Streamline-Core.svg',
    ],
    'mai-survey-question' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'interface-help-question-circle--circle-faq-frame-help-info-mark-more-query-question--Streamline-Core.svg',
    ],
    'mai-survey-submission' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'interface-file-clipboard-check--checkmark-edit-task-edition-checklist-check-success-clipboard-form--Streamline-Core.svg',
    ],
    'mai-survey-answer' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => $allIcons . 'Interface-file-text--text-common-file--Streamline-Core.svg',
    ],
];
