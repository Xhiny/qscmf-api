<?php

namespace QscmfApi\Behaviors;

class ResetSessionIdBehavior{

    public function run(&$params)
    {
        if ($this->isTargetModule()){
            $this->setExpire();
        }
        if ($this->isCommonSession() && $sid = $this->getSid()){
            session_id($sid);
        }
    }

    protected function isTargetModule(){
        if (!in_array(strtolower(MODULE_NAME), $this->transcodeApiModule())){
            return false;
        }

        return true;
    }

    protected function isCommonSession(){
        if (!C('QSCMFAPI_CUS_SESSION_TYPE') === \QscmfApi\CusSession::SESSION_TYPE_COMMON){
            return false;
        }
        return true;
    }

    protected function getSid(){
        $key = 'Authorization';
        return isset(getallheaders()[$key]) ? getallheaders()[$key] : null;
    }

    protected function transcodeApiModule(){
        return explode(',', C('QSCMFAPI_MODULE',null, 'api'));
    }

    protected function setExpire(){
        $expire = (int)C('QSCMFAPI_CUS_SESSION_EXPIRE', null, 3600);
        C('SESSION_OPTIONS.expire', $expire);
    }
}