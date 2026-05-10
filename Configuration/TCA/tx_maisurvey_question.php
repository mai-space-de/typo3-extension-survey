<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\CheckboxConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\JsonConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\NumberConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\RadioConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_survey', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maisurvey_question')))
    ->setSearchFields('question,type,options')
    ->setDefaultConfig()
    ->setLabel('question')
    ->setIconFile('EXT:mai_survey/Resources/Public/Icons/Extension.svg')
    ->setSortingField()
    ->addColumn(
        'survey',
        $lang('field.survey'),
        (new SelectSingleConfig())
            ->setForeignTable('tx_maisurvey_survey')
            ->setForeignTableWhere('ORDER BY tx_maisurvey_survey.title')
            ->setMinMaxItems(0, 1)
    )
    ->addColumn(
        'question',
        $lang('field.question'),
        (new InputConfig())->setRequired()->setEval('trim')
    )
    ->addColumn(
        'type',
        $lang('field.type'),
        (new RadioConfig())
            ->setItems([
                ['label' => $lang('question_type.single'), 'value' => 'single'],
                ['label' => $lang('question_type.multi'), 'value' => 'multi'],
                ['label' => $lang('question_type.text'), 'value' => 'text'],
                ['label' => $lang('question_type.scale'), 'value' => 'scale'],
                ['label' => $lang('question_type.date'), 'value' => 'date'],
            ])
            ->setDefault('single')
    )
    ->addColumn('required', $lang('field.required'), new CheckboxConfig())
    ->addColumn('options', $lang('field.options'), (new JsonConfig())->setReadOnly(false))
    ->addColumn('scale_min', $lang('field.scale_min'), (new NumberConfig())->setRange(0, 999)->setDefault(1))
    ->addColumn('scale_max', $lang('field.scale_max'), (new NumberConfig())->setRange(0, 999)->setDefault(5))
    ->addTypeShowItem(
        '0',
        'survey, question, type, required, options, scale_min, scale_max,
        --div--;' . $lang('tab.language') . ', --palette--;;language,
        --div--;' . $lang('tab.access') . ', --palette--;;hidden, --palette--;;access'
    )
    ->getConfig();
