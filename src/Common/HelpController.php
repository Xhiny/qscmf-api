<?php
namespace QscmfApiCommon;

use OpenApi\Generator;
use Think\Controller;

class HelpController extends Controller {

    public function index(){
        $openapi = Generator::scan(C("QSCMFAPI_SWAGGER_DIR", null, [ROOT_PATH . '/app/Api/Controller', ROOT_PATH . '/app/Common/Model']));
        header('Content-Type: application/json');
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers:Authorization,Accept,Device");
        header("Access-Control-Allow-Methods:GET,POST,PUT,DELETE,OPTIONS");
        echo $openapi->toJson();
    }
}