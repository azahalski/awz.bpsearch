<?php
namespace Awz\Bpsearch\Access\Custom\Rules;

use Bitrix\Main\Access\AccessibleItem;
use Awz\Bpsearch\Access\Custom\PermissionDictionary;
use Awz\Bpsearch\Access\Custom\Helper;

class Sbpview extends \Bitrix\Main\Access\Rule\AbstractRule
{

    public function execute(AccessibleItem $item = null, $params = null): bool
    {
        if ($this->user->isAdmin() && !Helper::ADMIN_DECLINE)
        {
            return true;
        }
        if ($this->user->getPermission(PermissionDictionary::SBP_VIEW))
        {
            return true;
        }
        return false;
    }

}