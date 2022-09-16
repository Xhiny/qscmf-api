<?php
namespace QscmfCrossApi;

use QscmfApiCommon\ARestController;
use QscmfCrossApi\Model\CrossApiRegisterModel;

class RestController extends ARestController {


    protected function getConfigData():array{
        return [
            'maintenance' => env("QSCMF_CROSS_API_MAINTENANCE"),
            'cors' => C('QSCMF_INTRANET_API_CORS', null, '*'),
            'cache' => env('USE_CROSS_API_CACHE'),
            'html_decode_res' => true,
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
        $id = getallheaders()['Authorization'];
        $intranet_api_register_model = new CrossApiRegisterModel();
        if(!$intranet_api_register_model->isExistsApiById($id, MODULE_NAME, CONTROLLER_NAME, $action_name)){
            $this->response('没有访问权限', 0, '', 403);
        }
    }

}
