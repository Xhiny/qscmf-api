<?php

namespace QscmfApiCommon\Behaviors;

class AfterInsertCollectBehavior {

    use TClearCollect;

    public function run(&$params){
        $this->collect($params['model_obj']->relate_api_controllers,'insert');
    }
}