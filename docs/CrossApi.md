# qscmf-cross-api

用于管理其他系统访问的接口，类restful的接口规范（接口命名方面不完全遵守）

```text
随着业务的变更、系统的迭代，不同系统间的数据可能需要打通
可以使用此扩展包来记录系统接口的使用情况，更好的维护与开发

情景举例：
存在系统A，系统B，系统C
三个系统均有各自管理员用户的权限体系。

现需要在系统A提供的入口统一登录，且统一管理三个系统的用户权限，则需要系统B、系统C提供相应接口来维护权限相关数据。

存在系统D，且其部分用户与系统B用户相关联，则需要系统B的接口提供所需服务。

可以在系统B中安装此扩展包，分别注册系统A、系统D可以访问的接口；
在系统C中安装此扩展包，注册系统A可以访问的接口

```

```php
public function up()
{
    //
    if (Schema::hasColumn(\QscmfCrossApi\RegisterMethod::getTableName(), 'ip'))
    {
        Schema::table(\QscmfCrossApi\RegisterMethod::getTableName(), function (Blueprint $table) {
            $table->dropColumn('ip');
        });
    }


}


public function down()
{
    //
    Schema::table(\QscmfCrossApi\RegisterMethod::getTableName(), function (Blueprint $table) {
        $table->string('ip', 20)->after('sign');
    });
}
```

## 用法


#### 使用数据迁移管理接口权限
```php
public function up()
{
    // 添加接口
    // $sign 使用此服务的系统标识
    // $name 使用此服务的系统名称（第一次新增时必填）
    $register = new \QscmfCrossApi\RegisterMethod('library_local','本地');
    
    // 接口路由信息
    // $module_name, $controller_name, $action_name
    $register->addMethod('IntranetApi', 'Index', 'gets');
    $register->addMethod('IntranetApi', 'Index', 'update');
    $register->register();

}
```

```php
public function down()
{
    // 移除接口
    // $sign 使用此服务的系统标识
    $register = new \QscmfCrossApi\RegisterMethod('library_local');
    
    // 接口路由信息
    // $module_name, $controller_name, $action_name
    $register->delMethod('IntranetApi', 'Index', 'gets');
    $register->delMethod('IntranetApi', 'Index', 'update');
    $register->register();
}
```


#### 接口例子

```php
namespace IntranetApi\Controller;

class IndexController extends \QscmfCrossApi\RestController
{
    protected $_filter = [
        ['id', 'isExists', 'Order', 404]  //自动完成Order表记录是否存在检查，如果不存在返回404
    ];

    public function gets(){
        $this->checkRequired(I('get.'), ['id' => '订单号']);  //检查提交的参数是否有id，否则会返回订单号不存在的提示
        
        $id = I('get.id');

        $order = D('Order')->getOne($id);

        $this->response('获取成功', 1, $order); //返回订单的详细的json数据
    }
    
    public function create(){
        
    }
    
    public function update(){
        
    }
    
    public function delete(){
        
    }
}
```


#### 验证请求
接口必须验证通过才能访问

#### 环境变量

环境变量在.env文件中设置

| 设置值                  | 说明           | 默认值 |
| :---------------------- | :------------- | :----- |
| QSCMF_CROSS_API_MAINTENANCE | 关闭接口的请求 |        |
| USE_CROSS_API_CACHE | 缓存机制开关，false 关闭 true 开启 |        |
| QSCMF_CROSS_API_HMAC_ENABLED | HMAC签名开关，false 关闭 true 开启 |  false  |


#### 使用HMAC
[使用HMAC说明](./Hmac.md)


#### 某个系统需要启用 HMAC签名
```php
public function up()
{
    
    $register = new \QscmfCrossApi\RegisterMethod('library_local','本地');
    // 使用 HMAC 签名
    $register->setUseHmac(true);
    
    $register->addMethod('IntranetApi', 'Index', 'gets');
    $register->addMethod('IntranetApi', 'Index', 'update');
    $register->register();

}
```

### 访问接口
#### 用法
+ 向服务端获取token
```
服务端管理客户端接口权限时，自动生成此值
从数据表 DB_PREFIX_cross_api_register 中获取对应客户端的id字段值即可
```
+ 在每个请求头的Authorization中加入token