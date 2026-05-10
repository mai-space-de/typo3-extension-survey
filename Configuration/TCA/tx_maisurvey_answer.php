<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\TextConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_survey', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maisurvey_answer')))
    ->setSearchFields('value')
    ->setDefaultConfig()
    ->setLabel('value')
    ->setIconFile('EXT:mai_survey/Resources/Public/Icons/Extension.svg')
    ->setSortingField()
    ->addColumn(
        'submission',
        $lang('field.submission'),
        (new SelectSingleConfig())
            ->setForeignTable('tx_maisurvey_submission')
            ->setForeignTableWhere('ORDER BY tx_maisurvey_submission.uid DESC')
            ->setMinMaxItems(0, 1)
    )
    ->addColumn(
        'question',
        $lang('field.question'),
        (new SelectSingleConfig())
            ->setForeignTable('tx_maisurvey_question')
            ->setForeignTableWhere('ORDER BY tx_maisurvey_question.question')
            ->setMinMaxItems(0, 1)
    )
    ->addColumn('value', $lang('field.value'), (new TextConfig())->setRows(5))
    ->addTypeShowItem(
        '0',
        'submission, question, value,
        --div--;' . $lang('tab.language') . ', --palette--;;language,
        --div--;' . $lang('tab.access') . ', --palette--;;hidden, --palette--;;access'
    )
    ->getConfig();
