<?php


namespace QscmfCrossApi;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use QscmfApiCommon\Encryper;

class RegisterMethod
{

    protected $sign;
    protected $name;
    protected $add_data;
    protected $del_data;
    protected $use_hmac = false;

    public function __construct($sign, $name = null)
    {
        $this->setSign($sign);
        !is_null($name) && $this->setName($name);
    }

    public function setUseHmac(bool $use_hamc)  {
        $this->use_hmac = $use_hamc;
        return $this;
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

    protected function genRandSecretKey(int $length = 16): string
    {
        // 定义可用的字符集
        // 过滤了容易混淆的字符：0, O, o, 1, l, I
        $set = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
        $len = strlen($set);
        
        $secret = '';
        
        // 从密码学安全的随机字节源中获取数据
        $randomBytes = random_bytes($length);
        
        for ($i = 0; $i < $length; ++$i) {
            // 将每个随机字节转换为字符集中的一个字符
            // ord() 获取字节的ASCII值 (0-255)
            // % $len 确保索引在字符集范围内
            $secret .= $set[ord($randomBytes[$i]) % $len];
        }
        
        return 'sk_' . $secret; // 仍然建议保留 "sk_" 前缀，以便识别
    }

    protected function showRegister(string $name, string $id, string $key):void{
         $msg = date('Y-m-d H:i:s').' 注册成功：'.PHP_EOL
                .'name:'.$name.PHP_EOL
                .'appid:'.$id.PHP_EOL.'key:'.$key.PHP_EOL
                .'只会展示一次，请另存'.PHP_EOL;

        $file =  LARA_DIR. DIRECTORY_SEPARATOR. 'storage/logs/'.date('Ymd').'_qscmf_api_register_sys.log';
        fwrite(STDOUT, $msg);
        file_put_contents($file, $msg, FILE_APPEND);
    }

    protected function genKey():array{
        $raw_key = $this->genRandSecretKey(16);
        $secret_key = (new Encryper())->encrypt($raw_key);

        return [$raw_key, $secret_key];
    }

    protected function insert(){
        $new_api = $this->combineApi();
        if (!empty($new_api)){
            $id = $this->genId();
            $insert_data = [
                'id' => $id,
                'sign' => $this->sign,
                'name' => $this->name,
                'api' => $new_api,
                'create_date' => microtime(true)
            ];
            if($this->use_hmac){
                [$raw_key, $secret_key] = $this->genKey();
                $insert_data['secret_key'] = $secret_key;
            }

            $r = DB::table(RegisterMethod::getTableName())->insert($insert_data);
            if($r && $this->use_hmac){
                $this->showRegister($this->name, $id, $raw_key);                
            }

            return $r;
        }
    }

    protected function update($data){
        $new_api = $this->combineApi($data->api);

        $update_data = [
            'name' => !is_null($this->name) ? $this->name : $data->name,
            'api' => $new_api,
        ];

        if($this->use_hmac && empty($data->secret_key)){
            $log = true;
            [$raw_key, $secret_key] = $this->genKey();
            $update_data['secret_key'] = $secret_key;
        }

        $r = DB::table(RegisterMethod::getTableName())->where('sign', $data->sign)->update($update_data);

         if($r && $log){
            $this->showRegister($data->name, $data->id, $raw_key);
        }

        return $r;
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