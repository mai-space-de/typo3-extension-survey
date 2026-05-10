<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiSurvey\Controller\SurveyController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::configurePlugin(
    'MaiSurvey',
    'Survey',
    [SurveyController::class => 'list,show,step,submit,confirmation'],
    [SurveyController::class => 'step,submit'],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
