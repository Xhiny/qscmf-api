<?php

namespace QscmfApiCommon\Hmac;


trait THmac
{
    
    public function getCachePrefix():string{
        return 'qscmf_api_hmac';
    }

    protected function isWhitelisted(): bool {
        $ip = $_SERVER['REMOTE_ADDR'];
        $whitelist = C('QSCMFAPI_HMAC_IP_WHITELIST', null, []);

        return in_array($ip, (array)$whitelist);
    }
    
    protected function getRedis():\Think\Cache {
        if(!isset($this->redis)){
            $this->redis = \Think\Cache::getInstance('redis', []);
        }

        return $this->redis;
    }    

    protected function checkNonce(string $nonce, int $timestamp_tolerance): bool {
        $nonce_cache_key = $this->getCachePrefix().'_nonce_' . $nonce;
        $r = $this->getRedis()->set($nonce_cache_key, $nonce
        , $timestamp_tolerance * 2, 'nx');

        if($r){
            return true;
        }

        return false;
    }

    protected function checkTimestamp(int $timestamp, int $timestamp_tolerance): bool {
        if (abs(time() - $timestamp) > $timestamp_tolerance) {
            return false;
        }

        return true;
    }
    
    protected function genSign(array $params, string $key): string {
        ksort($params);
        $params['key'] = $key;
        return strtoupper(hash_hmac('sha256', http_build_query($params), $key));
    }
    
    protected function getHeaderValue(string $key, array $headers, array $custom_keys): string {
        $key_map = $this->getHeaderKey($custom_keys);
        $header_key = $key_map[$key] ?? '';
        return $headers[$header_key] ?? '';
    }

    protected function getHeaderKey(array $custom_keys = []): array
    {
        return array_merge($this->header_map, $custom_keys);
    }

    protected function getSecretKey(string $app_id): string {
        $cache_key = $this->getCachePrefix()."_secret_{$app_id}";
        
        if ($this->secret_key_resolver) {
            $fn = \Qscmf\Utils\Libs\Common::cached($this->secret_key_resolver
            , 3600, $cache_key,  $this->getCachePrefix());
            $secret = $fn($app_id);

            return $secret;
        }
        
        throw new HmacException('SecretKey resolver not configured');
    }

    public function setSecretKeyResolver(callable $resolver): void {
        $this->secret_key_resolver = $resolver;
    }

    protected function getHeaderMap() : array {
        return (array)C('QSCMFAPI_HMAC_HEADER_MAP', null, [
            'appid' => 'X-H-Api-Appid',
            'timestamp' => 'X-H-Api-Timestamp',
            'nonce' => 'X-H-Api-Nonce',
            'signature' => 'X-H-Api-Sign'
        ]);
    }

    protected function getTimeTolerance() : int {
        return (int)C('QSCMFAPI_HMAC_TOLERANCE', null, 300);
    }

}
