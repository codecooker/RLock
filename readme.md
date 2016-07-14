PHP实现的，基于Redis的互斥锁，可用于原子事务，也可用于分布式锁。用于解决后台脚本并发执行过程中对数据重复操作造成的影响。先阶段至只实现了锁机制，并没有的对阻塞做控制，如果需要对阻塞做一些控制，可以参见Red算法（一种经典的阻塞控制算法）

### 如何使用
只需要将*RLock.class.php*文件引入自己的工程，在调用的时候引入文件,例如
```php
require("../src/RLock.class.php");
```

### 配置自定义参数
默认提供几个可自定义的参数供调用放设置
* server:redis库的配置信息
* retry_count：获取锁时的重试次数
* retry_interval：每次重试之间的时间间隔，单位为微秒
* ttl：锁的有效时间，超过这个时间间隔，锁将自动被释放

### 调用示例
初始化*RLock*
```php
$config = [
            'servers' => [
                [
                    'host' => '192.168.0.1',
                    'port' =>  6379,
                    'time_out' => 10,
                ],
            ],
        ];
RLock::init($config);
```
使用代码  
```php
RLock::lock('name');    //获取一个锁名为name的锁,如果获取成功则返回true
RLock::unlock('name');  //释放锁名为name的锁
```

以原子的形式执行代码段  
```php
RLock::synchronized('name',function() {
    for ($index = 0;$index < 10;$index++) {
        echo getmypid();
    }
});
```

更多使用方法可以参见test/test.php
