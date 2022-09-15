<?php

namespace QscmfApiCommon\Cache\CacheStrategy;

class AllCacheStrategy extends ACacheStrategy
{
    protected string $type = 'all';

    protected function validateItem($request_param, $value):bool{
        return true;
    }


}