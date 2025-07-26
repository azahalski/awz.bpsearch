<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Awz\BpSearch\Access\AccessController;
use Awz\BpSearch\Access\Custom\ActionDictionary;

global $APPLICATION;
$dirs = explode(DIRECTORY_SEPARATOR, dirname(__DIR__, 1));
$module_id = array_pop($dirs);
unset($dirs);
Loc::loadMessages(__FILE__);

if(!Loader::includeModule($module_id)) return;

if(!AccessController::can("", ActionDictionary::ACTION_SBP_VIEW))
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

if(file_exists(__DIR__. DIRECTORY_SEPARATOR.'check_awz_admin.php')){
    require_once('check_awz_admin.php');
}elseif(!Loader::includeModule('awz.admin')){
    return;
}

/* "Awz\BpSearch\AdminPages\BpIndexList" replace generator */
use Awz\BpSearch\AdminPages\BpIndexList as PageList;

$APPLICATION->SetTitle(PageList::getTitle());
$arParams = PageList::getParams();

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/awz.admin/include/handler.php");
/* @var bool $customPrint */
if(!$customPrint) {
    $adminCustom = new PageList($arParams);
    $adminCustom->defaultInterface();
}