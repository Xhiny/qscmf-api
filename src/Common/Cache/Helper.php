<?php

namespace QscmfApiCommon\Cache;

class Helper
{

    public static function replaceSpecStr(string $key):string {
        return str_replace('\\', '_', $key);
    }
}