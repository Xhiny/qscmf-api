<?php

namespace QscmfApi;

trait TCusSession
{
    private function _initCusSession():void{
        CusSession::init();

        $header_arr = getallheaders();
        CusSession::setId($header_arr['Authorization']);
    }
}