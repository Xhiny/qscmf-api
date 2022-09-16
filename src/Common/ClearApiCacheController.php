<?php

namespace QscmfApiCommon;

use QscmfApiCommon\Cache\CacheManager;

class ClearApiCacheController extends \Think\Controller
{
    use TCliMode;

    public function __construct(){
        $log_file_name = date("Ymd")."_clear_api_cache.log";
        $this->setLogFileName($log_file_name);
        parent::__construct();
    }

    public function clear(){
        $controller_name_arr = $this->getControllerName();
        if ($controller_name_arr){
            $num = CacheManager::getInstance()->del($controller_name_arr);
            $message = "successfully cleared".PHP_EOL."total " . $num.PHP_EOL;
        }else{
            $message = "nothing been cleared";
        }

        $this->writeErrorLog($message, true);
    }

    protected function getControllerName():array{
        $controller_name_arr = [];
        $dirs = C("QSCMFAPI_CACHE_DIR", null, [ROOT_PATH . '/app/Api/Controller']);
        foreach ($dirs as $dir){
            $namespace = $this->getNamespace($dir);
            $this->getOneDir($dir,$namespace,$controller_name_arr);
        }

        return $controller_name_arr;
    }

    protected function getNamespace($dir){
        $app_name = APP_NAME;
        $app_name_len = mb_strlen($app_name);
        return str_replace('/','\\' ,substr($dir, strpos($dir, $app_name)+$app_name_len+1)).'\\';
    }

    protected function getOneDir(string $dir, $namespace, &$controller_name_arr){
        $file_arr = glob($dir . '/*?Controller.class.php');
        foreach($file_arr as $file) {
            $file_name = basename($file);
            $controller_name = str_replace('.class.php', '', $file_name);
            $controller_name_arr[] = $namespace.$controller_name;
        }
    }
}