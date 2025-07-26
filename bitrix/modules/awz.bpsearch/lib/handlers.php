<?php
namespace Awz\BpSearch;

use Bitrix\Main\Event;

class Handlers {

    public static function onAfterAdd(Event $event){
        $primary = $event->getParameter('primary');
        $id = is_array($primary) ? ($primary['ID']??"") : $primary;
        $id = (int) $id;
        if($id)
            BpIndexTable::reIndexBpId($id);
    }

    public static function onAfterUpdate(Event $event){
        $primary = $event->getParameter('primary');
        $id = is_array($primary) ? ($primary['ID']??"") : $primary;
        $id = (int) $id;
        if($id)
            BpIndexTable::reIndexBpId($id);
    }

    public static function onAfterDelete(Event $event){
        $primary = $event->getParameter('primary');
        $id = is_array($primary) ? ($primary['ID']??"") : $primary;
        $id = (int) $id;
        if($id)
            BpIndexTable::reIndexBpId($id);
    }

}