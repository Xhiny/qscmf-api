<?php

namespace QscmfApiCommon\Lib\Hmac;

use Think\Log;

class HmacHelper
{
    // 时间容差（5分钟）
    const TIMESTAMP_TOLERANCE = 300;
    const DEFAULT_HEADER_MAP = [
        'appid' => 'X-H-Api-Appid',
        'timestamp' => 'X-H-Api-Timestamp',
        'nonce' => 'X-H-Api-Nonce',
        'signature' => 'X-H-Api-Sign'
    ];
    
    private static $secret_key_resolver = null;
    
    /**
     * 设置密钥解析器
     */
    public static function setSecretKeyResolver(callable $resolver): void
    {
        self::$secret_key_resolver = $resolver;
    }
    
    /**
     * 获取请求头值
     */
    private static function getHeaderValue(string $key, array $headers, array $custom_keys = []): string
    {
        $key_map = self::getHeaderKey($custom_keys);
        $header_key = $key_map[$key] ?? '';
        return $headers[$header_key] ?? '';
    }
    
    private static function getHeaderKey(array $custom_keys = []): array
    {
        return array_merge(self::DEFAULT_HEADER_MAP, $custom_keys);
    }

    /**
     * 获取密钥（带缓存）
     */
    public static function getSecretKey(string $app_id): string
    {
        $cache_key = "hmac_secret_{$app_id}";
        
        // 缓存命中
        if ($secret = S($cache_key)) {
            return $secret;
        }
        
        // 通过解析器获取
        if (self::$secret_key_resolver) {
            $secret = call_user_func(self::$secret_key_resolver, $app_id);
            $cache_ttl = 300 + rand(-30, 30); // 随机抖动避免雪崩
            S($cache_key, $secret, $cache_ttl);
            return $secret;
        }
        
        throw new HmacException('SecretKey resolver not configured');
    }
    
    /**
     * 完整HMAC验证
     */
    public static function verify(array $custom_keys = []): bool
    {
        $headers = getallheaders();
        
        // 获取header值
        $app_id = self::getHeaderValue('appid', $headers, $custom_keys);
        $timestamp = (int)self::getHeaderValue('timestamp', $headers, $custom_keys);
        $nonce = self::getHeaderValue('nonce', $headers, $custom_keys);
        $signature = self::getHeaderValue('signature', $headers, $custom_keys);
        
        // 基础验证
        if (empty($app_id) || empty($timestamp) || empty($nonce) || empty($signature)) {
            return false;
        }
        
        // 获取密钥
        try {
            $secret_key = self::getSecretKey($app_id);
        } catch (HmacException $e) {
            if (env('HMAC_LOG_ENABLED')) {
                Log::write("HMAC验证失败: {$e->getMessage()}", Log::WARN);
            }
            return false;
        }
        
        // 时间戳验证
        if (abs(time() - $timestamp) > self::TIMESTAMP_TOLERANCE) {
            return false;
        }
        
        // Nonce验证
        $nonce_cache_key = 'hmac_nonce_' . $nonce;
        if (S($nonce_cache_key)) {
            return false;
        }
        S($nonce_cache_key, 1, self::TIMESTAMP_TOLERANCE * 2);
        
        // 生成服务器签名
        $key_map = self::getHeaderKey($custom_keys);
        $params = [
            $key_map['appid'] => $app_id,
            $key_map['timestamp'] => $timestamp,
            $key_map['nonce'] => $nonce
        ];
        $server_signature = self::genSign($params, $secret_key);
        
        // 安全比较签名
        return hash_equals($server_signature, $signature);
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
