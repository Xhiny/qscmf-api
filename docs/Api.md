# qscmf-api

API开发集成库，自身内置接口开发规范，采用自定义的session验证机制，类restful的接口规范（接口命名方面不完全遵守），并且绑定swagger说明文档工具

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


#### 验证请求

接口如果没有设置noAuthorization 属性，则必须验证通过才能访问；先通过请求颁发sid的接口完成登录验证，登录验证接口可以在返回数据中加入sid通知请求端，请求端在获取到新的sid后保存到本地，在拦截器中给每个请求头的Authorization中加入sid；请求端监控每个请求是否返回新的sid，如果有新的sid，则必须替换掉原来旧的sid值。


#### 内置方法

| 方法名                | 说明                   | 参数                                                         | 返回值                                                       |
| :-------------------- | :--------------------- | :----------------------------------------------------------- | ------------------------------------------------------------ |
| CusSession::get       | 读取CusSession         | key 存放在CusSession对应的key值                              | key对应的数据                                                |
| CusSession::set       | 将数据存放到CusSession | key 存放在CusSession的标识<br />value 需要存放的数据，可以是数组，字符串，数字<br />expire 数据的过期时间 | 返回false表示出错，否则成功                                  |
| CusSession::$send_flg | 是否要返回sid          | true response会自动加上sid返回<br />默认是false              |                                                              |



#### 设置值

设置值可以在 app/Common/Conf/config.php 里设置，其中QSCMFAPI_REST_USER_MODEL ，QSCMFAPI_AUTH_ID_COLUMN 在使用前必须配置，否则报错

| 设置值                      | 说明                      | 默认值                                                       |
| :-------------------------- | :------------------------ | :----------------------------------------------------------- |
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

| 设置值                  | 说明                      | 默认值 |
| :---------------------- |:------------------------| :----- |
| QSCMFAPI_MP_MAINTENANCE | 关闭接口的请求                 |        |
| USE_API_CACHE | 缓存机制开关，false 关闭 true 开启 |        |


#### 设置接口角色权限

开启角色验证，必须为接口定义_role_auth属性及通过 \QscmfApi\RestController::registerRoleHandler() 方法注册获取角色方法才会生效

开启后，接口会自动完成用户角色权限的验证，没有权限统一返回403

支持一个多角色定义，只要符合其中一种角色，及可通过验证

实例代码
```php
//在AppInitBehavior里注册获取角色的方法, person_id
\QscmfApi\RestController::registerRoleHandler(function(){
      $person_id = CusSession::get(C("QSCMFAPI_AUTH_ID"));

      $teacher = D("Teacher")->where(['person_id' => $person_id, 'status' => DBCont::NORMAL_STATUS])->find();
      if($teacher){
          return [PersonRole::TEACHER];
      }
      else{
          return [PersonRole::GUEST];
      }
});
```

有两种定义_role_auth的方法，第一种
```php
class DemoController extends RestController{

    //所有类型的接口（get,post,put,delete）都要验证是否包含TEACHER角色
    protected $_role_auth = [
        PersonRole::TEACHER
    ];

    public function gets(){

        $this->response('成功', 1, 'ok');
    }
}
```

第二种
```php
class DemoController extends RestController{

    //只有gets类型的请求才要求验证TEACHER角色
    protected $_role_auth = [
        'gets' => [PersonRole::TEACHER]
    ];

    public function gets(){

        $this->response('成功', 1, 'ok');
    }
}
```