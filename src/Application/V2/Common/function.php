<?php
/**
 * Created by PhpStorm.
 * User: zhujianping
 * Date: 2017/5/16 0016
 * Time: 下午 17:05
 */

/**
 * 判断搜索引擎
 *
 * @return void
 */
function searchEngine($url)
{
    $keyword = '';
    $from = '';

    // 百度PC
    if (strstr($url, 'www.baidu.com')) {
        preg_match("|baidu.+wo?r?d=([^\\&]*)|is", $url, $tmp);
        $keyword = urldecode($tmp[1]);
        $from = '百度PC';
    } elseif (strstr($url, 'm.baidu.com')) { // 手机百度
        preg_match("|baidu.+wo?r?d=([^\\&]*)|is", $url, $tmp);
        $keyword = urldecode($tmp[1]);
        $from = '手机百度';
    } elseif (strstr($url, 'tieba.baidu.com')) {// 百度贴吧
        preg_match("|baidu.+wo?r?d=([^\\&]*)|is", $url, $tmp);
        $keyword = urldecode($tmp[1]);
        $from = '百度贴吧';
    } elseif (strstr($url, 'google.com') or strstr($url, 'google.cn')) { // 谷歌
        preg_match("|google.+q=([^\\&]*)|is", $url, $tmp );
        $keyword = urldecode($tmp[1]);
        $from = '谷歌';
    } elseif (strstr($url, 'haosou.com') or strstr($url, 'so.com')) {  // 360搜索
        preg_match("|so.+q=([^\\&]*)|is", $url, $tmp);
        $keyword = urldecode($tmp[1]);
        $from = '360';
    } elseif (strstr($url, 'sogou.com')) { // 搜狗
        preg_match("|sogou.com.+query=([^\\&]*)|is", $url, $tmp);
        $keyword = urldecode($tmp[1]);
        $from = '搜狗';
    } elseif (strstr($url, 'sm.cn')) { // 神马搜索
        preg_match("|sm.cn.+q=([^\\&]*)|is", $url, $tmp);
        $keyword = urldecode($tmp[1]);
        $from = '神马搜索';
    } elseif (strstr($url, 'weixinbridge.com')){ // 微信
        $keyword = '';
        $from = '微信';
    } elseif (strstr($url, 'bing.com')) { // bing搜索
        preg_match("|bing.com.+query=([^\\&]*)|is", $url, $tmp);
        $keyword = urldecode($tmp[1]);
        $from = '必应';
    }

    return array('keyword' => $keyword, 'from' => $from);
}

/**
 * 发送短信
 *
 * @return void
 */
function pushNoteQueue($info)
{
    /** 初始化redis */
    $redis = new \Redis();
    $redis->connect(C('REDIS_HOST'), C('REDIS_PORT'));

    /** 尝试次数 3次 */
    $info['attempts'] = 3;
    if (!$redis->lPush('noteQueue', json_encode($info))) {
        \Think\Log::write('写入短信队列错误{' . json_encode($info) . '}', 'ERR');
    }

    $redis->close();
}