# 缓存机制

```
同一个Api，处理相同的请求参数,在数据没有变动的情况下返回值应该是不变的。
使用缓存可以减轻数据库、服务器压力，提高系统响应速度。

根据请求参数设置缓存策略，灵活处理接口的缓存数据。
当数据有变动时，自动清理相关接口的缓存。
```

**当接口返回的数据与用户态相关时，不应该使用缓存，否则会出现用户数据错乱的情况。**

#### 用法

+ 缓存类型*DATA_CACHE_TYPE*需设置为*Redis*

+ 将Api缓存机制开关打开，详情请看对应的Api文档

+ 配置接口缓存策略，符合策略则设置缓存，详情查看缓存策略说明
  
  ```php
  // 不同版本的接口需要不同的缓存策略
  protected array $cache_strategy = [
      'gets' => [['type' => 'not_exists', 'strategy' => 'id', 'cache' => 3600]],
      'gets_v2' => [['type' => 'all', 'cache' => 3600]],
  ];
  ```

+ 接口需返回*\QscmfApiCommon\Cache\Response*对象
  
  ```php
  public function gets(){
      // 业务代码
      return new \QscmfApiCommon\Cache\Response('成功', 1, $res);
  }
  ```

+ 配置模型层*relate_api_controllers*属性，数据变动时清空相关接口的缓存数据
  
  ```php
  // 在ActivityModel中配置public属性relate_api_controllers
  // 当Activity数据有新增、更新、删除时，都删除对应接口的数据
  public array $relate_api_controllers = [
      'insert' => ActivityController::class,
      'update' => [
          ActivityController::class,
          SchoolActivityController::class,
          ClassController::class,
          ReadRecordController::class
      ],
      'delete' => [
          ActivityController::class,
       ]
  ];
  ```

#### 缓存策略说明

| 设置值      | 类型            | 说明        |
|:-------- |:------------- |:--------- |
| type     | string        | 缓存类型      |
| strategy | string\|array | 参数字段      |
| cache    | int           | 缓存时间，单位为秒 |

##### 类型说明

+ **all** 所有情况都设置缓存
  
  ```php
  protected array $cache_strategy = [
      'gets' => [['type' => 'all','cache' => 3600]],
  ];
  ```

+ **in** 仅参数字段符合配置则设置缓存
  
  ```php
  // 请求参数只存在id，才会设置缓存
  protected array $cache_strategy = [
      'gets' => [['type' => 'in','strategy' => 'id','cache' => 3600]],
  ];
  ```

+ **exists** 参数存在某个字段则设置缓存
  
  ```php
  protected array $cache_strategy = [
      'gets' => [
          // 请求参数只要存在id，就会设置缓存
          ['type' => 'exists', 'strategy' => 'id', 'cache' => 3600],
          // 请求参数只要存在name，就会设置缓存
          ['type' => 'exists', 'strategy' => 'name', 'cache' => 3600],
      ],
  ];
  
  // 效果与以上一致
  protected array $cache_strategy = [
      'gets' => [
          // 请求参数只要存在id或者name，就会设置缓存
          ['type' => 'exists', 'strategy' => ['field' => ['id','name'], 'logic' => 'or'], 'cache' => 3600],
      ],
  ];
  ```

+ **not_exists** 参数不存在某个字段则设置缓存
  
  ```php
  // 请求参数只要不存在id，就会设置缓存
  protected array $cache_strategy = [
      'gets' => [['type' => 'not_exists','strategy' => 'id','cache' => 3600]],
  ];
  ```

缓存数据的数据结构为*hash*，根据接口分组，不同的请求参数为一个*member*

如缓存前缀*prefix*为*qs_cmf*，模块为*Api*，控制器*DemoController*的缓存键值：
*qs_cmf_Api_Controller_DemoController*