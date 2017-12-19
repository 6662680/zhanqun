<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 短信操作类 (阿里) Dates: 2016-02-26
// +------------------------------------------------------------------------------------------

namespace Vendor\aliNote;

class aliNote 
{
    /** App Key */
    private $appkey = '23315424';
    /** 密码 */
    private $appSecret = 'a1909069fe8ec22f04844d3ab7da9a2b';
    /** 服务器地址 */
    private $server = 'http://gw.api.taobao.com/router/rest';
    /** 返回信息格式 */
    private $format = 'json';
    /** 错误信息 */
    public $errorMsg = '';


    /**
     * 发送短信
     *
     * @return void
     */
    public function send($mobiles = '', $content = '', $template = '')
    {
        include "TopSdk.php";
        
        $c = new \TopClient;
        $c->appkey = $this->appkey;
        $c->secretKey = $this->appSecret;
        $c->format = $this->format;
       
        /** $msg = array();
        $msg['code'] = '123abc';
        $msg['product'] = '闪修侠'; */

        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req->setSmsType("normal");
        $req->setSmsFreeSignName("闪修侠");
        $req->setSmsParam(json_encode($content));
        /** $req->setRecNum("13336046996"); */
        $req->setRecNum($mobiles);
        /** $req->setSmsTemplateCode("SMS_5024350"); */
        $req->setSmsTemplateCode($template);

        $rst = $c->execute($req);

        $this->resultHandle($rst);
        return $rst;
    }

    /**
     * 结果处理
     *
     * @return void
     */
    private function resultHandle($rst)
    {
        if ($rst->result->success) {
            return true;
        } else {
            $msg = "短信接口错误，code:" . $rst->code . ";msg:" . $rst->msg . ";sub_code:" . $rst->sub_code . ";sub_msg:" . $rst->sub_msg . ";request_id:" . $rst->request_id;
            \Think\Log::write($msg, 'ERR');
            return false;
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