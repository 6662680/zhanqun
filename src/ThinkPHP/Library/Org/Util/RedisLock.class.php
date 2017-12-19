<?php
/**
 * Created by PhpStorm.
 * User: zhujianping
 * Date: 2017/6/1 0001
 * Time: 上午 10:11
 * 分布式redis锁
 */

namespace Org\Util;


class RedisLock
{

    //锁名称
    protected $lockName = '';
    //锁名称的值
    protected $identifier = '';
    //redis连接句柄
    protected $conn = null;

    public function __construct()
    {
        $this->conn = new \Redis();
        $this->conn->connect('10.27.6.207',6379);
    }

    /**
     * 获取锁
     * @param string $lockName 锁名称
     * @param int $acquireTime 锁重试时间
     * @param int $lockTimeout 锁超时时间
     * @return boolean
     */
    public function acquireLock($lockName = '', $acquireTime = 1, $lockTimeout = 10)
    {
        //设定锁名称和锁值
        $this->lockName = 'lock:' . ($lockName ? $lockName : md5(CONTROLLER_NAME . ACTION_NAME));
        $this->identifier = uniqid();
        //获取锁的重试时间
        $endTime = time() + $acquireTime;
        while ($endTime > time()) {
            if ($this->conn->setnx($this->lockName, $this->identifier)) {
                $this->conn->expire($this->lockName, $lockTimeout);
                return true;
                //对于没有对锁附加超时的设定超时
            } elseif (!$this->conn->ttl($this->lockName)) {
                $this->conn->expire($this->lockName, $lockTimeout);
            }
            usleep(10000);
        }
        //如果获取锁失败,则返回false
        return false;
    }

    /**
     * 释放锁资源
     * @return bool
     */
    public function releaseLock()
    {
        while (true) {
            try {
                //判断是否仍有进程持有锁
                $this->conn->watch($this->lockName);
                if ($this->conn->get($this->lockName) == $this->identifier) {
                    //锁释放
                    $this->conn->multi()->delete($this->lockName)->exec();
                    return true;
                }
                $this->conn->unwatch();
                break;
            } catch (RedisException $e) {
                continue;
            }
        }
        return false;
    }

}