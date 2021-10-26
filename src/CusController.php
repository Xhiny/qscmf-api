<?php
namespace QscmfApi;

use Think\Controller;

class CusController extends Controller{

    public function __construct() {
        parent::__construct();

        CusSession::init();

        $header_arr = getallheaders();
        CusSession::setId($header_arr['Authorization']);
    }
}