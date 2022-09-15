# qscmf-api

API开发集成库，自身内置接口开发规范，类restful的接口规范（接口命名方面不完全遵守），并且绑定swagger说明文档工具


## 安装

```
composer require quansitech/qscmf-api
```

## swagger接口文档工具

捆绑swagger工具，可通过  http://域名/extendApi/help 来访问swagger配置

[swagger使用文档](https://github.com/zircote/swagger-php)


## 用法
+ [Api-API开发集成库](https://github.com/quansitech/qscmf-api/blob/master/docs/Api.md)
+ [CrossApi-用于管理其他系统访问的接口](https://github.com/quansitech/qscmf-api/blob/master/docs/CrossApi.md)


#### 属性设置

属性值在继承了RestController的类里进行设置

| 属性            | 说明                                       | 格式                                                         |
| :-------------- | :----------------------------------------- | :----------------------------------------------------------- |
| filter          | 过滤请求，只有通过了才能进行业务数据的访问 | 二维数组  [['id', 'isExists', 'Order', 404], ['item_id', 'isExists', 'Item', 404]] <br />目前仅支持isExists，检查数据库表有无对应的记录，没有则返回设置的http状态码 |


#### restful规范的语义化请求

1. get 表示获取信息 ，对应controller的gets方法
2. post 表示创建信息，对应controller的create方法
3. put 表示编辑信息, 对应controller的update方法
4. delete 表示删除信息，对应controller的delete方法


#### 版本控制

通过在http请求头的accept里加入version=1.2.1之类的版本号来控制接口的请求路由

如get请求，在accept 的位置加入 version=1.2.1，那么就会匹配到controller的  gets_v1_2_1的方法


#### 内置方法

| 方法名                | 说明                   | 参数                                                         | 返回值                                                       |
| :-------------------- | :--------------------- | :----------------------------------------------------------- | ------------------------------------------------------------ |
| response              | 返回请求内容           | message  提示信息<br />status 类型标记<br />data 返回的具体内容<br />code http状态码，默认值 200<br />extra_res_data 额外需要返回的数据，默认为空数组 | 返回json或者xml等格式的字符串（根据请求的资源类型而定）<br />{  'info': 'message内容', 'status': 1, 'data': 'data的json格式内容', 'extra_res_data':'自定义返回内容'} |
| checkRequired         | 必填验证               | data 需要验证的数组<br />required_list 必填的字段设置，有两种格式，直接举例说明： 1. [ 'id', 'name'] 表示id, name字段都是必填，如果没有填写，自动返回"id必填"这样的错误提示。 2. [ 'title'=> '文章标题', 'type' => '文章类型'], 表示 title, type都是必填字段，后面的value值表示对应字段的中文描述，如没有传递type字段，会自动返回“文章类型必填”的错误提示，这样用户更容易理解错误信息。 | 验证不通过，直接response错误信息，否则返回true               |


#### 设置值

设置值可以在 app/Common/Conf/config.php

| 设置值                      | 说明                      | 默认值                                                       |
| :-------------------------- | :------------------------ | :----------------------------------------------------------- |
| QSCMFAPI_SWAGGER_DIR        | 记录了swagger注释的文件夹 | [ROOT_PATH . '/app/Api/Controller', ROOT_PATH . '/app/Common/Model'] |


#### 使用缓存机制
+ [缓存使用说明](https://github.com/quansitech/qscmf-api/blob/master/docs/Cache.md)
