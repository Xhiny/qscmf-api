<?php


namespace QscmfApi\Session;

class Session
{
    public $name = \QscmfApi\CusSession::SESSION_TYPE_COMMON;

    public static function set($key, $value)
    {
        session($key, $value);
    }

    public static function get($key){
        return session($key);
    }
    
    public static function setId($sid = '')
    {
        return session_id();
    }

}