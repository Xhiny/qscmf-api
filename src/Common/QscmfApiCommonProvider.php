<?php
namespace QscmfApiCommon;

use Bootstrap\Provider;
use Bootstrap\RegisterContainer;

class QscmfApiCommonProvider implements Provider {

    public function register(){
        $this->addHook();
        RegisterContainer::registerController('extendApi','Help', HelpController::class);
    }

    protected function addHook(){
        \Think\Hook::add('action_end', 'QscmfApiCommon\\Behaviors\\ClearApiCacheBehavior');
        \Think\Hook::add('after_insert', 'QscmfApiCommon\\Behaviors\\AfterInsertCollectBehavior');
        \Think\Hook::add('after_update', 'QscmfApiCommon\\Behaviors\\AfterUpdateCollectBehavior');
        \Think\Hook::add('after_delete', 'QscmfApiCommon\\Behaviors\\AfterDeleteCollectBehavior');
    }
}