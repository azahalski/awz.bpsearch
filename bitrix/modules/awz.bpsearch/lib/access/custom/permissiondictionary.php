<?php
namespace Awz\Bpsearch\Access\Custom;

use Awz\Bpsearch\Access\Permission;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class PermissionDictionary
    extends Permission\PermissionDictionary
{
    /*awz.gen start - !!!nodelete*/
	public const MODULE_SETT_VIEW = "96";
	public const MODULE_SETT_EDIT = "97";
	public const MODULE_RIGHT_VIEW = "98";
	public const MODULE_RIGHT_EDIT = "99";
	public const SBP_VIEW = "5";
	/*awz.gen end - !!!nodelete*/
}