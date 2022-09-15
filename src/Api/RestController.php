<?php
namespace QscmfApi;

use QscmfApiCommon\ARestController;

class RestController extends ARestController {

    use TCusSession;

    public function __construct()
    {
        $this->_initCusSession();
        parent::__construct();
    }

    protected function getConfigData():array{
        return [
            'maintenance' => env("QSCMFAPI_MP_MAINTENANCE"),
            'cors' => C('QSCMFAPI_CORS', null, '*'),
            'cache' => env('USE_API_CACHE'),
            'html_decode_res' => C("QSCMFAPI_HTML_DECODE_RES", null, false),
        ];
    }

    protected function response($message, $status, $data = '', $code = 200, array $extra_res_data = []) {
        $this->sendHttpStatus($code);
        $return_data['status'] = $status;
        $return_data['info'] = $message;
        $return_data['data'] = $data;
        if(CusSession::$send_flg){
            $return_data['sid'] = CusSession::$sid;
        }
        if (!empty($extra_res_data)){
            $return_data = array_merge($return_data, $extra_res_data);
        }
        qs_exit($this->encodeData($return_data,strtolower($this->_type)));
    }

    protected function auth($action_name){
        $id = CusSession::get(C('QSCMFAPI_AUTH_ID', null, 'qscmfapi_auth_id'));
        $user_info = D(C('QSCMFAPI_REST_USER_MODEL'))->where([C('QSCMFAPI_AUTH_ID_COLUMN') => $id])->find();
        if(!$user_info){
            $this->response('未登录', 0, '', 401);
        }
    }

}