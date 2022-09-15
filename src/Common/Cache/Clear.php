<?php

namespace QscmfApiCommon\Cache;

class Clear
{
    public static array $clear_api_name = [];

    public static function collect(array $relate_list, $type)
    {
        collect($relate_list)->each(function ($item, $key)use($type){
            $keys = explode(',', $key);
            in_array($type, $keys) && $item && self::$clear_api_name = array_merge(self::$clear_api_name, (array)$item);
        });
    }

    public static function exec():void{
        $keys = array_unique(self::$clear_api_name);
        $keys && CacheManager::getInstance()->del($keys);
    }
}