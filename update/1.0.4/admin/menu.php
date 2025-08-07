<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Awz\Bpsearch\Access\AccessController;
use Awz\Bpsearch\Access\Custom\ActionDictionary;
Loc::loadMessages(__FILE__);
$module_id = "awz.bpsearch";
if(!Loader::includeModule($module_id)) return;

$items = [];
if(AccessController::can(0, ActionDictionary::ACTION_SBP_VIEW)){
    $items[] = [
        "text" => Loc::getMessage('AWZ_BPSEARCH_MENU_NAME_SBPLIST'),
        "url" => "awz_bpsearch_bpindex_list.php?lang=".LANGUAGE_ID.'&mid='.$module_id.'&mid_menu=1'
    ];
}
if(AccessController::isViewSettings() || AccessController::isViewRight()){
    $level2 = [];
    if(AccessController::isViewSettings()){
        $level2[] = [
            "text" => Loc::getMessage('AWZ_BPSEARCH_MENU_NAME_SETT_1'),
            "url" => "settings.php?lang=".LANGUAGE_ID.'&mid='.$module_id.'&mid_menu=1'
        ];
    }
    if(AccessController::isViewRight()){
        $level2[] = [
            "text" => Loc::getMessage('AWZ_BPSEARCH_MENU_NAME_SETT_2'),
            "url" => "javascript:BX.SidePanel.Instance.open('/bitrix/admin/settings.php?mid=".$module_id."&lang=".LANGUAGE_ID."&mid_menu=1');"
        ];
    }
    $items[] = [
        "text" => Loc::getMessage('AWZ_BPSEARCH_MENU_NAME_SETT'),
        "items_id" => str_replace('.','_',$module_id).'_sett',
        "items"=>$level2
    ];
}

if(empty($items)) return;
$aMenu[] = array(
    "parent_menu" => "global_menu_services",
    "section" => 'awz_bps',
    "sort" => 100,
    "module_id" => $module_id,
    "text" => Loc::getMessage('AWZ_BPSEARCH_MENU_NAME'),
    "title" => Loc::getMessage('AWZ_BPSEARCH_MENU_NAME'),
    "items_id" => 'awz_bps',
    "items" => $items,
);
return $aMenu;