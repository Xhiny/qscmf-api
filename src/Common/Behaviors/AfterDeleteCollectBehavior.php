<?php

namespace QscmfApiCommon\Behaviors;

class AfterDeleteCollectBehavior {

    use TClearCollect;

    public function run(&$params){
        $this->collect($params['model_obj']->relate_api_controllers,'delete');
    }
}