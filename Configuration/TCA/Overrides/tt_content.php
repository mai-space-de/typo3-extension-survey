<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiBase\TableConfigurationArray\Helper;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

$lang = Helper::localLangHelperFactory('mai_survey', 'Default/locallang_tca.xlf');

ExtensionUtility::registerPlugin(
    'MaiSurvey',
    'Survey',
    $lang('plugin.survey.title'),
    'mai-content',
    'maispace_plugins_interactive',
    '',
    'FILE:EXT:mai_survey/Configuration/FlexForms/SurveyPlugin.xml',
);
