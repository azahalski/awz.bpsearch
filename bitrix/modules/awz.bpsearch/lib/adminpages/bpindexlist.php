<?php

namespace Awz\BpSearch\AdminPages;

use Awz\BpSearch\Access\AccessController;
use Awz\BpSearch\Access\Custom\ActionDictionary;
use Bitrix\Main\Localization\Loc;
use Awz\Admin\IList;
use Awz\Admin\IParams;
use Awz\Admin\Helper;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class BpIndexList extends IList implements IParams {

    public function __construct($params){
        global $POST_RIGHT;
        if(AccessController::can(0, ActionDictionary::ACTION_SBP_VIEW)){
            $POST_RIGHT = "W";
            \Awz\BpSearch\BpIndexTable::reIndex();
        }
        parent::__construct($params);
    }

    public function getAdminResult(){

        static $adminResult;
        if(!$adminResult){
            /* @var $userQuery \Bitrix\Main\ORM\Query\Query */
            $userQuery = $this->getUserQuery();
            $sel = $userQuery->getSelect();
            $sel[] = 'BPID';
            $sel[] = 'BP.TYPE';
            if(!in_array('CRM_DOCUMENT', $sel))
                $sel[] = 'CRM_DOCUMENT';
            if(!in_array('CRM_MODULE', $sel))
                $sel[] = 'CRM_MODULE';
            $userQuery->setSelect($sel);
            $result = $userQuery->exec();
            $totalCountRequest = $this->getAdminList()->isTotalCountRequest();
            if ($totalCountRequest)
            {
                $this->getAdminList()->sendTotalCountResponse($result->getCount());
            }
            $adminResult = $result;
        }

        return $adminResult;

    }

    public function trigerGetRowListAdmin($row){
        $link = '';
        if($row->arRes['BPID'] && $row->arRes['CRM_MODULE']=='crm'){
            ///crm/type/31/automation/2/
            if($row->arRes['AWZ_BPSEARCH_BP_INDEX_BP_TYPE']=='robots'){
                $typeId = mb_strtolower($row->arRes['CRM_DOCUMENT']);
                if($row->arRes['CRM_DOCUMENT'] == 'SMART_INVOICE') $typeId = 'type/31';
                $typeId = str_replace('dynamic_','type/',$typeId);
                $link = '/crm/'.$typeId.'/automation/0/';
            }elseif($row->arRes['AWZ_BPSEARCH_BP_INDEX_BP_TYPE']=='bitrix_bizproc_automation'){
                $typeId = 'CRM_'.mb_strtoupper($row->arRes['CRM_DOCUMENT']);
                $link = '/crm/configs/bp/'.$typeId.'/edit/'.$row->arRes['BPID'].'/';
            }elseif($row->arRes['AWZ_BPSEARCH_BP_INDEX_BP_TYPE']=='custom_robots'){
                $typeId = 'CRM_'.mb_strtoupper($row->arRes['CRM_DOCUMENT']);
                $link = '/crm/configs/bp/'.$typeId.'/edit/'.$row->arRes['BPID'].'/';
            }elseif($row->arRes['AWZ_BPSEARCH_BP_INDEX_BP_TYPE']=='default'){
                $typeId = 'CRM_'.mb_strtoupper($row->arRes['CRM_DOCUMENT']);
                $link = '/crm/configs/bp/'.$typeId.'/edit/'.$row->arRes['BPID'].'/';
            }

        }elseif($row->arRes['BPID'] && $row->arRes['CRM_MODULE']=='lists'){
            $link = '/services/lists/'.str_replace('iblock_','',$row->arRes['CRM_DOCUMENT']).'/bp_edit/'.$row->arRes['BPID'].'/';
        }
        if($link){
            $row->AddViewField('BP.NAME','<a href="'.$link.'" target="_blank">'.$row->arRes['AWZ_BPSEARCH_BP_INDEX_BP_NAME'].'</a>');
            $row->AddViewField('ACT_ID','<a href="'.$link.'#'.$row->arRes['ACT_ID'].'" target="_blank">'.$row->arRes['ACT_ID'].'</a>');
        }else{
            $row->AddViewField('BP.NAME',$row->arRes['AWZ_BPSEARCH_BP_INDEX_BP_NAME']);
            $row->AddViewField('ACT_ID',$row->arRes['ACT_ID']);
        }
        $paramsHtml = [];
        foreach($row->arRes['PARAMS'] as $k=>$v){
            $paramsHtml[] = $k.': '.print_r($v, true);
        }
        $row->AddViewField('PARAMS',implode("<br>",$paramsHtml));
    }

    public function trigerInitFilter(){
        $filter = [];
        foreach($this->filter as $code=>$value){
            $filter[str_replace('__', '.', $code)] = $value;
        }
        $this->filter = $filter;
    }

    public function trigerGetRowListActions(array $actions): array
    {
        return $actions;
    }

    public static function getTitle(): string
    {
        return Loc::getMessage('AWZ_BPSEARCH_BPINDEX_LIST_TITLE');
    }

    public static function getParams(): array
    {
        $arParams = [
            "ENTITY" => "\\Awz\\BpSearch\\BpIndexTable",
            "BUTTON_CONTEXTS"=> [],
            "ADD_GROUP_ACTIONS"=> [],
            "ADD_LIST_ACTIONS"=> [],
            "FIND"=> [
                "CRM_MODULE"=>[
                    "type"=>"list","id"=>"CRM_MODULE","realId"=>"CRM_MODULE",
                    "name"=>Loc::getMessage('AWZ_BPSEARCH_BPINDEX_ENTITY_CRM_MODULE'),
                    "items"=>["lists"=>"lists","crm"=>"crm"],
                    "params"=>["multiple"=>"Y"]
                ],
                "PARAMS"=>[
                    "type"=>"string","id"=>"PARAMS",
                    "name"=>Loc::getMessage('AWZ_BPSEARCH_BPINDEX_ENTITY_PARAMS'),
                    'filterable'=>"%"
                ],
            ],
            "FIND_FROM_ENTITY"=> [
                "BPID"=>[],
                //"CRM_MODULE",
                //"CRM_TYPE",
                //"CRM_DOCUMENT",
                "ACT_NAME"=>[],
                "ACT_ID"=>[],
                "DATE_UP"=>[]
            ],
            //"SQL_COLS"=>["*"],
            "COLS"=>[
                "BPID","CRM_MODULE","CRM_TYPE","CRM_DOCUMENT","ACT_NAME","ACT_ID","DATE_UP","PARAMS",
                //"BP.NAME"=>['id'=>'BP.NAME','content'=>'Название БП','sort'=>'BP.NAME','default'=>true],
                //"BP.ACTIVE","BP.USER","BP.DESCRIPTION",
            ],
        ];

        if(Loader::includeModule("bizproc")){
            $arParams['FIND']['BP.NAME'] = [
                "type"=>"string","id"=>"BP__NAME",
                "name"=>Loc::getMessage('AWZ_BPSEARCH_BPINDEX_LIST_BP_NAME'),
                'filterable'=>"%"
            ];
            $arParams['COLS']['NAME'] = [
                'id'=>'BP.NAME',
                'content'=>Loc::getMessage('AWZ_BPSEARCH_BPINDEX_LIST_BP_NAME'),
                'sort'=>'BP.NAME',
                'default'=>true
            ];
        }

        return $arParams;
    }
}