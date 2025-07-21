# HMAC签名
```
HMAC (Hash-based Message Authentication Code) 是一种基于密钥的报文验证机制，用于验证API请求的合法性。

默认使用HMAC-SHA256签名验证，可有效防止请求伪造和重放攻击。
```

## 核心功能
- IP白名单机制
- 自定义Header映射
- 自定义签名算法

### 用法

#### 环境变量

环境变量在.env文件中设置

| 设置值                  | 说明           | 默认值 |
| :---------------------- | :------------- | :----- |
| QSCMF_API_ENCRYPTION_KEY |  用于加密密钥的key，长度为32位  |   |


```bash
# 将结果复制到 .env 文件
php -r "echo 'QSCMF_API_ENCRYPTION_KEY=' . bin2hex(random_bytes(32)) . PHP_EOL;"
```


#### 配置参数
   ```php
   // config.php

    // 跳过验证的IP列表
   'HMAC_IP_WHITELIST' => [], 

    // 最大时间偏差(秒)
   'HMAC_TOLERANCE' => 300, 

   // Header 头字段映射配置，默认值如下，支持自定义
   'HMAC_HEADER_MAP' => [
        'appid' => 'X-H-Api-Appid',
        'timestamp' => 'X-H-Api-Timestamp',
        'nonce' => 'X-H-Api-Nonce',
        'signature' => 'X-H-Api-Sign'
   ]

   ```


### 客户端请求
+ 获取授权信息，appid 与 key
+ HTTP 请求头
    | 字段       | 类型      | 必选  | 描述  | 示例                 |
    | -------- | ------- | --- | ---- | ------------------ |
    | X-H-Api-Appid | string | 是   | appid   | your_appid     |
    | X-H-Api-Timestamp   | integer  | 是   | 当前 Unix 时间戳（秒），误差5秒之内 | 1678886400     |
    | X-H-Api-Nonce | string   | 是   | 32位以内的随机字符串 | a8f5f167255d44a5b5f73a13f7a0de79 |
    | X-H-Api-Sign | string   | 是   | 本次请求的签名，详见下文 | ASDGAGFDGDA... |

+ X-H-Api-Sign 签名生成
   + 除 key 外，对其他字段进行字典序排序；
   + 拼接 key ；
   + 使用 HMAC-SHA256 签名算法加密；
   + 转为大写


### 自定义HMAC签名算法
// todo 待完善