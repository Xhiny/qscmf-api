<?php

namespace QscmfApiCommon\Hmac;


trait THmacController
{

    /**
     * 验证HMAC签名
     */
    protected function verifyHmac(array $headerKeys = []):array {    
        [$r, $appid] = HmacContext::verify($headerKeys);   
        if (!$r) {
            $this->response('签名验证失败', 0, '', 403);
        }

        return [$r, $appid];
    }

}