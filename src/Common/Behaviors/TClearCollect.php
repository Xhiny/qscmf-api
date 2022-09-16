<?php

namespace QscmfApiCommon\Behaviors;

use QscmfApiCommon\Cache\Clear;

trait TClearCollect{

    protected function collect($list, $type):void{
        if($this->_needCollect($list, $type)){
            Clear::collect($list, $type);
        }
    }

    private function _needCollect($list, $type):bool{
        if (!env('USE_CROSS_API_CACHE') && !env('USE_API_CACHE')){
            return false;
        }
        if (!in_array($type, ['insert', 'update', 'delete'])){
            return false;
        }
        if (empty($list)){
            return false;
        }

        return true;
    }
}