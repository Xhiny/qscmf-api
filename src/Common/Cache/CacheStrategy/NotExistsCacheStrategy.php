<?php

namespace QscmfApiCommon\Cache\CacheStrategy;

class NotExistsCacheStrategy extends ACacheStrategy
{
    protected string $type = 'not_exists';

    protected function validateItem($request_param, $value):bool{
        $value = is_array($value) ? $value : explode(',', $value);
        $request_param_keys = array_keys($request_param);
        return (bool)array_diff(array_filter($value),$request_param_keys);
    }


}