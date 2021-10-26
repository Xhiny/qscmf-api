# qscmf-api

API开发集成库，自身内置接口开发规范，采用自定义的session验证机制，类restful的接口规范（接口命名方面不完全遵守），并且绑定swagger说明文档工具



## 安装

```
composer require quansitech/qscmf-api
```



## 自定义的session验证机制

1. 需要验证访问的接口要求请求头Authorization必须包含一个sid的32位随机字符串，用于验证用户的身份。

2. sid由登录接口签发，登录通过后，接口在返回内容中包含sid的信息，终端自行保存sid用于请求验证接口。

3. sid有过期时间，过期时间可根据CUS_SESSION_EXPIRE配置设定，过期后，验证接口返回401，通知终端必须重新找登录接口进行新的sid签发。

### session类型
```text
可通过 QSCMFAPI_CUS_SESSION_TYPE 配置设置session类型，默认为 CusSession。

```
+ CusSession
  ```text
  使用默认缓存管理登录态。
  ```
+ Session
  ```text
  使用原生session管理登录态。
  
  此类型可以解决情景：若系统已使用原生session管理登录态，且使用接口的终端不支持cookie，就会导致原登录态失效以及使用了登录态的程序也不可以重用。
  ```


## swagger接口文档工具

捆绑swagger工具，可通过  http://域名/extendApi/help 来访问swagger配置

[swagger使用文档](https://github.com/zircote/swagger-php)



## 用法

先上代码，再上配置说明

```php
use QscmfApi\ResController;
use QscmfApi\CusSession;

class DemoController extends RestController
{
    protected $_filter = [
        ['id', 'isExists', 'Oroder', 404]  //自动完成order表记录是否存在检查，如果不存在返回404
    ];

    protected $noAuthorization = ['gets']; //设置get请求无需通过权限检查

    public function gets(){
        $this->checkRequired(I('get.'), ['id' => '订单号']);  //检查提交的参数是否有id，否则会返回订单号不存在的提示
        
        $id = I('get.id');

        $order = D('Order')->getOne($id);

        $this->response('获取成功', 1, $order); //返回订单的详细的json数据
    }
    
    public function create(){
        $user_id = CusSession::get(C("QSCMFAPI_AUTH_ID"));  //获取登录用户的user_id
        $this->checkRequired(I('post.'), ['title' => '订单号', 'qty' => '物品数量', 'item_id' => '物品id']);
        
        $data['title'] = I('post.title');
        $data['qty'] = I('post.qty');
        $data['item_id'] = I('post.item_id');
        $data['user_id'] = $user_id;
        
        $r = D("Order")->createAdd($data);
        if($r === false){
            $this->response(D("Order")->getError(), 0);
        }
        else{
            $this->response('保存成功', 1);
        }
    }
    
    public function update(){
        
    }
    
    public function delete(){
        
    }
}
```



#### 属性设置

属性值在继承了RestController的类里进行设置

| 属性            | 说明                                       | 格式                                                         |
| :-------------- | :----------------------------------------- | :----------------------------------------------------------- |
| noAuthorization | 无需权限验证的接口方法                     | 数组格式，['gets', 'create']                                 |
| filter          | 过滤请求，只有通过了才能进行业务数据的访问 | 二维数组  [['id', 'isExists', 'Order', 404], ['item_id', 'isExists', 'Item', 404]] <br />目前仅支持isExists，检查数据库表有无对应的记录，没有则返回设置的http状态码 |



#### restful规范的语义化请求

1. get 表示获取信息 ，对应controller的gets方法
2. post 表示创建信息，对应controller的create方法
3. put 表示编辑信息, 对应controller的update方法
4. delete 表示删除信息，对应controller的delete方法



#### 验证请求

接口如果没有设置noAuthorization 属性，则必须验证通过才能访问；先通过请求颁发sid的接口完成登录验证，登录验证接口可以在返回数据中加入sid通知请求端，请求端在获取到新的sid后保存到本地，在拦截器中给每个请求头的Authorization中加入sid；请求端监控每个请求是否返回新的sid，如果有新的sid，则必须替换掉原来旧的sid值。



#### 版本控制

通过在http请求头的accept里加入version=1.2.1之类的版本号来控制接口的请求路由

如get请求，在accept 的位置加入 version=1.2.1，那么就会匹配到controller的  gets_v1_2_1的方法



#### 内置方法

| 方法名                | 说明                   | 参数                                                         | 返回值                                                       |
| :-------------------- | :--------------------- | :----------------------------------------------------------- | ------------------------------------------------------------ |
| response              | 返回请求内容           | message  提示信息<br />status 类型标记<br />data 返回的具体内容<br />code http状态码，默认值 200<br />extra_res_data 额外需要返回的数据，默认为空数组 | 返回json或者xml等格式的字符串（根据请求的资源类型而定）<br />{  'info': 'message内容', 'status': 1, 'data': 'data的json格式内容', 'extra_res_data':'自定义返回内容'} |
| checkRequired         | 必填验证               | data 需要验证的数组<br />required_list 必填的字段设置，有两种格式，直接举例说明： 1. [ 'id', 'name'] 表示id, name字段都是必填，如果没有填写，自动返回"id必填"这样的错误提示。 2. [ 'title'=> '文章标题', 'type' => '文章类型'], 表示 title, type都是必填字段，后面的value值表示对应字段的中文描述，如没有传递type字段，会自动返回“文章类型必填”的错误提示，这样用户更容易理解错误信息。 | 验证不通过，直接response错误信息，否则返回true               |
| CusSession::get       | 读取CusSession         | key 存放在CusSession对应的key值                              | key对应的数据                                                |
| CusSession::set       | 将数据存放到CusSession | key 存放在CusSession的标识<br />value 需要存放的数据，可以是数组，字符串，数字<br />expire 数据的过期时间 | 返回false表示出错，否则成功                                  |
| CusSession::$send_flg | 是否要返回sid          | true response会自动加上sid返回<br />默认是false              |                                                              |



#### 设置值

设置值可以在 app/Common/Conf/config.php 里设置，其中QSCMFAPI_REST_USER_MODEL ，QSCMFAPI_AUTH_ID_COLUMN 在使用前必须配置，否则报错

| 设置值                      | 说明                      | 默认值                                                       |
| :-------------------------- | :------------------------ | :----------------------------------------------------------- |
| QSCMFAPI_SWAGGER_DIR        | 记录了swagger注释的文件夹 | [ROOT_PATH . '/app/Api/Controller', ROOT_PATH . '/app/Common/Model'] |
| QSCMFAPI_AUTH_ID            | 记录用户登录信息的key     | qscmfapi_auth_id                                             |
| QSCMFAPI_CORS               | CORS allow-origin设置     | *                                                            |
| QSCMFAPI_CUS_SESSION_EXPIRE | CusSession超时时间设置    | 3600                                                         |
| QSCMFAPI_REST_USER_MODEL    | 存放用户信息的model       |                                                              |
| QSCMFAPI_AUTH_ID_COLUMN     | 用户标识对应的字段        |                                                              |
| QSCMFAPI_HTML_DECODE_RES    | 接口返回的html特殊字符串是否需要反转义，false 否 true 是   | false                                                              |
| QSCMFAPI_CUS_SESSION_TYPE   | session验证类型，可选值 CusSession I Session  | CusSession                           |                                      |
| QSCMFAPI_MODULE | 若QSCMFAPI_CUS_SESSION_TYPE为Session，需要配置接口模块，多个值使用英文逗号拼接   | Api                           |



#### 环境变量

环境变量在.env文件中设置

| 设置值                  | 说明           | 默认值 |
| :---------------------- | :------------- | :----- |
| QSCMFAPI_MP_MAINTENANCE | 关闭接口的请求 |        |