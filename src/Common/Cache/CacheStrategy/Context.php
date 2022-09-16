<?php

namespace QscmfApiCommon\Cache\CacheStrategy;

use QscmfApiCommon\Cache\Cache;
use QscmfApiCommon\Cache\Request;

class Context
{

    protected Cache $match_cache;
    protected Request $request;
    protected array $match_options;
    protected array $cache_key;

    public function __construct(Request $request, $cache_strategy, array $cache_key)
    {
        $this->request = $request;
        $this->cache_key = $cache_key;
        $this->init($cache_strategy);
    }

    protected function init($cache_strategy):void{
        foreach($cache_strategy as $item){
            $cls = $this->getStrategyByMap($item['type']);
            if (class_exists($cls)){
                $strategy = new $cls($this->request, $item['strategy']??'');
                $match = $strategy->isMatch();
                if ($match){
                    $this->setMatchOptions($item);
                    $this->setMatchCache($item['cache']);
                    break;
                }
            }else{
                E("暂不支持该类型");
            }
        }
    }

    protected function setMatchOptions($match_options):self{
        $this->match_options = $match_options;
        return $this;
    }

    protected function setMatchCache($cache_options):self{
        $this->match_cache = new Cache($this->cache_key['name'], $this->cache_key['key'], $cache_options);
        return $this;
    }

    protected function getStrategyByMap($type){
        $cls =  ucfirst(\Illuminate\Support\Str::camel($type))."CacheStrategy";
        return "\\QscmfApiCommon\\Cache\\CacheStrategy\\$cls";
    }

    public function isMatch():bool{
        return isset($this->match_options)&&!empty($this->match_options);
    }

    public function getMatchCache():Cache{
        return $this->match_cache;
    }

}