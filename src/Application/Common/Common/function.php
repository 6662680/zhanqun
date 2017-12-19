<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 函数库 Dates: 2016-07-14
// +------------------------------------------------------------------------------------------ 

/**
 * 打印参数
 *
 * @return void
 */
function pr($content)
{
    echo '<pre>';

    if (is_array($content) || is_array($content)) {
        print_r($content);
    } else {
        var_dump($content);
    }

    echo '</pre>';
}

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

    function lg($msg, $level = 'ERR')
    {
        \Think\Log::record($msg, $level);
    }
}

/**
 * 错误返回
 *
 * @return void
 */
if (!function_exists('error')) {

    function error($msg, $error)
    {
        return [
            'error'    => $error,
            'errorMsg' => $msg
        ];
    }
}
/**
 * 错误返回
 *
 * @return void
 */
if (!function_exists('is_error')) {

    function is_error($result)
    {
        if( (isset($result['ret']) && intval($result['ret']) === 0) || intval($result) === 0) {
            return true;
        }

        return false;
    }
}