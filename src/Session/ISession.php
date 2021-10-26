<?php


namespace QscmfApi\Session;

interface ISession
{
    public static function set($key, $value, $expire = null);
    public static function get($key = '');
    public static function setId($sid = '');

}