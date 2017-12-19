<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: admin模块函数库 Dates: 2016-07-14
// +------------------------------------------------------------------------------------------ 

/**
 * 加密
 *
 * @return void
 */
if (!function_exists('createPassword')) {
    
    function createPassword($password)
    {
        return md5($password);
    }
}

/**
 * 日志
 *
 * @return void
 */
if (!function_exists('lg')) {
    
    function lg($msg, $level = '')
    {
        \Think\Log::record($msg, $level);
    }
}

/**
 * flock独占锁
 */
function flockMutex()
{
    $lockFile = TEMP_PATH.md5(CONTROLLER_NAME.ACTION_NAME).'.txt';
    //生成锁文件
    if(!is_file($lockFile)){
        $fp=fopen($lockFile,'w');
        fclose($fp);
    }
    $fp = fopen($lockFile,'w');
    //判断是否被其他进程锁定
    if(!flock($fp,LOCK_EX|LOCK_NB)){
        fclose($fp);
        return false;
    }
    return $fp;
}

/**
 * 释放锁资源
 * @param $fp
 */
function unFlockMutex(&$fp)
{
    flock($fp,LOCK_UN);
    fclose($fp);
}

















