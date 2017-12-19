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

class PayController extends Controller
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

        if (empty($id) || empty($number)) {
            $this->error('参数错误！');
        }

        /** 类型 W：维修 N：换新 D：新单 I：保险*/
        $type = I('get.type');

        $info = array();
        $map = array();
        $map['id'] = $id;
        $map['number'] = $number;
        $map['status'] = '5';

        switch ($type) {
            case 'W':
                /** 查看优惠券是否使用，如使用修改数据库订单实际金额 */
                //D('Coupon')->used($coupon, $number, $id);

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

        if (empty($info)) {
            $this->error('未查询到当前订单！');
        } else {
            header("Content-type: text/html; charset=utf-8");
            require_once(CONF_PATH . "alipay.config.php");
            require_once(VENDOR_PATH . "alipay/alipay_submit.class.php");

            /**************************请求参数**************************/
            //构造要请求的参数数组，无需改动
            $parameter = array(
                "service" => "create_direct_pay_by_user",
                "partner" => trim($alipay_config['partner']),
                "seller_email" => trim($alipay_config['seller_email']),
                "payment_type" => "1",
                "notify_url" => "http://api.shanxiuxia.com/Api/pay/handle_asyn",
                "return_url" => "http://api.shanxiuxia.com/Api/pay/mySuccess",
                "out_trade_no" => $info['number'],
                "subject" => $info['title'],
                "total_fee"    => $info['price'],
                "body" => $info['description'],
                "_input_charset" => trim(strtolower($alipay_config['input_charset']))
            );

            //建立请求
            $alipaySubmit = new \AlipaySubmit($alipay_config);
            $html_text = $alipaySubmit->buildRequestForm($parameter, 'post', '');
            echo $html_text;
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
     * 支付成功 - 异步处理
     *
     * @return void
     */
    public function handle_asyn()
    {
        require_once(CONF_PATH . "alipay.config.php");
        require_once(VENDOR_PATH . "alipay/alipay_notify.class.php");

        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if ($verify_result) {
            // 商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            // 支付宝交易号
            $trade_no = $_POST['trade_no'];
            // 交易状态
            $trade_status = $_POST['trade_status'];
            /** 买家账号 */
            $buyer_email = $_POST['buyer_email'];
            // 金额
            $price = $_POST['total_fee'];
            
            $numberArr = explode('_', $out_trade_no);

            if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
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
                        $data['payment_method'] = 1;
                        $data['third_party_number'] = $trade_no;
                        $data['buyer_email'] = $buyer_email;

                        /** 如果是预计款订单，可能多次付款 付款交易号，付款账号使用逗号隔开*/
                        if ($order['pay_type'] == 2) {
                            /** 已付金额 */
                            $data['paid_amount'] = $price;
                            D('Admin/order')->stock($data);
                            D('Admin/order')->writeLog($numberArr[1], '用户支付宝付款[buyer_email：' . $buyer_email . '], 付款金额[' . $price . ']');
                        } elseif (empty($order['third_party_number'])) {
                            $data['status'] = 6;
                            D('Admin/order')->stock($data);
                            D('Admin/order')->writeLog($numberArr[1], '用户支付宝付款[buyer_email：' . $buyer_email . '], 付款金额[' . $price . ']');
                        }
                        
                        \Think\Log::record('错误查找(order_id:' . $numberArr[1] . '-支付方式:支付宝-third_party_number:' . $trade_no .'-buyer_email:'.$buyer_email.'-time:'.date('Y-m-d H:i:s').')', 'ERR');
                        break;
                    case 'N':
                        $model = 'tradein';
                        $data['payment'] = 1;
                        break;
                    case 'D':
                        $model = 'order_buy';
                        $data['payment'] = 1;
                        break;
                    case 'I': //保险支付通知页面
                        $param = array(
                            'id' => $numberArr[1],
                            'number' => $numberArr[0],
                            'pay_account' => $buyer_email,
                            'pay_number' => $trade_no,
                            'pay_price' => $price,
                        );
                        
                        \Think\Log::record('错误查找(insurance_id:' . $numberArr[1] . '-支付方式:支付宝-third_party_number:' . $trade_no .'-buyer_email:'.$buyer_email.'-time:'.date('Y-m-d H:i:s').')', 'ERR');
                        D('Admin/phomalInsurance')->payInsuranceOrder($param);
                        break;
                    default:
                        \Think\Log::record('错误查找(order_id:' . $numberArr[1] . '-支付方式:支付宝-third_party_number:' . $trade_no .'-buyer_email:'.$buyer_email.'-time:'.date('Y-m-d H:i:s').')', 'ERR');
                        break;
                }
            }

            echo "success";
        } else {
            echo "fail";
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
        
        /** 类型 W：维修 N：换新 D：新单 I：保险*/
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
     * 成功页面
     *
     * @return void
     */
    public function mySuccess()
    {
        require_once(CONF_PATH . "alipay.config.php");
        require_once(VENDOR_PATH . "alipay/alipay_notify.class.php");

        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();

        if ($verify_result) {
            $out_trade_no = $_GET['out_trade_no'];
            $trade_no = $_GET['trade_no'];
            $trade_status = $_GET['trade_status'];
            $buyer_email = $_GET['buyer_email'];
            $price = $_GET['total_fee'];
            $type = substr($out_trade_no, 0 , 1);
            $numberArr = explode('_', $out_trade_no);

            if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                /** W150813114855nvw_xx */
                $map = array();
                $data = array();
                
                switch ($type) {
                    case 'W':
                        $map['id'] = $numberArr[1];
                        $map['number'] = $numberArr[0];
                        $order = M('order')->where($map)->field('status, third_party_number')->find();
                        $third_party_number = $order['third_party_number'];
                        
                        if ($order['status'] != 6) {
                            D('Admin/order')->writeLog($numberArr[1], '用户支付宝付款[buyer_email：' . $buyer_email . ']');
                        }
                        
                        $data['id'] = $numberArr[1];
                        $data['number'] = $numberArr[0];
                        $data['status'] = 6;
                        $data['is_clearing'] = 1;
                        $data['payment_method'] = 1;
                        $data['third_party_number'] = $trade_no;
                        $data['buyer_email'] = $buyer_email;
                        
                        if (empty($third_party_number)) {
                            \Think\Log::record('错误查找(order_id:' . $numberArr[1] . '-支付方式:支付宝-third_party_number:' . $trade_no .'-buyer_email:'.$buyer_email.'-time:'.date('Y-m-d H:i:s').')', 'ERR');
                            
                            D('Admin/order')->stock($data);
                        }
                        break;
                    case 'N':
                        $model = 'tradein';
                        $data['payment'] = 1;
                        break;
                    case 'D':
                        $model = 'order_buy';
                        $data['payment'] = 1;
                        break;
                    case 'I': //保险支付通知页面
                    
                        $param = array(
                            'id' => $numberArr[1],
                            'number' => $numberArr[0],
                            'pay_account' => $buyer_email,
                            'pay_number' => $trade_no,
                            'pay_price' => $price,
                        );
                    
                        \Think\Log::record('错误查找(insurance_id:' . $numberArr[1] . '-支付方式:支付宝-third_party_number:' . $trade_no .'-buyer_email:'.$buyer_email.'-time:'.date('Y-m-d H:i:s').')', 'ERR');
                        
                        D('Admin/phomalInsurance')->payInsuranceOrder($param);
                        break;
                    default:
                        $map['id'] = $numberArr[1];
                        $map['number'] = $numberArr[0];
                        $order = M('order')->where($map)->field('status, third_party_number')->find();
                        $third_party_number = $order['third_party_number'];
                        
                        if ($order['status'] != 6) {
                            D('Admin/order')->writeLog($numberArr[1], '用户支付宝付款[buyer_email：' . $buyer_email . ']');
                        }
                        
                        $data['id'] = $numberArr[1];
                        $data['number'] = $numberArr[0];
                        $data['status'] = 6;
                        $data['is_clearing'] = 1;
                        $data['payment_method'] = 1;
                        $data['third_party_number'] = $trade_no;
                        $data['buyer_email'] = $buyer_email;
                        
                        if (empty($third_party_number)) {
                            \Think\Log::record('错误查找(order_id:' . $numberArr[1] . '-支付方式:支付宝-third_party_number:' . $trade_no .'-buyer_email:'.$buyer_email.'-time:'.date('Y-m-d H:i:s').')', 'ERR');
                            
                            D('Admin/order')->stock($data);
                        }
                        break;
                }
            } else {
                echo "trade_status=" . $_GET['trade_status'];
            }
            $this->assign('orderId',$numberArr[1]);
            $this->assign('type',$type);
            $this->assign('orderId',$numberArr[1]);
            /** 支付成功 */
            $this->assign('status',"succeed");
        } else {
            $this->assign('type',$type);
            /** 支付失败 */
            $this->assign('status',"fild");
        }

        //尝鲜联盟,http://www.100what.com/cxlm/wiki/rcmfromleague.php
        $apiUser = "cxlm-20160108-sxx";
        $apiKey = "uibmvibfn1va3elzrecg2echk1if9zx7";
        $url = 'http://www.100what.com/league/api/getRcmFromLeague';

        $param = http_build_query(
            array(
                'apiuser'=>$apiUser,
                'apikey'=>$apiKey,
                'wechat'=>1,
                'type'=>1
            )
        );

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$param);
        $rcmDataFromLeagueResult = curl_exec($ch);
        curl_close($ch);

        $rcmDataFromLeague = json_decode($rcmDataFromLeagueResult,true);

        if(!$rcmDataFromLeague['errcode']){
            $this->assign('rcmDataFromLeague',$rcmDataFromLeague);
        }

        exit('支付成功');
        //$this->display();
    }

    /**
     * 失败页面
     *
     * @return void
     */
    public function myError()
    {
        /** 查询订单 */
        /** 跳转到结算页面 */
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
    public function refund($param)
    {
        require_once(CONF_PATH . "alipay.config.php");
        require_once(VENDOR_PATH . "alipayrefund/alipay_submit.class.php");

        /** 退款笔数，必填，参数detail_data的值中，“#”字符出现的数量加1，最大支持1000笔（即“#”字符出现的数量999个） */
        $batch_num = 1;

        /** 退款详细数据，必填，格式（支付宝交易号^退款金额^备注），多笔请用#隔开 (2011011201037066^5.00^协商退款) */
        $detail_data = $param['third_party_number'] . '^' . $param['refund_amount'] . '^' . '协商退款';

        $parameter = array(
            "service" => 'refund_fastpay_by_platform_pwd',
            "partner" => $alipay_config['partner'],
            "notify_url" => 'http://api.shanxiuxia.com/index.php/api/pay/refundNotify',
            "seller_email" => $alipay_config['seller_email'],
            "refund_date" => date('Y-m-d H:i:s'),
            "batch_no" => $param['batch_no'],
            "batch_num" => $batch_num,
            "detail_data" => $detail_data,
            "_input_charset" => $alipay_config['input_charset']
        );

        /** 发起请求 */
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $rst = $alipaySubmit->buildRequestHttp($parameter);
        return $rst;
    }

    /**
     * 退款异步通知
     *
     * @return void
     */
    public function refundNotify()
    {
        require_once("alipay.config.php");
        require_once("lib/alipay_notify.class.php");

        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if ($verify_result) {
            //批量退款数据中的详细信息
            $result_details = $_POST['result_details'];
            $result_details_array = explode('^', $result_details);

            $map = array();
            $map['batch_no'] = $_POST['batch_no'];
            $map['third_party_no'] = $result_details_array[0];
 
            /** 退款日志 */
            $row = array();
            $row['refund_result'] = ($result_details_array[2] == 'SUCCESS') ? 1 : -1;
            $row['third_party_info'] = json_encode($_POST);

            if (M('order_refund')->where($map)->save($row) === false) {
                \Think\Log::record('退款异步通知错误(支付宝)[日志写入错误]{' . json_encode($_POST) . '}', 'ERR');
                echo "fail";
            } else {
                echo "success";
            }
        } else {
            echo "fail";
            \Think\Log::record('退款异步通知错误(支付宝){' . json_encode($_POST) . '}', 'ERR');
        }
    }

}