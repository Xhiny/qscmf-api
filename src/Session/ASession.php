<?php


namespace QscmfApi\Session;

abstract class ASession
{
    public static $sid = '';
    public $name = null;

    abstract public static function set($key, $value, $expire = null);

    abstract public static function get($key = '');

    abstract public static function setId($sid = '');

}