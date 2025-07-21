<?php

namespace QscmfCrossApi\Model;

class CrossApiRegisterModel extends \Qscmf\Core\QsListModel
{

    public function fetchApiById($id){
        return $this->where(['id' => $id])->getField('api');
    }

    public function isExistsApiById($id, $module_name, $controller_name, $action_name){
        $api = $this->fetchApiById($id);
        $api_list = json_decode($api, true);

        if (empty($api) || !is_array($api_list)){
            return false;
        }else{
            return in_array(implode(',', [$module_name,$controller_name,$action_name]),$api_list);
        }
    }

    public function fetchById($id, $field){
        return $this->where(['id' => $id])->field($field)->find();
    }

}