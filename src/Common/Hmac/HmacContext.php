<?php

namespace QscmfApiCommon\Hmac;

class HmacContext {
    const HANDLER_DEFAULT = 'DefaultHmacHandler';
    
    private static $handler = null;
    
    public static function registerHandler(IHmacHandler $handler): void {
        self::$handler = $handler;
    }
    
    public static function setSecretKeyResolver(callable $resolver): void {
        self::getHandler()->setSecretKeyResolver($resolver);
    }
    
    public static function verify(array $custom_keys = []): array {
        return self::getHandler()->verify($custom_keys);
    }
    
    private static function getHandler(): IHmacHandler {
        if (self::$handler === null) {
            self::init();
        }
        return self::$handler;
    }

    public static function getCachePrefix(): string {
        return self::getHandler()->getCachePrefix();
    }
    
    private static function init(): void {
        $type = C('HMAC_HANDLER_TYPE', null, self::HANDLER_DEFAULT);
        $class = strpos($type, '\\') ? $type : __NAMESPACE__ . '\\' . $type;
        
        if (class_exists($class)) {
            self::$handler = new $class();
        } else {
            throw new HmacException('HMAC处理器不存在: ' . $type);
        }

        if(self::$handler instanceof DefaultHmacHandler &&
         !class_exists(\Qscmf\Utils\Libs\Common::class)){
            throw new HmacException('请引入缓存类:'. \Qscmf\Utils\Libs\Common::class);
        }

    }

}
