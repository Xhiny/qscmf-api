<?php

namespace QscmfApiCommon\Cache;

class Request
{
    protected $request_data = [];
    protected $type;

    public function __construct($method)
    {
        $this->type = $this->transcodeMethonToType($method);
        $this->initRequestData();
    }

    protected function initRequestData(){
        $this->request_data = I($this->type.'.');
    }

    public function getParams(){
        return array_filter($this->request_data,function($item){return !qsEmpty($item);});
    }

    protected function transcodeMethonToType($method){
        $find_str = strstr($method, '_', true);
        if ($find_str !== false){
            $method = $find_str;
        }
        return match($method){
            'gets','delete' => 'get',
            'create' => 'post',
            'update' => 'put',
        };
    }

}