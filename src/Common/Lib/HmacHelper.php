<?php

namespace QscmfApiCommon\Lib;

class HmacHelper
{
    // 时间容差（5分钟）
    const TIMESTAMP_TOLERANCE = 300;
    const DEFAULT_HEADERS = [
        'appid' => 'X-H-Api-Appid',
        'timestamp' => 'X-H-Api-Timestamp',
        'nonce' => 'X-H-Api-Nonce',
        'signature' => 'X-H-Api-Sign'
    ];
    
    private static $secretKeyResolver = null;
    
    /**
     * 设置密钥解析器
     */
    public static function setSecretKeyResolver(callable $resolver): void
    {
        self::$secretKeyResolver = $resolver;
    }
    
    /**
     * 获取请求头值
     */
    private static function getHeaderValue(string $key, array $headers, array $customKeys = []): string
    {
        $keyMap = self::getHeaderKey( $customKeys);
        $headerKey = $keyMap[$key] ?? '';
        return $headers[$headerKey] ?? '';
    }
    
     private static function getHeaderKey(array $customKeys = []): array
    {
        return array_merge(self::DEFAULT_HEADERS, $customKeys);
    }

    /**
     * 获取密钥（带缓存）
     */
    public static function getSecretKey(string $appId): string
    {
        $cacheKey = "hmac_secret_{$appId}";
        
        // 缓存命中
        if ($secret = S($cacheKey)) {
            return $secret;
        }
        
        // 通过解析器获取
        if (self::$secretKeyResolver) {
            $secret = call_user_func(self::$secretKeyResolver, $appId);
            S($cacheKey, $secret, 300); // 缓存5分钟
            return $secret;
        }
        
        throw new \Exception('SecretKey resolver not configured');
    }
    
    /**
     * 完整HMAC验证
     */
    public static function verify(array $customKeys = []): bool
    {
        $headers = getallheaders();
        
        // 获取header值
        $appId = self::getHeaderValue('appid', $headers, $customKeys);
        $timestamp = (int)self::getHeaderValue('timestamp', $headers, $customKeys);
        $nonce = self::getHeaderValue('nonce', $headers, $customKeys);
        $signature = self::getHeaderValue('signature', $headers, $customKeys);
        
        // 基础验证
        if (empty($appId) || empty($timestamp) || empty($nonce) || empty($signature)) {
            return false;
        }
        
        // 获取密钥
        try {
            $secretKey = self::getSecretKey($appId);
        } catch (\Exception $e) {
            return false;
        }
        
        // 时间戳验证
        if (abs(time() - $timestamp) > self::TIMESTAMP_TOLERANCE) {
            return false;
        }
        
        // Nonce验证
        $nonceCacheKey = 'hmac_nonce_' . $nonce;
        if (S($nonceCacheKey)) {
            return false;
        }
        S($nonceCacheKey, 1, self::TIMESTAMP_TOLERANCE * 2);
        
        $keyMap = self::getHeaderKey( $customKeys);
        $params = [
            $keyMap['appid'] => $appId,
            $keyMap['timestamp'] => $timestamp,
            $keyMap['nonce'] => $nonce
        ];
        // 生成服务器签名
        $serverSignature = self::genSign( $params, $secretKey);
        
        // 安全比较签名
        return hash_equals($serverSignature, $signature);
    }

    /**
     * 生成HMAC签名
     */
    public static function genSign(array $params, string $key):string{
        ksort($params);

        $params['key'] = $key;

        return strtoupper(hash_hmac('sha256', http_build_query($params), $key));
    }
}
