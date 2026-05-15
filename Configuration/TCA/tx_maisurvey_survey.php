<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\CheckboxConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\TextConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_survey', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maisurvey_survey')))
    ->setSearchFields('title,description')
    ->setDefaultConfig()
    ->setLabel('title')
    ->setIconFile('EXT:mai_base/Resources/Public/Icons/generic_table.svg')
    ->setSortingField()
    ->addColumn('title', $lang('field.title'), (new InputConfig())->setRequired())
    ->addColumn('description', $lang('field.description'), new TextConfig())
    ->addColumn('is_active', $lang('field.is_active'), new CheckboxConfig())
    ->addColumn('allow_anonymous', $lang('field.allow_anonymous'), new CheckboxConfig())
    ->addColumn('prevent_duplicates', $lang('field.prevent_duplicates'), new CheckboxConfig())
    ->addColumn('success_message', $lang('field.success_message'), new TextConfig())
    ->addTypeShowItem('0', 'title, description, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, is_active, allow_anonymous, prevent_duplicates, success_message')
    ->getConfig();
