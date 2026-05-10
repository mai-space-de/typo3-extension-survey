<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\DatetimeConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\NumberConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_survey', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maisurvey_submission')))
    ->setSearchFields('session_hash,fe_user_uid')
    ->setDefaultConfig()
    ->setLabel('session_hash')
    ->setIconFile('EXT:mai_survey/Resources/Public/Icons/Extension.svg')
    ->setDefaultSorting('ORDER BY submitted_at DESC')
    ->addColumn(
        'survey',
        $lang('field.survey'),
        (new SelectSingleConfig())
            ->setForeignTable('tx_maisurvey_survey')
            ->setForeignTableWhere('ORDER BY tx_maisurvey_survey.title')
            ->setMinMaxItems(0, 1)
    )
    ->addColumn('session_hash', $lang('field.session_hash'), (new InputConfig())->setEval('trim'))
    ->addColumn('fe_user_uid', $lang('field.fe_user_uid'), (new NumberConfig())->setRange(0, 99999999))
    ->addColumn('submitted_at', $lang('field.submitted_at'), (new DatetimeConfig())->setFormat('datetime')->setReadOnly())
    ->addTypeShowItem(
        '0',
        'survey, session_hash, fe_user_uid, submitted_at,
        --div--;' . $lang('tab.language') . ', --palette--;;language,
        --div--;' . $lang('tab.access') . ', --palette--;;hidden, --palette--;;access'
    )
    ->getConfig();
