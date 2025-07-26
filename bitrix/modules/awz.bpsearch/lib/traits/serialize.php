<?php
namespace Awz\BpSearch\Traits;

use Bitrix\Main\Web\Json;

trait Serialize {

    public static function serialize($value){
        return serialize($value);
    }

    public static function serializeJson($value){
        return Json::encode($value);
    }

    public static function unserialize($value){
        return unserialize($value, ['allowed_classes'=>false]);
    }

    public static function unserializeJson($value){
        return Json::decode($value);
    }

}