<?php
namespace QscmfCrossApi;

use QscmfApiCommon\ARestController;
use QscmfApiCommon\Encryper;
use QscmfApiCommon\Hmac\HmacContext;
use QscmfCrossApi\Model\CrossApiRegisterModel;

class RestController extends ARestController {

    public function __construct() {
        parent::__construct();
        
        // 初始化 HMAC 密钥解析器
        HmacContext::setSecretKeyResolver(function($appId) {
            return $this->getSecretKey($appId);
        });

    }

    protected function getConfigData():array{
        return [
            'maintenance' => env("QSCMF_CROSS_API_MAINTENANCE"),
            'cors' => C('QSCMF_INTRANET_API_CORS', null, '*'),
            'cache' => env('USE_CROSS_API_CACHE'),
            'html_decode_res' => true,
            'hmac_enabled' => env('QSCMF_CROSS_API_HMAC_ENABLED', false), // 添加 HMAC 启用配置
        ];
    }

    protected function response($message, $status, $data = '', $code = 200, array $extra_res_data = []) {
        $this->sendHttpStatus($code);
        $return_data['status'] = $status;
        $return_data['info'] = $message;
        $return_data['data'] = $data;
        if (!empty($extra_res_data)){
            $return_data = array_merge($return_data, $extra_res_data);
        }
        qs_exit($this->encodeData($return_data,strtolower($this->_type)));
    }

    protected function auth($action_name){
        $config = $this->getConfigData();
        
        if ($config['hmac_enabled'] === true) {
            // 使用 HMAC 验证方法
            [$r, $id] = $this->verifyHmac();

            $key = [$id, MODULE_NAME, CONTROLLER_NAME, $action_name];
            $cache_key = 'qscmf_cross_api_method_list_'.implode('_', $key);
            $fn = \Qscmf\Utils\Libs\Common::cached(function($id, $module_name, $controller_name, $action_name){
                return (new CrossApiRegisterModel())
                ->isExistsApiById($id, $module_name, $controller_name,$action_name);

            }, 3600, $cache_key);

            if(!$fn($id, MODULE_NAME, CONTROLLER_NAME, $action_name)){
                $this->response('没有访问权限', 0, '', 403);
            }
        }else{
            $id = getallheaders()['Authorization'] ?? '';
            if (!$id) {
                $this->response('缺少认证信息', 0, '', 401);
            }      
            $intranet_api_register_model = new CrossApiRegisterModel();
            if(!$intranet_api_register_model->isExistsApiById($id, MODULE_NAME, CONTROLLER_NAME, $action_name)){
                $this->response('没有访问权限', 0, '', 403);
            }
        }
    }
    
    /**
     * 获取应用密钥
     * 
     * @param string $appId 应用ID
     * @return string 密钥
     */
    protected function getSecretKey(string $appId): string {
        $model = new CrossApiRegisterModel();
        $record = $model->fetchById($appId, 'status,secret_key');
        
        if (!$record || $record['status'] != 1) {
            $this->response('应用未授权', 0, '', 403);
        }
        
        return (new Encryper())->decrypt($record['secret_key']);
    }

}
