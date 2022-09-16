<?php

namespace QscmfApiCommon;

class CustomConfig
{
    protected array $config_mapping;

    public function __construct(array $config_mapping){
        $this->config_mapping = $this->initCustomConfig($config_mapping);
    }

    protected function initCustomConfig(array $config_mapping):array{
        $default_val = [
            'maintenance' => false,
            'cors' => '*',
            'cache' => false,
            'html_decode_res' => false,
        ];

        return array_merge($default_val,$config_mapping);
    }

    public static function create(array $config_mapping):self{
        return new static($config_mapping);
    }

    public function __call(string $name, array $arguments)
    {
        $regexp = "/^(get)(\w+)(Config)/";
        if (!function_exists($this->$name) && preg_match($regexp, $name)){
            $name = preg_replace($regexp, '$2', $name);
            $name = \Illuminate\Support\Str::snake($name);
            return $this->config_mapping[$name];
        }
    }

}