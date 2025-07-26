<?php
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\EventManager,
    Bitrix\Main\ModuleManager,
	Bitrix\Main\Config\Option,
    Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

class awz_bpsearch extends CModule
{
	var $MODULE_ID = "awz.bpsearch";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;

    public function __construct()
	{
        $arModuleVersion = array();
        include(__DIR__.'/version.php');

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = Loc::getMessage("AWZ_BPSEARCH_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("AWZ_BPSEARCH_MODULE_DESCRIPTION");
		$this->PARTNER_NAME = Loc::getMessage("AWZ_PARTNER_NAME");
		$this->PARTNER_URI = "https://zahalski.dev/";

		return true;
	}

    function DoInstall()
    {
        global $APPLICATION, $step;

        $this->InstallFiles();
        $this->InstallDB();
        $this->checkOldInstallTables();
        $this->InstallEvents();
        $this->createAgents();

        ModuleManager::RegisterModule($this->MODULE_ID);

        Option::set($this->MODULE_ID, 'last_timestamp_index', 0, "");
        Option::set($this->MODULE_ID, 'last_id_index', 0, "");

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("AWZ_BPSEARCH_MODULE_NAME"),
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'. $this->MODULE_ID .'/install/solution.php'
        );

        return true;
    }

    function DoUninstall()
    {
        global $APPLICATION, $step;

        $step = intval($step);
        if($step < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('AWZ_BPSEARCH_INSTALL_TITLE'),
                $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'. $this->MODULE_ID .'/install/unstep.php'
            );
        }
        elseif($step == 2) {
            if($_REQUEST['save'] != 'Y' && !isset($_REQUEST['save'])) {
                $this->UnInstallDB();
            }
            $this->UnInstallFiles();
            $this->UnInstallEvents();
            $this->deleteAgents();

            if($_REQUEST['saveopts'] != 'Y' && !isset($_REQUEST['saveopts'])) {
                \Bitrix\Main\Config\Option::delete($this->MODULE_ID);
            }

            ModuleManager::UnRegisterModule($this->MODULE_ID);
            return true;
        }
		
    }

    function InstallDB()
    {
        global $DB, $DBType, $APPLICATION;
        $connection = \Bitrix\Main\Application::getConnection();
        $this->errors = false;
        if(!$this->errors && !$DB->TableExists(implode('_', explode('.',$this->MODULE_ID)).'_permission')) {
            $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/" . $this->MODULE_ID . "/install/db/".$connection->getType()."/access.sql");
        }
        if (!$this->errors) {
            return true;
        } else {
            $APPLICATION->ThrowException(implode("", $this->errors));
            return $this->errors;
        }
    }

    function UnInstallDB()
    {
        global $DB, $DBType, $APPLICATION;
        $connection = \Bitrix\Main\Application::getConnection();
        $this->errors = false;
        if (!$this->errors) {
            $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/" . $this->MODULE_ID . "/install/db/" . $connection->getType() . "/unaccess.sql");
        }
        if (!$this->errors) {
            return true;
        }
        else {
            $APPLICATION->ThrowException(implode("", $this->errors));
            return $this->errors;
        }
    }

    function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible(
            'main', 'OnAfterUserUpdate',
            $this->MODULE_ID, '\\Awz\\BpSearch\\Access\\Handlers', 'OnAfterUserUpdate'
        );
        $eventManager->registerEventHandlerCompatible(
            'main', 'OnAfterUserAdd',
            $this->MODULE_ID, '\\Awz\\BpSearch\\Access\\Handlers', 'OnAfterUserUpdate'
        );

        $eventManager->registerEventHandler(
            'bizproc', '\Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplate::onAfterUpdate',
            $this->MODULE_ID, '\\Bitrix\\BpSearch\\Handlers', 'onAfterUpdate'
        );
        $eventManager->registerEventHandler(
            'bizproc', '\Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplate::onAfterAdd',
            $this->MODULE_ID, '\\Bitrix\\BpSearch\\Handlers', 'onAfterAdd'
        );
        $eventManager->registerEventHandler(
            'bizproc', '\Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplate::onAfterDelete',
            $this->MODULE_ID, '\\Bitrix\\BpSearch\\Handlers', 'onAfterDelete'
        );

        return true;
    }

    function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'sale', 'OnAfterUserUpdate',
            $this->MODULE_ID, '\\Awz\\BpSearch\\Access\\Handlers', 'OnAfterUserUpdate'
        );
        $eventManager->unRegisterEventHandler(
            'sale', 'OnAfterUserAdd',
            $this->MODULE_ID, '\\Awz\\BpSearch\\Access\\Handlers', 'OnAfterUserUpdate'
        );
        $eventManager->unRegisterEventHandler(
            'bizproc', '\Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplate::onAfterUpdate',
            $this->MODULE_ID, '\\Bitrix\\BpSearch\\Handlers', 'onAfterUpdate'
        );
        $eventManager->unRegisterEventHandler(
            'bizproc', '\Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplate::onAfterAdd',
            $this->MODULE_ID, '\\Bitrix\\BpSearch\\Handlers', 'onAfterAdd'
        );
        $eventManager->unRegisterEventHandler(
            'bizproc', '\Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplate::onAfterDelete',
            $this->MODULE_ID, '\\Bitrix\\BpSearch\\Handlers', 'onAfterDelete'
        );
        return true;
    }

    function InstallFiles()
    {
        CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/admin/", $_SERVER['DOCUMENT_ROOT']."/bitrix/admin/", true);
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/components/bpsearch.config.permissions/",
            $_SERVER['DOCUMENT_ROOT']."/bitrix/components/awz/bpsearch.config.permissions",
            true, true
        );
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles(
            $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/admin",
            $_SERVER['DOCUMENT_ROOT']."/bitrix/admin"
        );
        DeleteDirFilesEx("/bitrix/components/awz/bpsearch.config.permissions");
        return true;
    }

    function createAgents() {
        return true;
    }

    function deleteAgents() {
        CAgent::RemoveModuleAgents($this->MODULE_ID);
        return true;
    }

    function checkOldInstallTables()
    {
        return true;
    }

}