<?php
 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 支付宝WAP  Dates: 2015-09-06
// +------------------------------------------------------------------------------------------ 

namespace Api\Controller;

use Think\Controller;

class AlipaywapController extends Controller
{
    /**
     * 支付处理
     *
     * @return void
     */
    public function handle()
    {
        /** 订单id */
        $id = I('get.orderId');
        $type = I('get.type');
    
        if (empty($id)) {
            $this->error('参数错误！');
        }
    
        $info = array();
        $map = array();
        $map['id'] = $id;
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

                /** 如果是预付款类型的订单, 如果没有付款, 则根据预付款生成二维码, 如果已经付款，则根据实际价格减去预付款生成二维码 */
                if ($order['pay_type'] == 2) {
                    /** 本次活动预付为全款 */
                    $info['price'] = $order['actual_price'];
                } else {
                    $info['price'] = $order['actual_price'];
                }

                $info['title'] = '维修:' . $info['name'][0]['name'];
                $info['number'] = $order['number'] . '_' . $id;
                $info['description'] = $info['title'];
                break;
            default:
                $order = M('order')->where($map)->find();
                $sql = "select p.name from `order` as o
                        left join order_phomal as opm on o.id=opm.order_id
                        left join phone_malfunction as pm on opm.phomal_id=pm.id
                        left join phone as p on pm.phone_id=p.id
                        where o.id={$id}";
                $info['name'] = M()->query($sql);
                $info['title'] = '维修:' . $info['name'][0]['name'];
                $info['number'] = $order['number'] . '_' . $id;
                $info['price'] = $order['actual_price'];
                $info['description'] = $info['title'];
                break;
        }
    
        if (empty($info)) {
            $this->error('未查询到当前订单！');
        } else {
            require_once(VENDOR_PATH . "alipaywap/conf/config.php");
            require_once(VENDOR_PATH . "alipaywap/service/AlipayTradeService.php");
            require_once(VENDOR_PATH . "alipaywap/buildermodel/AlipayTradeWapPayContentBuilder.php");
    
            $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
            $payRequestBuilder->setBody($info['description']);
            /** 订单名称，必填 */
            $payRequestBuilder->setSubject($info['title']);
            /** 商户订单号，商户网站订单系统中唯一订单号，必填 */
            $payRequestBuilder->setOutTradeNo($info['number']);
            /** 付款金额，必填 */
            $payRequestBuilder->setTotalAmount($info['price']);
            /** 超时时间 */
            $payRequestBuilder->setTimeExpress("1m");

            $payResponse = new \AlipayTradeService($config);
            $result=$payResponse->wapPay($payRequestBuilder,$config['return_url'], $config['notify_url']);
            return ;
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
        $id = I('get.orderId');
        $type = I('get.type');

        if (empty($id)) {
            $this->error('参数错误！');
        }

        $info = array();
        $map = array();
        $map['id'] = $id;
        /** $map['status'] = '5'; */

        $map = array();
        $map['pio.old_order_id'] = $id;
        $map['pio.number'] = I('get.number');
        $map['pio.status'] = 0;
        $order = M('phomal_insurance_order')->join('pio left join phomal_insurance pi on pi.id = pio.phomal_insurance_id')
            ->field('pio.number, pio.price, pi.title, pio.id')->where($map)->find();

        $info['title'] = '保险:' . $order['title'];
        $info['number'] = $order['number'] . '_' . $order['id'];
        $info['price'] = $order['price'];
        $info['description'] = $info['title'];


        if (empty($info)) {
            $this->error('未查询到当前订单！');
        } else {
            require_once(VENDOR_PATH . "alipaywap/conf/config.php");
            require_once(VENDOR_PATH . "alipaywap/service/AlipayTradeService.php");
            require_once(VENDOR_PATH . "alipaywap/buildermodel/AlipayTradeWapPayContentBuilder.php");

            $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
            $payRequestBuilder->setBody($info['description']);
            /** 订单名称，必填 */
            $payRequestBuilder->setSubject($info['title']);
            /** 商户订单号，商户网站订单系统中唯一订单号，必填 */
            $payRequestBuilder->setOutTradeNo($info['number']);
            /** 付款金额，必填 */
            $payRequestBuilder->setTotalAmount($info['price']);
            /** 超时时间 */
            $payRequestBuilder->setTimeExpress("1m");

            $payResponse = new \AlipayTradeService($config);
            $result=$payResponse->wapPay($payRequestBuilder,$config['return_url'], $config['notify_url']);
            return ;
        }
    }

    /**
     * 保险支付处理
     *
     * @return void
     */
    public function insurance()
    {
        /** 订单id */
        $id = I('get.id');
        $number = I('get.number');

        if (empty($id)) {
            $this->error('参数错误！');
        }

        $info = array();
        $map = array();
        $map['id'] = $id;
        /** $map['status'] = '5'; */

        $map = array();
        $map['pio.old_order_id'] = $id;
        $map['pio.number'] = $number;
        $map['pio.status'] = 0;
        $order = M('phomal_insurance_order')->join('pio left join phomal_insurance pi on pi.id = pio.phomal_insurance_id')
            ->field('pio.number, pio.price, pi.title')->where($map)->find();

        $info['title'] = '保险:' . $order['title'];
        $info['number'] = $order['number'] . '_' . $id;
        $info['price'] = $order['price'];
        $info['description'] = $info['title'];

        if (empty($info)) {
            $this->error('未查询到当前订单！');
        } else {
            require_once(VENDOR_PATH . "alipaywap/conf/config.php");
            require_once(VENDOR_PATH . "alipaywap/service/AlipayTradeService.php");
            require_once(VENDOR_PATH . "alipaywap/buildermodel/AlipayTradeWapPayContentBuilder.php");

            $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
            $payRequestBuilder->setBody($info['description']);
            /** 订单名称，必填 */
            $payRequestBuilder->setSubject($info['title']);
            /** 商户订单号，商户网站订单系统中唯一订单号，必填 */
            $payRequestBuilder->setOutTradeNo($info['number']);
            /** 付款金额，必填 */
            $payRequestBuilder->setTotalAmount($info['price']);
            /** 超时时间 */
            $payRequestBuilder->setTimeExpress("1m");

            $payResponse = new \AlipayTradeService($config);
            $result=$payResponse->wapPay($payRequestBuilder,$config['return_url'], $config['notify_url']);
            return ;
        }
    }

    /**
     * 支付成功 - 异步处理
     *
     * @return void
     */
    public function notify()
    {
        require_once(VENDOR_PATH . "alipaywap/conf/config.php");
        require_once(VENDOR_PATH . "alipaywap/service/AlipayTradeService.php");

        $arr=$_POST;
        $alipaySevice = new \AlipayTradeService($config); 
        $alipaySevice->writeLog(var_export($_POST,true));
        $result = $alipaySevice->check($arr);

        if ($result) {
            // 商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            // 支付宝交易号
            $trade_no = $_POST['trade_no'];
            // 交易状态
            $trade_status = $_POST['trade_status'];
            /** 买家账号 */
            $buyer_email = $_POST['buyer_id'];
            // 金额
            $price = $_POST['total_amount'];
            
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
     * 成功页面
     *
     * @return void
     */
    public function successPage()
    {
        $this->show("<h1>支付成功！</h1>");
    }

    /**
     * 失败页面
     *
     * @return void
     */
    public function errorPage()
    {
        $this->show("<h1>支付失败！</h1>");
    }

}