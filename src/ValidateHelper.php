<?php
namespace QscmfApi;

trait ValidateHelper{

    public function checkRequired($data, $required_list){
        foreach($required_list as $k => $v){
            if (is_numeric($k)){
                $key = $v;
                $value = $v;
            }else{
                $key = $k;
                $value = $v;
            }
            if(qsEmpty($data[$key])){
                $this->response($value. ' 不能为空', 0, [], 200);
            }
        }
        return true;
    }
}