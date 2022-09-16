<?php

namespace QscmfApiCommon\Cache;

class Cache
{

    protected string $name;
    protected string $key;
    protected int $expire;
    protected CacheManager $cache_manager;

    public function __construct(string $name, string $key, int $expire){
        $this->name = Helper::replaceSpecStr($name);
        $this->key = $key;
        $this->setExpire($expire);
        $this->cache_manager = CacheManager::getInstance();
    }

    protected function setExpire(int $expire){
        !is_numeric($expire) && E("缓存时间格式不对");
        $this->expire = $expire;
    }

    protected function getExpire():int{
        return $this->expire;
    }

    public function getData(){
        $res = $this->cache_manager->hGetWithExpire($this->name, $this->key);
        return $res !== false ? json_decode($res, true) : false;
    }

    public function setData(string $json_data){
        return $this->cache_manager->hSetWithExpire($this->name,$this->key, $json_data, $this->getExpire());
    }
}