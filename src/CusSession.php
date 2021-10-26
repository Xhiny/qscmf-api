<?php
namespace QscmfApi;

class CusSession {

    const SESSION_TYPE_CUS = 'CusSession';
    const SESSION_TYPE_COMMON = 'Session';

    public static $sid = '';
    public static $send_flg = false;

    private static $cus_session_obj = null;

    public static function registerSessionCls($cus_session_obj){
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
}