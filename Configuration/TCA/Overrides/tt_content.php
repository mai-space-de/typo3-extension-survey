<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiBase\TableConfigurationArray\CType;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

$lang = Helper::localLangHelperFactory('mai_survey', 'Default/locallang_tca.xlf');

ExtensionUtility::registerPlugin(
    'MaiSurvey',
    'Survey',
    $lang('plugin.survey.title'),
    'ext-maispace-mai_survey',
    'maispace_feature'
);

(new CType('maispace_survey_survey', $lang('ctype.survey'), 'ext-maispace-mai_survey'))
    ->addDefaultHeaderPalette()
    ->addCustomFields('pi_flexform')
    ->addDefaultLanguageTab()
    ->addDefaultAccessTab()
    ->setGroup('maispace_feature')
    ->register();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:mai_survey/Configuration/FlexForms/SurveyPlugin.xml',
    'maispace_survey_survey'
);
