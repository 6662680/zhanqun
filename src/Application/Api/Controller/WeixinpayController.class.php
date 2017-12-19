<?php
 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 微信支付  Dates: 2015-08-20
// +------------------------------------------------------------------------------------------ 

namespace Api\Controller;
use Think\Controller;

class WeixinpayController extends Controller
{
    /**
     * 支付处理
     *
     * @return void
     */
    public function handle()
    {
        /** 订单id */
        $id = I('get.id');
        /** 订单编号 */
        $number = I('get.number');
        /**优惠券号码*/
        $coupon = I('get.coupon');
        /** 类型 W：维修 N：换新 D：新单 I:保险*/
        $type = I('get.type');
        /** 输出二维码方式 0-默认 1-数据流*/
        $showType = I('get.show_type');

        if (empty($id) || empty($number)) {
            $this->error('参数错误！');
        }

        $info = array();
        $map = array();
        $map['id'] = $id;
        $map['number'] = $number;
        $map['status'] = '5';

        switch ($type) {
            case 'W':
                //D('Coupon')->used( $coupon , $number , $id );    /** 查看优惠券是否使用，如使用修改数据库订单实际金额 */

                $order = M('order')->where($map)->find();
                $sql = "select p.name from `order` as o 
                        left join order_phomal as opm on o.id=opm.order_id 
                        left join phone_malfunction as pm on opm.phomal_id=pm.id 
                        left join phone as p on pm.phone_id=p.id 
                        where o.id={$id}";
                $info['name'] = M()->query($sql);

                $info['title'] = '维修:' . $info['name'][0]['name'];
                $info['number'] = $order['number'] . '_' . $id;
                $info['price'] = (int) $order['actual_price'];
                $info['description'] = $info['title'];
                break;
            case 'I':
                $map = array();
                $map['pio.id'] = $id;
                $map['pio.number'] = $number;
                $map['pio.status'] = 0;
                $order = M('phomal_insurance_order')->join('pio left join phomal_insurance pi on pi.id = pio.phomal_insurance_id')
                         ->field('pio.number, pio.price, pi.title')->where($map)->find();
                 
                $info['title'] = '保险:' . $order['title'];
                $info['number'] = $order['number'] . '_' . $id;
                $info['price'] = $order['price'];
                $info['description'] = $info['title'];
                break;
            default:
                $this->error('参数错误！');
                break;
        }

        if (empty($order)) {
            $this->error('未查询到当前订单！');
        } else {
            require_once(VENDOR_PATH . "weixin/lib/WxPay.Api.php");
            require_once(VENDOR_PATH . "weixin/bin/WxPay.NativePay.php");

            $notify = new \NativePay();
            $input = new \WxPayUnifiedOrder();

            /** 商品描述 */
            $input->SetBody($info['title']);
            /** 商品详情 */
            $input->SetDetail($info['description']);
            /** 订单号 */
            $input->SetOut_trade_no(\WxPayConfig::MCHID.date("YmdHis"));
            /** 订单总额 */
            $input->SetTotal_fee(round($info['price'] * 100));
            /** 交易起始时间 */
            $input->SetTime_start(date("YmdHis"));
            /** 交易结束时间 */
            $input->SetTime_expire(date("YmdHis", time() + 600));
            /** 异步通知地址 */
            $input->SetNotify_url("http://api.shanxiuxia.com/Api/Weixinpay/handle_asyn");
            /** 交易类型 */
            $input->SetTrade_type("NATIVE");
            /** 商品ID */
            $input->SetProduct_id($info['number']);
            /** 订单编号 */
            $input->SetAttach($info['number']);

            $result = $notify->GetPayUrl($input);
            $url = $result["code_url"];

            /** 生成二维码 */
            if ($showType) {
                require_once(VENDOR_PATH . "phpqrcode/phpqrcode.php");
                \QRcode::png($url);
            } else {
                $this->show("<img alt='扫码支付' src='" . U('Api/weixinpay/qrcode') . '?url=' . urlencode($url) . "' style='width:150px;height:150px;'/>");
            }
        }
    }

    /**
     * 生成二维码
     *
     * @return void
     */
    public function qrcode()
    {
        $url = I('get.url');
        require_once(VENDOR_PATH . "phpqrcode/phpqrcode.php");
        \QRcode::png($url);
    }

    /**
     * 支付请求处理(微信内发起)
     *
     * @return void
     */
    public function payRequest()
    {
        require_once(VENDOR_PATH . "weixin/lib/WxPay.Api.php");
        require_once(VENDOR_PATH . "weixin/bin/WxPay.JsApiPay.php");

        $tools = new \JsApiPay();
        $openId = $tools->GetOpenid();

        /** 订单id */
        $id = I('get.id');
        /** 订单编号 */
        $number = I('get.number');
        /** 类型 W：维修 N：换新 D：新单 I:保险*/
        $type = I('get.type');
        /** code */
        $code = $openId;

        if (empty($id) || empty($number) || empty($type)) {
            $this->error('参数错误！');
        }

        $info = array();
        $map = array();
        $map['id'] = $id;
        $map['number'] = $number;
        /** $map['status'] = '5'; */

        switch ($type) {
            case 'W':
                $order = M('order')->where($map)->find();
                $sql = "select p.name from `order` as o 
                        left join order_phomal as opm on o.id=opm.order_id 
                        left join phone_malfunction as pm on opm.phomal_id=pm.id 
                        left join phone as p on pm.phone_id=p.id 
                        where o.id={$id}";
                $info['name'] = M()->query($sql);

                $info['title'] = '维修:' . $info['name'][0]['name'];
                $info['number'] = $order['number'] . '_' . $id;
                $info['description'] = $info['title'];

                /** 如果是预付款类型的订单, 如果没有付款, 则根据预付款生成二维码, 如果已经付款，则根据实际价格减去预付款生成二维码 */
                if ($order['pay_type'] == 2) {
                    /** 本次活动预付为全款 */
                    $info['price'] = $order['actual_price'];
                } else {
                    $info['price'] = $order['actual_price'];
                }
                break;
            case 'I':
                $map = array();
                $map['pio.id'] = $id;
                $map['pio.number'] = $number;
                $map['pio.status'] = 0;
                $order = M('phomal_insurane_order')->join('pio left join phomal_insurance pi on pi.id = pio.phomal_insurance_id')
                         ->field('pio.number, pio.price, pi.title')->where($map)->find();
                 
                $info['title'] = '保险:' . $order['title'];
                $info['number'] = $order['number'] . '_' . $id;
                $info['price'] = $order['price'];
                $info['description'] = $info['title'];
                break;
            default:
                $this->error('参数错误！');
                break;
        }

        if (empty($order)) {
            $this->error('未查询到当前订单！');
        } else {
            $input = new \WxPayUnifiedOrder();

            /** 商品描述 */
            $input->SetBody($info['title']);
            /** 商品详情 */
            $input->SetDetail($info['description']);
            /** 订单号 */
            $input->SetOut_trade_no(\WxPayConfig::MCHID.date("YmdHis"));
            /** 订单总额 */
            $input->SetTotal_fee(round($info['price'] * 100));
            /** 交易起始时间 */
            $input->SetTime_start(date("YmdHis"));
            /** 交易结束时间 */
            $input->SetTime_expire(date("YmdHis", time() + 600));
            /** 异步通知地址 */
            $input->SetNotify_url("http://api.shanxiuxia.com/Api/Weixinpay/handle_asyn");
            /** $input->SetNotify_url("http://testadmin.shanxiuxia.com/Api/Weixinpay/handle_asyn"); */
            /** 交易类型 */
            $input->SetTrade_type("JSAPI");
            /** 商品ID */
            $input->SetProduct_id($info['number']);
            /** 订单编号 */
            $input->SetAttach($info['number']);
            /** openid */
            $input->SetOpenid($openId);

            $order = \WxPayApi::unifiedOrder($input);
            
            $jsApiParameters = $tools->GetJsApiParameters($order);

            $this->assign('info', $info);
            $this->assign('jsApiParameters', $jsApiParameters);
            $this->display();
        }
    }

    /**
     * 统一下单(微信回调)
     *
     * @return void
     */
    public function unifiedOrder()
    {
        
    }

    /**
     * 生成签名
     *
     * @return 签名
     */
    private function mkSign($data)
    {
        //签名步骤一：按字典序排序参数
        ksort($data);

        $string = '';

        foreach ($data as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $string .= $k . "=" . $v . "&";
            }
        }

        $string = trim($string, "&");

        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . \WxPayConfig::KEY;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);

        return $result;
    }

    /**
     * 支付成功 - 异步处理
     *
     * @return void
     */
    public function handle_asyn()
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

        if ($result['return_code'] == 'SUCCESS') {
            //商户订单号
            $out_trade_no = $result['attach'];
            //交易号
            $trade_no = $result['out_trade_no'];
            //交易状态
            $trade_status = $result['result_code'];
            //交易金额
            $price = ($result['total_fee'] / 100);
            
            $numberArr = explode('_', $out_trade_no);

            if ($trade_status == 'SUCCESS') {
                /** W150813114855nvw_xx */
                $map = array();
                $data = array();
                $type = substr($out_trade_no, 0 , 1);

                switch ($type) {
                    case 'W':
                        $map['id'] = $numberArr[1];
                        $map['number'] = $numberArr[0];
                        $order = M('order')->where($map)->find();

                        $data['id'] = $numberArr[1];
                        $data['number'] = $numberArr[0];
                        $data['is_clearing'] = 1;
                        $data['payment_method'] = 2;
                        $data['third_party_number'] = $trade_no; 

                        /** 如果是预计款订单，可能多次付款 付款交易号，付款账号使用逗号隔开*/
                        if ($order['pay_type'] == 2) {
                            /** 已付金额 */
                            $data['paid_amount'] = $price;
                            D('Admin/order')->stock($data);
                            D('Admin/order')->writeLog($numberArr[1], '用户微信付款[微信-third_party_number：' . $trade_no . '], 付款金额[' . $price . ']');
                        } elseif (empty($order['third_party_number'])) {
                            $data['status'] = 6;
                            D('Admin/order')->stock($data);
                            D('Admin/order')->writeLog($numberArr[1], '用户微信付款[微信-third_party_number：' . $trade_no . '], 付款金额[' . $price . ']');
                        }

                        \Think\Log::record('错误查找(order_id:' . $numberArr[1] . '-支付方式:微信-third_party_number:' . $trade_no .'-time:'.date('Y-m-d H:i:s').')', 'ERR');
                        break;
                    case 'I': //保险支付通知页面
                    
                        $param = array(
                            'id' => $numberArr[1],
                            'number' => $numberArr[0],
                            'pay_account' => '',
                            'pay_number' => $trade_no,
                            'pay_price' => $price,
                        );
                    
                        \Think\Log::record('错误查找(insurance_id:' . $numberArr[1] . '-支付方式:微信-third_party_number:' . $trade_no .'-time:'.date('Y-m-d H:i:s').')', 'ERR');
                        D('Admin/phomalInsurance')->payInsuranceOrder($param);
                        break;
                    default:
                        \Think\Log::record('错误查找(order_id:' . $numberArr[1] . '-支付方式:微信-third_party_number:' . $trade_no .'-time:'.date('Y-m-d H:i:s').')', 'ERR');
                        break;
                }
                
                $replay->SetReturn_code("SUCCESS");
                $replay->SetReturn_msg('OK');
                \WxpayApi::replyNotify($replay->ToXml());
            } else {
                \Think\Log::record('订单写入错误：' . $out_trade_no, 'ERR');
            }
        } else {
            $replay->SetReturn_code("FAIL");
            $replay->SetReturn_msg('通知错误！');
            \WxpayApi::replyNotify($replay->ToXml());
        }
    }

    /**
     * 支付验证
     *
     * @return void
     */
    public function verify()
    {
        /** 订单id */
        $id = I('post.id');
        /** 订单编号 */
        $number = I('post.number');

        if (empty($id) || empty($number)) {
            $this->error('参数错误！');
        }
        
        /** 类型 W：维修 N：换新 D：新单 I:保险*/
        $type = I('post.type');

        $result = array();
        $order = array();
        $map = array();
        $map['id'] = $id;
        $map['number'] = $number;
        $map['status'] = '6';
        $map['is_clearing'] = '1';

        switch ($type) {
            case 'W':
                $order = M('order')->where($map)->find();
                break;
            case 'N':
                $order = M('tradein')->where($map)->find();
                break;
            case 'D':
                $order = M('order_buy')->where($map)->find();
                break;
            case 'I':
                unset($map['is_clearing']);
                $map['status'] = array('gt', 0);
                $order = M('phomal_insurance_order')->where($map)->find();
                break;
        }

        if (!$order) {
            $result['status'] = 0;
            $result['msg'] = '未查询到订单！';
        } else {
            $result['status'] = 1;
        }

        echo json_encode($result);
    }

    /**
     * 第三方合作
     *
     * @return void
     */
    private function thirdParty($orderId)
    {
        $partner = M('order_partner')->where(array('order_id' => $orderId))->getField('partner');
        D('ThirdParty')->factory($partner, 'deal', array('orderId' => $orderId));
    }

    /**
     * 退款
     *
     * @return void
     */
    public function refund($order, $param)
    {
        require_once(VENDOR_PATH . "weixin/lib/WxPay.Api.php");

        $input = new WxPayRefund();
        /** 商户订单号 */
        $input->SetOut_trade_no($order['number'] . '_' . $order['id']);
        /** 订单金额 */
        $input->SetTotal_fee($order['actual_price'] * 100);
        /** 退款金额 */
        $input->SetRefund_fee($param['refund_amount'] * 100);
        $input->SetOut_refund_no($param['batch_no']);
        $input->SetOp_user_id(WxPayConfig::MCHID);

        $refundRst = WxPayApi::refund($input);

        if ($refundRst['return_code'] == 'SUCCESS') {

            if ($refundRst['result_code'] == 'SUCCESS') {
                /** 退款日志 */
                $row = array();
                $row['order_id'] = $order['id'];
                $row['order_number'] = $order['number'];
                $row['refund_time'] = time();
                $row['refund_amount'] = $param['refund_amount'];
                $row['third_party_no'] = $refundRst['refund_id'];
                $row['refund_way'] = $order['payment_method'];
                $row['user_id'] = session('userId');
                $row['refund_result'] = 1;
                $row['third_party_info'] = json_encode($refundRst);
                $row['batch_no'] = $param['batch_no'];

                if (M('order_refund')->add($row) === false) {
                    \Think\Log::record('退款异步通知错误(微信)[日志写入错误]{' . json_encode($refundRst) . '}', 'ERR');
                    return false;
                }
            } else {
                \Think\Log::record('退款异步通知错误(微信){' . json_encode($refundRst) . '}', 'ERR');
                return false;
            }

            return true;
        } else {
            \Think\Log::record('退款异步通知错误(微信){' . json_encode($refundRst) . '}', 'ERR');
            return false;
        }
    }

    /**
     * 支付处理
     *
     * @return void
     */
    public function handlenew()
    {
        /** 订单id */
        $id = I('get.id');
        /** 订单编号 */
        $number = I('get.number');
        /**优惠券号码*/
        $coupon = I('get.coupon');
        /** 类型 W：维修 N：换新 D：新单 I:保险*/
        $type = I('get.type');
        /** 输出二维码方式 0-默认 1-数据流*/
        $showType = I('get.show_type');

        if (empty($id) || empty($number)) {
            $this->error('参数错误！');
        }

        $info = array();
        $map = array();
        $map['id'] = $id;
        $map['number'] = $number;
        $map['status'] = '5';

        switch ($type) {
            case 'W':
                //D('Coupon')->used( $coupon , $number , $id );    /** 查看优惠券是否使用，如使用修改数据库订单实际金额 */

                $order = M('order')->where($map)->find();
                $sql = "select p.name from `order` as o
                        left join order_phomal as opm on o.id=opm.order_id
                        left join phone_malfunction as pm on opm.phomal_id=pm.id
                        left join phone as p on pm.phone_id=p.id
                        where o.id={$id}";
                $info['name'] = M()->query($sql);

                $info['title'] = '维修:' . $info['name'][0]['name'];
                $info['number'] = $order['number'] . '_' . $id;
                $info['price'] = (int) $order['actual_price'];
                $info['description'] = $info['title'];
                break;
            case 'I':
                $map = array();
                $map['pio.id'] = $id;
                $map['pio.number'] = $number;
                $map['pio.status'] = 0;
                $order = M('phomal_insurance_order')->join('pio left join phomal_insurance pi on pi.id = pio.phomal_insurance_id')
                    ->field('pio.number, pio.price, pi.title')->where($map)->find();

                $info['title'] = '保险:' . $order['title'];
                $info['number'] = $order['number'] . '_' . $id;
                $info['price'] = $order['price'];
                $info['description'] = $info['title'];
                break;
            default:
                $this->error('参数错误！');
                break;
        }

        if (empty($order)) {
            $this->error('未查询到当前订单！');
        } else {
            require_once(VENDOR_PATH . "weixin/lib/WxPay.Api.php");
            require_once(VENDOR_PATH . "weixin/bin/WxPay.NativePay.php");

            $notify = new \NativePay();
            $input = new \WxPayUnifiedOrder();

            /** 商品描述 */
            $input->SetBody($info['title']);
            /** 商品详情 */
            $input->SetDetail($info['description']);
            /** 订单号 */
            $input->SetOut_trade_no(\WxPayConfig::MCHID.date("YmdHis"));
            /** 订单总额 */
            $input->SetTotal_fee(round($info['price'] * 100));
            /** 交易起始时间 */
            $input->SetTime_start(date("YmdHis"));
            /** 交易结束时间 */
            $input->SetTime_expire(date("YmdHis", time() + 600));
            /** 异步通知地址 */
            $input->SetNotify_url("http://api.shanxiuxia.com/Api/Weixinpay/handle_asyn");
            /** 交易类型 */
            $input->SetTrade_type("NATIVE");
            /** 商品ID */
            $input->SetProduct_id($info['number']);
            /** 订单编号 */
            $input->SetAttach($info['number']);

            $result = $notify->GetPayUrl($input);
            $url = $result["code_url"];

            /** 生成二维码 */
            if ($showType) {
                require_once(VENDOR_PATH . "phpqrcode/phpqrcode.php");
                \QRcode::png($url);
            } else {
                $this->show("<img alt='扫码支付' src='" . U('Api/weixinpay/qrcode') . '?url=' . urlencode($url) . "' style='width:150px;height:150px;'/>");
            }
        }
    }

    /**
     * 支付请求处理(微信内发起)
     *
     * @return void
     */
    public function payRequestnew()
    {
        require_once(VENDOR_PATH . "weixin/lib/WxPay.Api.php");
        require_once(VENDOR_PATH . "weixin/bin/WxPay.JsApiPay.php");

        $tools = new \JsApiPay();
        $openId = $tools->GetOpenid();

        /** 订单id */
        $id = I('get.id');
        /** 订单编号 */
        $number = I('get.number');
        /** 类型 W：维修 N：换新 D：新单 I:保险*/
        $type = I('get.type');
        /** code */
        $code = $openId;

        if (empty($id) || empty($number) || empty($type)) {
            $this->error('参数错误！');

        }

        $info = array();
        $map = array();
        $map['id'] = $id;
        $map['number'] = $number;
        /** $map['status'] = '5'; */

        switch ($type) {
            case 'W':
                $order = M('order')->where($map)->find();
                $sql = "select p.name from `order` as o
                        left join order_phomal as opm on o.id=opm.order_id
                        left join phone_malfunction as pm on opm.phomal_id=pm.id
                        left join phone as p on pm.phone_id=p.id
                        where o.id={$id}";
                $info['name'] = M()->query($sql);

                $info['title'] = '维修:' . $info['name'][0]['name'];
                $info['number'] = $order['number'] . '_' . $id;
                $info['description'] = $info['title'];

                /** 如果是预付款类型的订单, 如果没有付款, 则根据预付款生成二维码, 如果已经付款，则根据实际价格减去预付款生成二维码 */
                if ($order['pay_type'] == 2) {
                    /** 本次活动预付为全款 */
                    $info['price'] = $order['actual_price'];
                } else {
                    $info['price'] = $order['actual_price'];
                }
                break;
            case 'I':
                $map = array();
                $map['pio.old_order_id'] = $id;
                $map['pio.number'] = $number;
                $map['pio.status'] = 0;

                $order = M('phomal_insurance_order')->join('pio left join phomal_insurance pi on pi.id = pio.phomal_insurance_id')
                    ->field('pio.number, pio.price, pi.title, pio.id')->where($map)->find();

                $info['title'] = '保险:' . $order['title'];
                $info['number'] = $order['number'] . '_' . $order['id'];
                $info['price'] = $order['price'];
                $info['description'] = $info['title'];
                break;
            default:
                $this->error('参数错误！');
                break;
        }

        if (empty($order)) {
            $this->error('未查询到当前订单！');
        } else {
            $input = new \WxPayUnifiedOrder();

            /** 商品描述 */
            $input->SetBody($info['title']);
            /** 商品详情 */
            $input->SetDetail($info['description']);
            /** 订单号 */
            $input->SetOut_trade_no(\WxPayConfig::MCHID.date("YmdHis"));
            /** 订单总额 */
            $input->SetTotal_fee(round($info['price'] * 100));
            /** 交易起始时间 */
            $input->SetTime_start(date("YmdHis"));
            /** 交易结束时间 */
            $input->SetTime_expire(date("YmdHis", time() + 600));
            /** 异步通知地址 */
            $input->SetNotify_url("http://api.shanxiuxia.com/Api/Weixinpay/handle_asyn");
            /** $input->SetNotify_url("http://testadmin.shanxiuxia.com/Api/Weixinpay/handle_asyn"); */
            /** 交易类型 */
            $input->SetTrade_type("JSAPI");
            /** 商品ID */
            $input->SetProduct_id($info['number']);
            /** 订单编号 */
            $input->SetAttach($info['number']);
            /** openid */
            $input->SetOpenid($openId);

            $order = \WxPayApi::unifiedOrder($input);

            $jsApiParameters = $tools->GetJsApiParameters($order);

            $this->assign('info', $info);
            $this->assign('jsApiParameters', $jsApiParameters);
            $this->display();
        }
    }
}