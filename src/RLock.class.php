<?php

/**
 * Created by PhpStorm.
 * User: codecooker
 * Date: 16/7/13
 * Time: 下午6:39
 */

class RLock
{
    private static $__config = [
        'retry_count'       =>      10,
        'retry_interval'    =>      20000,
        'ttl'               =>      10,
    ];

    private static $__instances = [];

    //初始化RLock的方法
    public static function init($config) {
        if (!empty($config)) {
            self::$__config = array_merge(self::$__config,$config);
        }
    }

    //链接redis
    private static function init_redis() {
        $servers = self::$__config['servers'];
        if (empty(self::$__instances)) {
            foreach ($servers as $server) {
                $redis = new \Redis();
                $redis->connect($server['host'], $server['port'],$server['time_out']);
                array_push(self::$__instances,$redis);
            }
        }
    }

    //获取锁
    public static function lock($res,$retry_count = null,$ttl = null) {

        self::init_redis();

        if (!isset($ttl)) $ttl = self::$__config['ttl'];
        if (!isset($retry_count)) $retry_count = self::$__config['retry_count'];

        do {

            $lock = true;
            foreach (self::$__instances as $redis) {
                $lock = $lock && self::_lock($redis,$res,$ttl);
            }

            if ($lock) return $lock;

            ($retry_count < 0)?$retry_count = 1:$retry_count--;

            usleep(self::$__config['retry_interval']);

        } while($retry_count > 0);

        return false;
    }

    //单台服务器加锁
    private static function _lock($redis,$res,$ttl) {
        $set_result = $redis->set($res,uniqid());
        if ($set_result) {
            $set_result = $redis->expire($res,$ttl);
        }
        return $set_result;
    }

    //释放锁
    public static function unlock($res) {
        foreach (self::$__instances as $redis) {
            self::_unlock($redis,$res);
        }
    }

    //单台服务器释放锁
    private static function _unlock($redis,$res) {
        return $redis->del($res);
    }

    //同步锁
    public static function synchronized($res,$action) {
        if (is_callable($action)) {
            self::lock($res,-1);
            $action();
            self::unlock($res);
        }
    }
}