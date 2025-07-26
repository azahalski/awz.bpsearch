<?php
namespace Awz\BpSearch;

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;

Loc::loadLanguageFile(__FILE__);

class BpIndexTable extends ORM\Data\DataManager
{
    use ORM\Data\Internal\DeleteByFilterTrait;
    use Traits\Serialize;

    public static $names = [];

    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
        return 'b_awz_bpsearch_bpindex';

        //ALTER TABLE b_awz_bpsearch_bpindex ADD ACT_ID varchar(255);
        //ALTER TABLE b_awz_bpsearch_bpindex ADD CRM_MODULE varchar(255);
        //ALTER TABLE b_awz_bpsearch_bpindex ADD CRM_DOCUMENT varchar(255);
    }

    public static function getMap()
    {
        return [
            (new ORM\Fields\IntegerField('ID'))
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_BPINDEX_ENTITY_ID'))
                ->configureAutocomplete()->configurePrimary(true),
            (new ORM\Fields\StringField('APP_ID'))->configureRequired(true)
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_NAMES_ENTITY_APP_ID')),
            (new ORM\Fields\StringField('MEMBER_ID'))->configureRequired(true)
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_NAMES_ENTITY_MEMBER_ID')),
            (new ORM\Fields\IntegerField('BPID'))->configureRequired(true)
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_BPINDEX_ENTITY_BPID')),
            (new ORM\Fields\StringField('CRM_MODULE'))->configureRequired(true)
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_BPINDEX_ENTITY_CRM_MODULE')),
            (new ORM\Fields\StringField('CRM_TYPE'))->configureRequired(true)
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_BPINDEX_ENTITY_CRM_TYPE')),
            (new ORM\Fields\StringField('CRM_DOCUMENT'))->configureRequired(true)
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_BPINDEX_ENTITY_CRM_DOCUMENT')),
            (new ORM\Fields\StringField('ACT_NAME'))->configureRequired(true)
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_BPINDEX_ENTITY_ACT_NAME')),
            (new ORM\Fields\StringField('ACT_ID'))->configureRequired(true)
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_BPINDEX_ENTITY_ACT_ID')),
            (new ORM\Fields\StringField('PARAMS'))
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_BPINDEX_ENTITY_PARAMS'))
                ->addSaveDataModifier(function ($value){
                    return self::serialize($value);
                })->addFetchDataModifier(function ($value) {
                    return self::unserialize($value);
                }),
            (new ORM\Fields\DatetimeField('DATE_UP'))->configureRequired(true)
                ->configureTitle(Loc::getMessage('AWZ_BPSEARCH_BPINDEX_ENTITY_DATE_UP')),
            (new ORM\Fields\Relations\Reference(
                "BP",
                '\Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable',
                ['=this.BPID' => 'ref.ID']
            ))
        ];
    }

    public static function reIndexAll(string $appId = "default", string $memberId = "default")
    {
        Option::set(Helper::MODULE_ID, 'last_timestamp_index', 0, "");
        Option::set(Helper::MODULE_ID, 'last_id_index', 0, "");
        $connection = Application::getConnection();
        $connection->truncateTable(NamesTable::getTableName());
        $connection->truncateTable(self::getTableName());
        self::reIndex($appId, $memberId);
    }

    public static function reIndex(string $appId = "default", string $memberId = "default")
    {
        $lastTimestamp = (int) Option::get(Helper::MODULE_ID, 'last_timestamp_index', "0", "");
        $lastId = (int) Option::get(Helper::MODULE_ID, 'last_id_index', "0", "");
        //print_r($lastTimestamp);
        $filter = [
            //>ID'=>$lastId,
            '>=MODIFIED'=>DateTime::createFromTimestamp($lastTimestamp)
        ];
        $r = WorkflowTemplateTable::getList([
            'order'=>['MODIFIED'=>'asc','ID'=>'asc'],
            'select'=>['ID','MODIFIED'],
            'filter'=>$filter
        ]);
        while($data = $r->fetch()){
            self::reIndexBpId($data['ID'], $appId, $memberId);
            Option::set(Helper::MODULE_ID, 'last_timestamp_index', strtotime($data['MODIFIED']), "");
            Option::set(Helper::MODULE_ID, 'last_id_index', $data['ID'], "");
        }
    }

    public static function checkDefTemplate(array $data = []){
        static $defIb;
        if(!$defIb){
            $defIb = [];
            if(Loader::includeModule('iblock')){
                $ibs = \Bitrix\Iblock\IblockTable::getList([
                    'select'=>['ID'],
                    'filter'=>['IBLOCK_TYPE_ID'=>'bitrix_processes'
                    ]])->fetchAll();
                foreach($ibs as $ib){
                    $defIb[] = 'iblock_'.$ib['ID'];
                }
            }
        }
        if($data['MODULE_ID']=='lists'){
            return in_array($data['DOCUMENT_TYPE'], $defIb);
        }

        return false;
    }

    public static function reIndexBpId(int $id)
    {
        self::deleteByFilter(['BPID'=>$id]);
        $data = WorkflowTemplateTable::getById($id)->fetch();
        if($data){
            if(self::checkDefTemplate($data))
                return;
            self::addActivities(
                (int)$data['ID'],
                $data['MODULE_ID'], $data['ENTITY'], $data['DOCUMENT_TYPE'],
                $data['TEMPLATE']
            );
        }
    }

    public static function addActivities(int $id, string $entityModule, string $entityId, string $entityDocument, array $templateData=[], string $appId = "default", string $memberId = "default"){
        if(is_array($templateData)){
            foreach($templateData as $v){
                if(!isset($v['Type'])) continue;
                if(!isset($v['Name'])) continue;
                //echo'<pre>';print_r($v);echo'</pre>';
                if(is_array($v) && $v['Type']){
                    if(isset($v['Properties']['Title']) && $v['Properties']['Title']){
                        if(!isset(self::$names[$v['Type']])){
                            self::$names[$v['Type']] = [];
                        }
                        if(!in_array($v['Properties']['Title'],self::$names[$v['Type']])){
                            NamesTable::add([
                                'APP_ID'=>$appId,
                                'MEMBER_ID'=>$memberId,
                                'ACT_NAME'=>$v['Type'],
                                'ACT_TITLE'=>$v['Properties']['Title']
                            ]);
                            self::$names[$v['Type']][] = $v['Properties']['Title'];
                        }
                    }
                    $r = self::add([
                        'APP_ID'=>$appId,
                        'MEMBER_ID'=>$memberId,
                        'BPID'=>$id,
                        'CRM_MODULE'=>$entityModule,
                        'CRM_TYPE'=>$entityId,
                        'CRM_DOCUMENT'=>$entityDocument,
                        'ACT_ID'=>$v['Name'],
                        'ACT_NAME'=>$v['Type'],
                        'PARAMS'=>$v['Properties'],
                        'DATE_UP'=>DateTime::createFromTimestamp(time())
                    ]);
                }
                if(is_array($v['Children'])){
                    self::addActivities($id, $entityModule, $entityId, $entityDocument, $v['Children'], $appId, $memberId);
                }
            }
        }
    }

}