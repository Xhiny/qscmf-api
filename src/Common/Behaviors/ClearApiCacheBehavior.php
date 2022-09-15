<?php

namespace QscmfApiCommon\Behaviors;

use QscmfApiCommon\Cache\Clear;

class ClearApiCacheBehavior{

    public function run(&$params)
    {
        Clear::exec();
    }
}