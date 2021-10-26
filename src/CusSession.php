<?php
namespace QscmfApi;

use QscmfApi\Session\ISession;

class CusSession {

    const SESSION_TYPE_CUS = 'CusSession';
    const SESSION_TYPE_COMMON = 'Session';

    public static $sid = '';
    public static $send_flg = false;

    private static $cus_session_obj = null;

    public static function registerSessionCls(ISession $cus_session_obj){
        self::$cus_session_obj = $cus_session_obj;
    }

    public static function set($key, $value, $expire = null){
        return self::$cus_session_obj->set($key, $value, $expire);
    }

    public static function get($key = ''){
        return self::$cus_session_obj->get($key);
    }

    public static function setId($sid = ''){
        return self::$sid = self::$cus_session_obj->setId($sid);
    }

    public static function init(){
        $type = C('QSCMFAPI_CUS_SESSION_TYPE', null, \QscmfApi\CusSession::SESSION_TYPE_CUS);
        $class  =   strpos($type,'\\')? $type : 'QscmfApi\\Session\\'.$type;
        if(class_exists($class)){
            $session = new $class();
            self::registerSessionCls($session);
        }else{
            E('不存在此session类型:'.$type);
        }
    }
}