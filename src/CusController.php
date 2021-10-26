<?php
namespace QscmfApi;

use Think\Controller;

class CusController extends Controller{

    public function __construct() {
        parent::__construct();

        $this->initSession();

        $header_arr = getallheaders();
        CusSession::setId($header_arr['Authorization']);
    }

    protected function initSession(){
        $type = C('QSCMFAPI_CUS_SESSION_TYPE', null, \QscmfApi\CusSession::SESSION_TYPE_CUS);
        $class  =   strpos($type,'\\')? $type : 'QscmfApi\\Session\\'.$type;
        if(class_exists($class)){
            $session = new $class();
            CusSession::registerSessionCls($session);
        }else{
            E('不存在此session类型:'.$type);
        }

    }
}