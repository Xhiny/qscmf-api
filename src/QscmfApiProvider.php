<?php
namespace QscmfApi;

use Bootstrap\Provider;
use Bootstrap\RegisterContainer;

class QscmfApiProvider implements Provider {

    public function register(){
        RegisterContainer::registerController('extendApi','Help', HelpController::class);
    }
}