<?php

namespace QscmfApiCommon\Cache\CacheStrategy;

use QscmfApiCommon\Cache\Request;

abstract class ACacheStrategy
{

    protected string $type;
    protected Request $request;
    protected array $field;
    protected string $logic;

    public function __construct(Request $request, string|array $value){
        $this->setRequest($request);
        $this->setValue($value);
    }

    protected function setValue(string|array $value):self {
        if(is_array($value)){
            !isset($value['field']) && E("不可以缺少 field");
            !isset($value['logic']) && E("不可以缺少 logic");

            $field = $value['field'];
            $logic = $value['logic'];
        }else{
            $field = [$value];
            $logic = 'and';
        }

        $this->field = $field;
        $this->logic = $logic;

        return $this;
    }

    protected function setRequest(Request $request):self{
        $this->request = $request;
        return $this;
    }

    abstract protected function validateItem($request_params, $field):bool;

    public function isMatch():bool{
        return match($this->logic){
            'or' => $this->isMatchByOr(),
            'and' => $this->isMatchByAnd()
        };
    }

    protected function isMatchByOr():bool{
        $request_param = $this->request->getParams();
        $not_match_num = 0;
        $is_match = false;

        foreach($this->field as $field){
            $is_match = $this->validateItem($request_param, $field);
            if ($is_match){
                break;
            }else{
                $not_match_num++;
            }
        }
        if (!$is_match && $not_match_num === count($this->field)){
            $is_match = false;
        }

        return $is_match;
    }

    protected function isMatchByAnd():bool{
        $request_param = $this->request->getParams();
        $is_match = false;
        foreach($this->field as $field){
            $is_match = $this->validateItem($request_param, $field);
            if (!$is_match){
                break;
            }
        }
        return $is_match;
    }
}