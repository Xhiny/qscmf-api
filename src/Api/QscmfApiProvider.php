<?php
namespace QscmfApi;

use Bootstrap\Provider;

class QscmfApiProvider implements Provider {

    public function register(){
        $this->addHook();
    }

    protected function addHook(){
        \Think\Hook::add('app_begin', 'QscmfApi\\Behaviors\\ResetSessionIdBehavior');
    }
}