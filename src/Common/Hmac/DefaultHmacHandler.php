<?php

namespace QscmfApiCommon\Hmac;

class DefaultHmacHandler implements IHmacHandler {

    use THmac;

    protected $secret_key_resolver;
    
    protected array $header_map;

    protected int $timestamp_tolerance;

    protected \Think\Cache $redis;
    
    public function __construct() {
        $this->header_map = $this->getHeaderMap();
        $this->timestamp_tolerance = $this->getTimeTolerance();
    }
    
    public function verify(array $custom_keys = []): array {        
        $headers = getallheaders();
        $app_id = $this->getHeaderValue('appid', $headers, $custom_keys);
        $timestamp = (int)$this->getHeaderValue('timestamp', $headers, $custom_keys);
        $nonce = $this->getHeaderValue('nonce', $headers, $custom_keys);
        $signature = $this->getHeaderValue('signature', $headers, $custom_keys);

        if ($this->isWhitelisted()) {
            return [true, $app_id];
        }
        
        if (count(array_filter([$app_id, $timestamp, $nonce, $signature])) < 4) {
            return [false];
        }
    
        if (!$this->checkTimestamp($timestamp, $this->timestamp_tolerance)) {
            return [false];
        }
        
        if(!$this->checkNonce($nonce, $this->timestamp_tolerance)){
            return [false];
        }

        if(!$secret_key = $this->getSecretKey($app_id)){        
            return [false];
        }
        
        $key_map = $this->getHeaderKey($custom_keys);
        $params = [
            $key_map['appid'] => $app_id,
            $key_map['timestamp'] => $timestamp,
            $key_map['nonce'] => $nonce
        ];
        $server_signature = $this->genSign($params, $secret_key);
        
        if(!hash_equals($server_signature, $signature)){
            return [false];
        }

        return [true, $app_id];        
    }

}
