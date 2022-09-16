<?php


namespace QscmfApiCommon\Cache;


use Think\Cache;

class CacheManager
{
    protected $redis;

    private function __construct()
    {
        $this->redis = Cache::getInstance('redis');
    }

    public static function getInstance() : static
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }
        return $instance;
    }

    protected function getPrefix() : string{
        return $this->redis->getOptions('prefix');
    }

    public function getRedis(){
        return $this->redis;
    }

    public function hSetWithExpire($name, $key, $value, $expire){
        if ($expire > 0){
            $expire = time()+(int)$expire;
            $value = json_encode(['expire' => $expire, 'value' => $value]);
            return $this->redis->hSet($name, $key, $value);
        }
    }

    public function hGetWithExpire($name, $key){
        $res = $this->redis->eval($this->hGetValidKeyByLua($name, $key));
        if ($res === 0){
            return false;
        }else{
            return json_decode($res, true)['value'];
        }
    }

    protected function hGetValidKeyByLua($name, $key){
        $name = $this->getPrefix().$name;
        return <<<LUA
local member_value = redis.call('hGet', '{$name}','{$key}');
if member_value then 
    local expire = string.match(member_value,"\"expire\":([0-9]+)",0);
    local time_arr = redis.call('TIME');
    local is_valid = time_arr[1] < expire;
    
    if is_valid then
        return member_value;
    else
        redis.call('hDel', '{$name}','{$key}');
        return 0;
    end
else
    return 0;
end
LUA;

    }

    public function del(array $keys){
        $keys = collect($keys)->map(Fn($item)=> \QscmfApiCommon\Cache\Helper::replaceSpecStr($item))->all();
        return $this->redis->del(...$keys);
    }

}