<?php

namespace QscmfApiCommon\Cache;

use QscmfApiCommon\Cache\CacheStrategy\Context;
use Qscmf\Utils\Libs\RedisLock;

class FuncRunner
{

    protected \Think\Controller $func_cls;
    protected string $func_name;
    protected Request $request;
    protected Context $strategy_context;

    public function __construct(\Think\Controller $func_cls, string $func_name, array $cache_options)
    {
        $this->func_cls = $func_cls;
        $this->func_name = $func_name;

        $this->request = new Request($func_name);
        $this->strategy_context = new Context($this->request, $cache_options, $this->genCacheKey());
    }

    protected function genCacheKey():array{
        $params_string = serialize($this->request->getParams());
        $class_name = get_class($this->func_cls);
        return ['name' => $class_name, 'key' => $this->func_name .'_'. md5($params_string)];
    }

    public function exec():Response{
        if (!$this->func_cls->getCustomConfig()->getCacheConfig() || !$this->strategy_context->isMatch()){
            $res = $this->genApiRes();
        }elseif(!$cache_data = $this->strategy_context->getMatchCache()->getData()){
            $redis_lock_cls = new RedisLock();
            list($is_lock, $cache_data) = $redis_lock_cls->lockWithCallback($this->genLockKey(),30, [$this,"fetchCacheData"],30);
            if ($is_lock === false){
                $res = new Response("系统繁忙，请稍后再试",0);
            }elseif($is_lock === true){
                $res = $this->genApiRes();
                $this->setCacheData($res);
                $redis_lock_cls->unlock($this->genLockKey());
            }else{
                $res = $this->genCacheRes($cache_data);
            }
        }else{
            $res = $this->genCacheRes($cache_data);
        }

        return $res;
    }

    protected function genApiRes():Response{
        $fun_name = $this->func_name;
        return $this->func_cls->$fun_name();
    }

    protected function genCacheRes($cache_data):Response{
        return new Response($cache_data['message'],$cache_data['status'],$cache_data['data'],$cache_data['code'],(array)$cache_data['extra_res_data']);
    }

    protected function genLockKey():string{
        return 'api_redis_lock_'.md5(json_encode($this->genCacheKey()));
    }

    public function fetchCacheData():array{
        $data = $this->strategy_context->getMatchCache()->getData();
        return [(bool)$data, $data];
    }

    protected function canFetchCache(&$cache_data):bool{
        if (!$this->strategy_context->isMatch()){
            return false;
        }

        list($flag, $cache_data) = $this->fetchCacheData();
        if ($cache_data === false){
            return false;
        }

        return true;
    }

    protected function setCacheData(Response $res){
        if ($this->strategy_context->isMatch()){
            $this->strategy_context->getMatchCache()->setData($res->toJson());
        }
    }

    public function getRequest(){
        return $this->request;
    }

}