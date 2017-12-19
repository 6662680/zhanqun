<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 来源统计 Dates: 2016-12-29
// +------------------------------------------------------------------------------------------

namespace Api\Controller;

use Think\Controller;

class SourceController extends Controller
{
    /**
     * 来源追踪
     *
     * @return void
     */
    public function trace()
    {
        $get = I('get.');
        $source = array();

        // 来源页面
        $source['origin'] = $get['referrer'];
        // 着路页面
        $source['dedark'] = $get['url'];
        // ip
        $source['ip'] = get_client_ip(0, 1);
        // 合作伙伴
        $engine = $this->searchEngine($get['referrer']);
        $source['partner'] = $engine['from'];
        // 关键词
        $source['keyword'] = $engine['keyword'];
        // 魔法词
        $source['magic'] = !empty($get['tommagic']) ? $get['tommagic'] : '';
        /** 着路时间 */
        $source['start_time'] = time();
        // ip来源地址 (后台根据ip获取)
        import('ORG.Net.IpLocation');// 导入IpLocation类
        $locate = new \Org\Net\IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
        $area = $locate->getlocation($source['ip']);
        $source['area'] = $area['country'];

        if (M('conversion')->add($source) === false) {
            \Think\Log::record('来源追踪写入错误[' . json_encode($get) . ']', ERR);
        }
        
        echo '';exit;
    }

    /**
     * 判断搜索引擎
     *
     * @return void
     */
    private function searchEngine($url)
    {
        $keyword ='';
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
}

/** <script>
var _maq = _maq || [];
_maq.push(['_setAccount', 'shanxiuxia']);
  
(function() {
    var ma = document.createElement('script'); ma.type = 'text/javascript'; ma.async = true;
    ma.src = ('https:' == document.location.protocol ? 'https://api.shanxiuxia.com/public/js' : 'http://api.shanxiuxia.com/public/js') + '/ss.js';
    var s = document.getElementsByTagName('script')[0]; 
    s.parentNode.insertBefore(ma, s);
})();
</script> */