<?php

namespace QscmfApiCommon\Cache\CacheStrategy;

class InCacheStrategy extends ACacheStrategy
{
    protected string $type = 'in';

    protected function validateItem($request_param, $value):bool{
        $value = is_array($value) ? $value : explode(',', $value);
        $request_param_keys = array_keys($request_param);
        return count($value) === count($request_param_keys) && !array_diff(array_filter($value),$request_param_keys);
    }


}