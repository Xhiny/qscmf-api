<?php


namespace QscmfCrossApi;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterMethod
{

    protected $sign;
    protected $name;
    protected $add_data;
    protected $del_data;

    public function __construct($sign, $name = null)
    {
        $this->setSign($sign);
        !is_null($name) && $this->setName($name);
    }

    public function setName($name){
        $this->name = $name;
        return $this;
    }

    public function setSign($sign){
        $this->sign = $sign;
        return $this;
    }

    protected function genId(){
        return Str::uuid()->getHex();
    }

    public function addMethod($module_name, $controller_name, $action_name){
        $this->add_data[] = $this->combineMethod($module_name, $controller_name, $action_name);
        return $this;
    }

    public function delMethod($module_name, $controller_name, $action_name){
        $this->del_data[] = $this->combineMethod($module_name, $controller_name, $action_name);
        return $this;
    }

    public function register(){
        $data = $this->fetchDataWithSign();
        if (empty($data)){
            return $this->insert();
        }else{
            return $this->update($data);
        }
    }

    protected function insert(){
        $new_api = $this->combineApi();
        if (!empty($new_api)){
            $insert_data = [
                'id' => $this->genId(),
                'sign' => $this->sign,
                'name' => $this->name,
                'api' => $new_api,
                'create_date' => microtime(true)
            ];

            return DB::table(RegisterMethod::getTableName())->insert($insert_data);
        }
    }

    protected function update($data){
        $new_api = $this->combineApi($data->api);

        $update_data = [
            'name' => !is_null($this->name) ? $this->name : $data->name,
            'api' => $new_api,
        ];

        return DB::table(RegisterMethod::getTableName())->where('sign', $data->sign)->update($update_data);
    }

    protected function combineMethod($module_name, $controller_name, $action_name){
        return implode(',', [$module_name,$controller_name,$action_name]);
    }

    protected function fetchDataWithSign(){
        return DB::table(RegisterMethod::getTableName())->where('sign', $this->sign)->get()->first();
    }

    protected function combineApi($db_api = null){
        $new_data = [];
        if (!empty($db_api_arr = json_decode($db_api, true))){
            $new_data = $db_api_arr;
        }
        if (!empty($this->add_data)){
            $new_data = array_merge($new_data, $this->add_data);
        }

        if (!empty($this->del_data)){
            $new_data = array_filter($new_data, function ($item) {
                return !in_array($item, $this->del_data);
            });
        }

        $new_data = array_values(array_unique($new_data));

        return !empty($new_data) ? json_encode($new_data) : "";

    }

    public static function getDbTablePrefix()
    {
        return env("DB_PREFIX");
    }

    public static function getTableName(){
        return self::getDbTablePrefix().'cross_api_register';
    }
}