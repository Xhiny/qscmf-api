<?php
namespace QscmfApi;

use Illuminate\Support\Str;

class CusSession {
    //put your code here
    public static $sid = '';
    public static $send_flg = false;

    public static function set($key, $value, $expire = null){
        if(empty(self::$sid)){
            return ;
        }
        $data = S(self::$sid);
        if(is_null($value)){
            unset($data[$key]);
        }
        else{
            $data[$key] = $value;
        }
        if(is_null($expire)){
            $expire = C('QSCMFAPI_CUS_SESSION_EXPIRE', null, 3600);
        }
        return S(self::$sid, $data, $expire);
    }

    public static function get($key = ''){
        if(empty(self::$sid)){
            return ;
        }
        $data = S(self::$sid);
        if($key == ''){
            return $data;
        }
        else{
            return isset($data[$key]) ? $data[$key] : '';
        }
    }

    public static function setId($sid = ''){
        $data = $sid ? S($sid) : '';
        if($data){
            S($sid, $data, C('QSCMFAPI_CUS_SESSION_EXPIRE', null, 3600));
            self::$sid = $sid;
        }
        else{
            self::$sid = Str::uuid()->getHex();
        }
    }
}