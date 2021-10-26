<?php
namespace QscmfApi;

use Bootstrap\Provider;
use Bootstrap\RegisterContainer;

class QscmfApiProvider implements Provider {

    public function register(){
        $this->addHook();
        RegisterContainer::registerController('extendApi','Help', HelpController::class);
    }

    protected function addHook(){
        \Think\Hook::add('app_begin', 'QscmfApi\\Behaviors\\ResetSessionIdBehavior');
    }
}