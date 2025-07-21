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
| QSCMFAPI_ENCRYPTION_KEY |  用于加密密钥的key，长度为32位  |   |


```bash
# 将结果复制到 .env 文件
php -r "echo 'QSCMFAPI_ENCRYPTION_KEY=' . bin2hex(random_bytes(32)) . PHP_EOL;"
```


#### 配置参数
   ```php
   // config.php

    // 跳过验证的IP列表
   'QSCMFAPI_HMAC_IP_WHITELIST' => [], 

    // 最大时间偏差(秒)
   'QSCMFAPI_HMAC_TOLERANCE' => 300, 

   // Header 头字段映射配置，默认值如下，支持自定义
   'QSCMFAPI_HMAC_HEADER_MAP' => [
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
    | X-H-Api-Appid | string | 是   | 应用ID   | your_appid     |
    | X-H-Api-Timestamp   | integer  | 是   | 当前 Unix 时间戳（秒） | 1678886400     |
    | X-H-Api-Nonce | string   | 是   | 32位以内的随机字符串 | a8f5f167255d44a5b5f73a13f7a0de79 |
    | X-H-Api-Sign | string   | 是   | 本次请求的签名，详见下文 | ASDGAGFDGDA... |

#### 签名生成步骤

1. 准备参数：
   - appid
   - timestamp
   - nonce
   - 服务端分配的key

2. 按字典序排序参数

3. 拼接参数和key：
   ```
   appid=your_app_id&nonce=random_string&timestamp=1678886400&key=your_key
   ```

4. 使用HMAC-SHA256算法生成签名

5. 转为大写作为最终签名


### 自定义HMAC签名算法
// todo 待完善