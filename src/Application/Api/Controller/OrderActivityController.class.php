<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 支付  Dates: 2015-08-13
// +------------------------------------------------------------------------------------------ 

namespace Api\Controller;

use Think\Controller;

class OrderActivityController extends BaseController {

    /**
     * 重写ajaxReturn 解决跨域
     *
     * @return void
     */
    public function ajaxReturn($rst)
    {
        header("Access-Control-Allow-Origin:*");
        parent::ajaxReturn($rst);
    }

    /**
     * 用户下单
     *
     * @return void
     */
    public function add()
    {
        /** 返回内容 {status:状态, msg:错误信息, data:数据信息} */
        $rst = array();
        $get = I('get.');

        /** 手机号码 */
        if (empty($get['cellphone'])) {
            $rst['status'] = 0;
            $rst['msg'] = "手机号码不能为空！";
            $this->ajaxReturn($rst);
        }

        if (!preg_match("/^1[34578][0-9]{9}$/", $get['cellphone'])) {
            $rst['status'] = 0;
            $rst['msg'] = "手机号码格式错误！";
            $this->ajaxReturn($rst);
        }

        /** 验证码 */
/**         if (empty($get['code'])) {
            $rst['status'] = 0;
            $rst['msg'] = "验证码不能为空！";
            $this->ajaxReturn($rst);
        } */

        /** 短信验证 */
/**         if (S('code' . $get['cellphone']) != $get['code']) {
            $rst['status'] = 0;
            $rst['msg'] = "验证码错误！";
            $this->ajaxReturn($rst);
        } */

        /** 姓名 */
        if (empty($get['name'])) {
            $rst['status'] = 0;
            $rst['msg'] = "用户名不能为空！";
            $this->ajaxReturn($rst);
        }

        /** 地址 */
        if (empty($get['address'])) {
            $rst['status'] = 0;
            $rst['msg'] = "地址不能为空！";
            $this->ajaxReturn($rst);
        }

        /** 机型ID */
        if (empty($get['phone_id'])) {
            $rst['status'] = 0;
            $rst['msg'] = "请选择机型！";
            $this->ajaxReturn($rst);
        }

        /** 机型名称 */
        if (empty($get['phone_name'])) {
            $phone_name = M('phone')->where(array('id' => $get['phone_id']))->getField('name');
            $get['phone_name'] = $phone_name;
        }

        /** 省市区 */
        if (empty($get['province']) || empty($get['city']) || empty($get['county'])) {
            $rst['status'] = 0;
            $rst['msg'] = "地址数据错误！";
            $this->ajaxReturn($rst);
        }
        
        /** 用户信息 */
        $user = array();
        $user['name'] = $get['name'];
        $user['cellphone'] = $get['cellphone'];
        $user['address'] = $get['address'];
        $user['create_time'] = time();

        /** 开始事务 */
        M()->startTrans();

        /** 写入用户 */
        $customer_id = M('customer')->add($user);

        if ($customer_id === false) {
            M()->rollback();
            $rst['status'] = 0;
            $rst['msg'] = "数据写入错误！";
            $this->ajaxReturn($rst);
        }

        /** 订单信息 */
        $order = array();
        $order['phone_id'] = $get['phone_id'];
        $order['phone_name'] = $get['phone_name'];
        $order['province'] = $get['province'];
        $order['city'] = $get['city'];
        $order['county'] = $get['county'];
        $order['remark'] = !empty($get['remark']) ? $get['remark'] : '';
        $order['customer_id'] = $customer_id;
        /** 活动类型 1 一元活动体验 */
        $order['type'] = 1;
        /** 状态 -1 取消 0 预留 1 下单  6 已付款 */
        $order['status'] = 1;
        $order['create_time'] = time();

        /** 写入订单 */
        $order_id = M('order_activity')->add($order);

        if ($order_id === false) {
            M()->rollback();
            $rst['status'] = 0;
            $rst['msg'] = "数据写入错误！";
            $this->ajaxReturn($rst);
        } else {
            M()->commit();
            $rst['status'] = 1;
            $rst['data'] = array('id' => $order_id);
            $this->ajaxReturn($rst);
        }
    }

    /**
     * 用户下单 钢化膜 一元体验
     *
     * @return void
     */
    public function addOne()
    {
        /** 返回内容 {status:状态, msg:错误信息, data:数据信息} */
        $rst = array();
        $get = I('get.');

        /** 手机号码 */
        if (empty($get['cellphone'])) {
            $rst['status'] = 0;
            $rst['msg'] = "手机号码不能为空！";
            $this->ajaxReturn($rst);
        }

        if (!preg_match("/^1[34578][0-9]{9}$/", $get['cellphone'])) {
            $rst['status'] = 0;
            $rst['msg'] = "手机号码格式错误！";
            $this->ajaxReturn($rst);
        }

        /** 验证码 */
/**         if (empty($get['code'])) {
            $rst['status'] = 0;
            $rst['msg'] = "验证码不能为空！";
            $this->ajaxReturn($rst);
        } */

        /** 短信验证 */
/**         if (S('code' . $get['cellphone']) != $get['code']) {
            $rst['status'] = 0;
            $rst['msg'] = "验证码错误！";
            $this->ajaxReturn($rst);
        } */

        /** 姓名 */
        if (empty($get['name'])) {
            $rst['status'] = 0;
            $rst['msg'] = "用户名不能为空！";
            $this->ajaxReturn($rst);
        }

        /** 机型ID */
        if (empty($get['phone_id'])) {
            $rst['status'] = 0;
            $rst['msg'] = "请选择机型！";
            $this->ajaxReturn($rst);
        }

        /** 机型名称 */
        if (empty($get['phone_name'])) {
            $phone_name = M('phone')->where(array('id' => $get['phone_id']))->getField('name');
            $get['phone_name'] = $phone_name;
        }

        /** 省市区 */
        if (empty($get['province']) || empty($get['city']) || empty($get['county'])) {
            $rst['status'] = 0;
            $rst['msg'] = "地址数据错误！";
            $this->ajaxReturn($rst);
        }

        /** 用户信息 */
        $user = array();
        $user['name'] = $get['name'];
        $user['cellphone'] = $get['cellphone'];
        $user['create_time'] = time();

        /** 开始事务 */
        M()->startTrans();

        /** 写入用户 */
        $customer_id = M('customer')->add($user);

        if ($customer_id === false) {
            M()->rollback();
            $rst['status'] = 0;
            $rst['msg'] = "数据写入错误！";
            $this->ajaxReturn($rst);
        }

        /** 订单信息 */
        $order = array();
        $order['phone_id'] = $get['phone_id'];
        $order['phone_name'] = $get['phone_name'];
        $order['province'] = $get['province'];
        $order['city'] = $get['city'];
        $order['county'] = $get['county'];
        $order['remark'] = !empty($get['remark']) ? $get['remark'] : '';
        $order['customer_id'] = $customer_id;
        /** 活动类型 2 数据线 3 钢化膜*/
        $order['type'] = $get['type'];
        /** 状态 -1 取消 0 预留 1 下单  6 已付款 */
        $order['status'] = 1;
        $order['create_time'] = time();

        /** 写入订单 */
        $order_id = M('order_activity')->add($order);

        if ($order_id === false) {
            M()->rollback();
            $rst['status'] = 0;
            $rst['msg'] = "数据写入错误！";
            $this->ajaxReturn($rst);
        } else {
            M()->commit();
            $rst['status'] = 1;
            $rst['data'] = array('id' => $order_id);
            $this->ajaxReturn($rst);
        }
    }


    /**
     * 支付宝二维码
     *
     * @return void
     */
    public function aliQrcode()
    {
        /** 订单id */
        $id = I('get.id');

        if (empty($id)) {
            $rst['status'] = 0;
            $rst['msg'] = "参数错误！";
            $this->ajaxReturn($rst);
        }

        $map = array();
        $map['id'] = $id;
        $map['type'] = '1';
        $order = M('order_activity')->where($map)->find();

        if (empty($order)) {
            $rst['status'] = 0;
            $rst['msg'] = "未查询到当前订单！";
            $this->ajaxReturn($rst);
        }

        if ($order['status'] != 1) {
            $rst['status'] = 0;
            $rst['msg'] = "当前订单不在未付款状态！";
            $this->ajaxReturn($rst);
        }

        require_once(CONF_PATH . "alipay.config.php");
        require_once(VENDOR_PATH . "alipay/alipay_submit.class.php");
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($alipay_config['partner']),
            "seller_email" => trim($alipay_config['seller_email']),
            "payment_type" => "1",
            "notify_url" => "http://api.shanxiuxia.com/api/OrderActivity/aliAasyn",
            "return_url" => "http://m.weadoc.com",
            "out_trade_no" => $id,
            "subject" => '一元体验:' . $order['phone_name'],
            "total_fee" => 1,
            "body" => '一元体验:' . $order['phone_name'],
            "qr_pay_mode" => "0",
            "_input_charset" => trim(strtolower($alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        /** $html_text = $alipaySubmit->buildRequestForm($parameter, 'get', ''); */
        $url = $alipaySubmit->buildRequestUrl($parameter);
        /** 生成二维码 */
        $this->show("<img alt='扫码支付' src='" . U('api/OrderActivity/qrcode') . '?url=' . urlencode($url) . "' style='width:240px;height:240px;'/>");
    }

    /**
     * 支付宝连接地址
     * @author liyang
     * @return void
     */
    public function aliUrl()
    {
        /** 订单id */
        $id = I('get.id');

        if (empty($id)) {
            $rst['status'] = 0;
            $rst['msg'] = "参数错误！";
            $this->ajaxReturn($rst);
        }

        $map = array();
        $map['id'] = $id;
        $map['type'] = '1';
        $order = M('order_activity')->where($map)->find();

        if (empty($order)) {
            $rst['status'] = 0;
            $rst['msg'] = "未查询到当前订单！";
            $this->ajaxReturn($rst);
        }

        if ($order['status'] != 1) {
            $rst['status'] = 0;
            $rst['msg'] = "当前订单不在未付款状态！";
            $this->ajaxReturn($rst);
        }

        require_once(CONF_PATH . "alipay.config.wap.php");
        require_once(VENDOR_PATH . "alipay_wap/lib/alipay_submit.class.php");
        $parameter = array(
            "service" => "alipay.wap.create.direct.pay.by.user",
            "partner" => trim($alipay_config['partner']),
            "seller_id" => trim($alipay_config['seller_id']),
            "payment_type" => "1",
            "notify_url" => "http://api.shanxiuxia.com/api/OrderActivity/aliAasyn",
            "return_url" => "http://m.weadoc.com",
            "out_trade_no" => $id,
            "subject" => '一元体验:' . $order['phone_name'],
            "total_fee" => 1,
            "body" => '一元体验:' . $order['phone_name'],
            "_input_charset" => trim(strtolower($alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, 'post', '');
        echo $html_text;
    }


    /**
     * 支付宝异步回调
     *
     * @return void
     */
    public function aliAsyn()
    {
        require_once(CONF_PATH . "alipay.config.php");
        require_once(APP_PATH . 'Api/Library/Vendor/'. "alipay/alipay_notify.class.php");

        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if ($verify_result) {
            // 支付宝交易号
            $trade_status = $_POST['trade_status'];

            if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                $map = array();
                $map['id'] = $_POST['out_trade_no'];

                $data = array();
                $data['status'] = 6;
                $data['clearing_time'] = time();
                /** 付款方式  支付方式 1 支付宝 2 微信*/
                $data['payment'] = 1;
                /** 淘宝交易号 */
                $data['thirdparty_number'] = $_POST['trade_no'];
                /** 买家账号 */
                $data['thirdparty_account'] = $_POST['buyer_email'];
                
                if (M('order_activity')->where($map)->save($data) === false) {
                    \Think\Log::record('一元体验淘宝异步通知错误[' . json_encode($_POST) . ']', 'ERR');
                }
            }
            echo "success";
        } else {
            echo "fail";
        }
    }

    /**
     * 微信二维码
     *
     * @return void
     */
    public function weixinQrcode()
    {
        /** 订单id */
        $id = I('get.id');

        if (empty($id)) {
            $rst['status'] = 0;
            $rst['msg'] = "参数错误！";
            $this->ajaxReturn($rst);
        }

        $map = array();
        $map['id'] = $id;
        $map['type'] = '1';
        $order = M('order_activity')->where($map)->find();

        if (empty($order)) {
            $rst['status'] = 0;
            $rst['msg'] = "未查询到当前订单！";
            $this->ajaxReturn($rst);
        }

        if ($order['status'] != 1) {
            $rst['status'] = 0;
            $rst['msg'] = "当前订单不在未付款状态！";
            $this->ajaxReturn($rst);
        }

        require_once(VENDOR_PATH . "weixin/lib/WxPay.Api.php");
        require_once(VENDOR_PATH . "weixin/bin/WxPay.NativePay.php");

        $notify = new \NativePay();
        $input = new \WxPayUnifiedOrder();

        /** 商品描述 */
        $input->SetBody('一元体验:' . $order['phone_name']);
        /** 商品详情 */
        $input->SetDetail('一元体验:' . $order['phone_name']);
        /** 订单号 */
        $input->SetOut_trade_no(\WxPayConfig::MCHID.date("YmdHis"));
        /** 订单总额 */
        $input->SetTotal_fee(100);
        /** 交易起始时间 */
        $input->SetTime_start(date("YmdHis"));
        /** 交易结束时间 */
        $input->SetTime_expire(date("YmdHis", time() + 600));
        /** 异步通知地址 */
        $input->SetNotify_url("http://api.shanxiuxia.com/api/OrderActivity/weixinAsyn");
        /** 交易类型 */
        $input->SetTrade_type("NATIVE");
        /** 商品ID */
        $input->SetProduct_id($order['id']);
        /** 订单编号 */
        $input->SetAttach($order['id']);

        $result = $notify->GetPayUrl($input);

        $url = $result["code_url"];

        /** 生成二维码 */
        $this->show("<img alt='扫码支付' src='" . U('api/OrderActivity/qrcode') . '?url=' . urlencode($url) . "' style='width:240px;height:240px;'/>");
    }

    /**
     * 微信js
     *
     * @return void
     */
        /**
     * JS 接口
     *
     * @return void
     */
    public function weixinJs()
    {
        /** 订单id */
        $id = I('get.id');
        /** code */
        $code = I('get.code');

        if (empty($id)) {
            $rst['status'] = 0;
            $rst['msg'] = "参数错误！";
            $this->ajaxReturn($rst);
        }

        $map = array();
        $map['id'] = $id;
        $map['type'] = '1';
        $order = M('order_activity')->where($map)->find();

        if (empty($order)) {
            $rst['status'] = 0;
            $rst['msg'] = "未查询到当前订单！";
            $this->ajaxReturn($rst);
        }

        if ($order['status'] != 1) {
            $rst['status'] = 0;
            $rst['msg'] = "当前订单不在未付款状态！";
            $this->ajaxReturn($rst);
        }

        require_once(VENDOR_PATH . "weixin/lib/WxPay.Api.php");
        require_once(VENDOR_PATH . "weixin/bin/WxPay.JsApiPay.php");

        $input = new \WxPayUnifiedOrder();
        $tools = new \JsApiPay();
        $openId = $tools->GetOpenidFromMp($code);

        /** 商品描述 */
        $input->SetBody('一元体验:' . $order['phone_name']);
        /** 商品详情 */
        $input->SetDetail('一元体验:' . $order['phone_name']);
        /** 订单号 */
        $input->SetOut_trade_no(\WxPayConfig::MCHID.date("YmdHis"));
        /** 订单总额 */
        $input->SetTotal_fee(100);
        /** 交易起始时间 */
        $input->SetTime_start(date("YmdHis"));
        /** 交易结束时间 */
        $input->SetTime_expire(date("YmdHis", time() + 600));
        /** 异步通知地址 */
        $input->SetNotify_url("http://api.shanxiuxia.com/api/OrderActivity/weixinAsyn");
        /** 交易类型 */
        $input->SetTrade_type("JSAPI");
        /** 商品ID */
        $input->SetProduct_id($order['id']);
        /** 订单编号 */
        $input->SetAttach($order['id']);
        /** openid */
        $input->SetOpenid($openId);

        $item = \WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($item);

        header("Access-Control-Allow-Origin:*");
        echo $jsApiParameters;
    }

    /**
     * 微信异步回调
     *
     * @return void
     */
    public function weixinAsyn()
    {
        require_once(VENDOR_PATH . "weixin/lib/WxPay.Data.php");
        require_once(VENDOR_PATH . "weixin/lib/WxPay.Api.php");
        $return = array();
        $replay = new \WxPayNotifyReply();

        //获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];

        //如果返回成功则验证签名
        try {
            $result = \WxPayResults::Init($xml);
        } catch (\WxPayException $e){
            $replay->SetReturn_code("FAIL");
            $replay->SetReturn_msg($e->errorMessage());
            \WxpayApi::replyNotify($replay->ToXml());
        }

        if ($result['result_code'] == 'SUCCESS') {
            $map = array();
            $map['id'] = $result['attach'];

            $data = array();
            $data['status'] = 6;
            $data['clearing_time'] = time();
            /** 付款方式  支付方式 1 支付宝 2 微信*/
            $data['payment'] = 2;
            /** 微信交易号 */
            $data['thirdparty_number'] = $result['out_trade_no'];
            
            if (M('order_activity')->where($map)->save($data) === false) {
                \Think\Log::record('一元体验微信异步通知错误(数据写入)[' . json_encode($_POST) . ']', 'ERR');
            }
        } else {
            \Think\Log::record('一元体验微信异步通知错误[' . json_encode($_POST) . ']', 'ERR');
            $replay->SetReturn_code("FAIL");
            $replay->SetReturn_msg('通知错误！');
            \WxpayApi::replyNotify($replay->ToXml());
        }
    }

    /**
     * 生成二维码
     *
     * @return void
     */
    public function qrcode($url)
    {
        require_once(VENDOR_PATH . "phpqrcode/phpqrcode.php");
        header('Content-Type: image/png');
        ob_clean();
        \QRcode::png($url);
    }

}
