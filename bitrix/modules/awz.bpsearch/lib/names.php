<?php
namespace Awz\BpSearch;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;

class NamesTable extends ORM\Data\DataManager
{
    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
        return 'b_awz_bpsearch_names';
    }

    public static function getMap()
    {
        return [
            (new ORM\Fields\IntegerField('ID'))
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_NAMES_ENTITY_ID'))
                ->configureAutocomplete()->configurePrimary(true),
            (new ORM\Fields\StringField('APP_ID'))->configureRequired(true)
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_NAMES_ENTITY_APP_ID')),
            (new ORM\Fields\StringField('MEMBER_ID'))->configureRequired(true)
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_NAMES_ENTITY_MEMBER_ID')),
            (new ORM\Fields\StringField('ACT_NAME'))->configureRequired(true)
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_NAMES_ENTITY_ACT_NAME')),
            (new ORM\Fields\IntegerField('ACT_TITLE'))->configureRequired(true)
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_NAMES_ENTITY_ACT_TITLE')),
        ];
    }
}