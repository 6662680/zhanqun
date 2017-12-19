<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 短信操作类 Dates: 2015-09-23
// +------------------------------------------------------------------------------------------

namespace Org\Util;

class Note 
{
    /** 序列号 */
    private $sn = 'SDK-WSS-010-08860';
    /** 密码 */
    private $pwd = '8f1-7E9c';
    /** 主服务器地址 支持的端口包括：80，8060，8061对应相应服务，优先8060端口 */
    private $serverMaster = 'sdk.entinfo.cn:8060/webservice.asmx';
    /** 备用服务器地址 支持的端口包括：80，8060，8061对应相应服务，优先8060端口*/
    private $serverBackup = 'sdk2.entinfo.cn:8060/webservice.asmx';
    /** 端口号 */
    private $port = '8060';
    /** 错误信息 */
    public $errorMsg = '';


    /**
     * 构造发送内容
     *
     * @return void
     */
    private function buildParams($mobiles = '', $content = '', $time = '')
    {
        //请参考 'content'=>iconv( "UTF-8", "gb2312//IGNORE" ,'您好测试短信[XXX公司]'),//短信内容
        // Sn       软件序列号   是   格式XXX-XXX-XXX-XXXXX
        // Pwd      密码      是   md5(sn+password) 32位大写密文
        // Mobile   手机号     是   必填(支持10000个手机号,建议<=5000)多个英文逗号隔开 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
        // Content  内容      是   支持长短信(详细请看长短信扣费说明)提交短信记得加签名 如果是utf-8,转成GB2312 70个字一条
        // Ext      扩展码     否   例如：123（默认置空）
        // stime    定时时间    否   例如：2010-12-29 16:27:03（非定时置空）
        // Rrid     唯一标识    否   最长18位，只能是数字或者 字母 或者数字+字母的组合

        $argv = array( 
            'Sn' => $this->sn,
            'Pwd' => strtoupper(md5($this->sn . $this->pwd)),
            'Mobile' => '',
            'Content' => '',
            'Ext' => '',
            'Stime' => '',
            'Rrid' => ''
        );

        if (!empty($mobiles)) {
            $argv['Mobile'] = $mobiles;
        } else {
            $this->errorMsg = '参数错误-手机号为空';
            return false;
        }

        if (!empty($content)) {
            $argv['Content'] = iconv("UTF-8", "gb2312//IGNORE" , ($content . '[闪修侠]'));
        } else {
            $this->errorMsg = '参数错误-内容为空';
            return false;
        }

        if (!empty($time)) {
            $argv['Stime'] = $time;
        }

        // 构造字符串
        $flag = 0;
        $params = '';
        foreach ($argv as $key=>$value) {
            if ($flag != 0) { 
                $params .= "&"; 
                $flag = 1; 
            }

            $params .= $key . "="; 
            $params .= urlencode($value);

            $flag = 1; 
        }

        return $params;
    }

    /**
     * 发送短信
     *
     * @return void
     */
    public function send($mobiles = '', $content = '', $time = '')
    {
        $params = $this->buildParams($mobiles, $content, $time);

        if (!$params) {
            return false;
        }

        $length = strlen($params);

        //创建socket连接
        $fp = fsockopen($this->serverMaster, $this->port, $errno, $errstr, 10);

        if (!$fp) {
            $this->errorMsg = 'Master:' . $errstr . ":" . $errno;
            $fp = fsockopen($this->serverBackup, $this->port, $errno, $errstr, 10);

            if (!$fp) {
                $this->errorMsg = "\r\n" . 'Backup:' . $errstr . ":" . $errno;
                return fasle;
            }
        }

        //构造post请求的头 
        $header = "POST /webservice.asmx/mdSmsSend HTTP/1.1\r\n";
        $header .= "Host:sdk.entinfo.cn\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . $length . "\r\n";
        $header .= "Connection: Close\r\n\r\n";
        //添加post的字符串 
        $header .= $params . "\r\n";

        //发送post的数据
        fwrite($fp, $header);

        $line = '';
        while (!feof($fp)) {
            $line .= fgets($fp, 1024) . "\r\n";
        }

        fclose($fp);

        return $this->resultHandle($line);
    }

    /**
     * 结果处理
     *
     * @return void
     */
    private function resultHandle($line)
    {
        // <string xmlns="http://tempuri.org/">-5</string>
        $flag = preg_match("/\<string.*\>(.*)\<\//", $line, $result);

        if ($result[1] < 0) {
            $msg = "短信接口错误，错误码：" . $result[1];
            \Think\Log::record($msg, 'ERR');
            return false;
        } else {
            return true;
        }     
    }

}
// END Note class

// + --------------------------------------------
// + demo
// + --------------------------------------------
// + $note = new note();
// + $note->send('13336046996,15158113084', '这是测试内容：Hello World!~~~');
// + --------------------------------------------