<?php
namespace Api\Controller;
use Api\Model\CheckModel;
use Api\Model\OrderModel;
use Api\Model\OrderVerificationModel;
use Api\Model\RequestModel;
use Think\Controller;
/**
 * 订单控制器
 * author :liyang
 * time : 2016-8-4
 */

class OrderController extends BaseController
{

    /**
     * 订单支付宝二维码
     *
     * @return void
     */
    public function alipayQrcode()
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
            require_once(CONF_PATH . "alipay.config.php");
            require_once(VENDOR_PATH . "alipay/alipay_submit.class.php");
    
            /**************************请求参数**************************/
            //构造要请求的参数数组，无需改动
            $parameter = array(
                            "service" => "create_direct_pay_by_user",
                            "partner" => trim($alipay_config['partner']),
                            "seller_email" => trim($alipay_config['seller_email']),
                            "payment_type" => "1",
                            "notify_url" => "http://api.shanxiuxia.com/api/pay/handle_asyn",
                            /** "notify_url" => "http://testadmin.shanxiuxia.com/api/pay/handle_asyn", */
                            "return_url" => "http://api.shanxiuxia.com/api/pay/mySuccess",
                            "out_trade_no" => $info['number'],
                            "subject" => $info['title'],
                            "total_fee" => $info['price'],
                            "body" => $info['description'],
                            "qr_pay_mode" => "4",
                            "qrcode_width" => '150',
                            "_input_charset" => trim(strtolower($alipay_config['input_charset']))
            );
    
            //建立请求
            $alipaySubmit = new \AlipaySubmit($alipay_config);
            $html_text = $alipaySubmit->buildRequestForm($parameter, 'post', '');
            echo $html_text;exit;
            /** $url = $alipaySubmit->buildRequestUrl($parameter); */
            /** 生成二维码 */
            /** $this->show("<img alt='扫码支付' src='" . U('api/order/qrcode') . '?appkey=9dc5de36dc343fb5ae1e86863150cc82&url=' . urlencode($url) . "' style='width:240px;height:240px;'/>"); */
        }
    }
    
    /**
     * 订单微信二维码
     *
     * @return void
     */
    public function weixinQrcode()
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
            $input->SetNotify_url("http://api.shanxiuxia.com/api/Weixinpay/handle_asyn");
            /** $input->SetNotify_url("http://testadmin.shanxiuxia.com/api/Weixinpay/handle_asyn"); */
            /** 交易类型 */
            $input->SetTrade_type("NATIVE");
            /** 商品ID */
            $input->SetProduct_id($info['number']);
            /** 订单编号 */
            $input->SetAttach($info['number']);
    
            $result = $notify->GetPayUrl($input);
            $url = $result["code_url"];
    
            /** 生成二维码 */
            $this->show("<img alt='扫码支付' src='" . U('api/order/qrcode') . '?appkey=9dc5de36dc343fb5ae1e86863150cc82&url=' . urlencode($url) . "' style='width:150px;height:150px;'/>");
        }
    }

    /**
     * 订单支付: 支付宝，微信
     *
     * @return void
     */
    public function pay()
    {
        /** 订单id */
        $id = I('get.orderId');
        $type = I('get.payType');

        if (empty($id) || empty($type)) {
            $this->error('参数错误！');
        }

        $info = array();
        $map = array();
        $map['id'] = $id;
        $map['status'] = '5';

        $info = M('order')->where($map)->find();

        $sql = "select p.name from `order` as o
                        left join order_phomal as opm on o.id=opm.order_id
                        left join phone_malfunction as pm on opm.phomal_id=pm.id
                        left join phone as p on pm.phone_id=p.id
                        where o.id={$id}";
        $info['name'] = M()->query($sql);



        $info['title'] = '维修:' . $info['name'][0]['name'];
        $info['number'] = $info['number'] . '_' . $id;
        $info['description'] = $info['title'];

        if ($info['status'] != 5) {
            $this->error('无效的订单');
        } else {

            switch ($type) {
                case '1':
                require_once(CONF_PATH . "alipay.config.php");
                require_once(VENDOR_PATH . "alipay/alipay_submit.class.php");

                /**************************请求参数**************************/
                //构造要请求的参数数组，无需改动
                $parameter = array(
                    "service" => "create_direct_pay_by_user",
                    "partner" => trim($alipay_config['partner']),
                    "seller_email" => trim($alipay_config['seller_email']),
                    "payment_type" => "1",
                    "notify_url" => "http://api.shanxiuxia.com/api/pay/handle_asyn",
                    /** "notify_url" => "http://testadmin.shanxiuxia.com/api/pay/handle_asyn", */
                    "return_url" => "http://api.shanxiuxia.com/api/pay/mySuccess",
                    "out_trade_no" => $info['number'],
                    "subject" => $info['title'],
                    "total_fee" => $info['actual_price'],
                    "body" => $info['description'],
                    "qr_pay_mode" => "4",
                    "qrcode_width" => '150',
                    "_input_charset" => trim(strtolower($alipay_config['input_charset']))
                );

                //建立请求
                $alipaySubmit = new \AlipaySubmit($alipay_config);
                $html_text = $alipaySubmit->buildRequestForm($parameter, 'post', '');
                echo $html_text;exit;
                break;
            default:
                case '2':
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
                $input->SetNotify_url("http://api.shanxiuxia.com/api/Weixinpay/handle_asyn");
                /** $input->SetNotify_url("http://testadmin.shanxiuxia.com/api/Weixinpay/handle_asyn"); */
                /** 交易类型 */
                $input->SetTrade_type("NATIVE");
                /** 商品ID */
                $input->SetProduct_id($info['number']);
                /** 订单编号 */
                $input->SetAttach($info['number']);

                $result = $notify->GetPayUrl($input);
                $url = $result["code_url"];

                /** 生成二维码 */
                $this->show("<img alt='扫码支付' src='" . U('api/order/qrcode') . '?appkey=9dc5de36dc343fb5ae1e86863150cc82&url=' . urlencode($url) . "' style='width:150px;height:150px;'/>");
                break;
            }

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
    
    /**
     * 用户下单
     * @author liyang
     * @return void
     */
    public function add()
    {
        $data = I('post.');
        $status = array();
        $model = D('order');
        $verModel = D('orderVerification');

        /*表单验证*/
        if (!$verModel->verification($data)) {
            $status = array('code' => 403, 'msg' => $verModel->errorInfo['msg']);
            \Think\Log::write('下单数据错误:' . $status['msg'] . '. 错误数据[' . json_encode($_POST) . ']','ERR');
            $this->_error('503', $status['msg']);
        }

        /*获取公司详细地址*/
        if ($data['category'] == 1) {
            $data['companyAddressInfo'] = $model->category($data['city']);
        }
        
        $data['address'] = D('address')->idAddress($data['province']);
        $data['address'] .= D('address')->idAddress($data['city']);
        $data['address'] .= D('address')->idAddress($data['area']);
        $data['address'] .= $data['detailed_address'];
        /* 开启事务 */
        M()->startTrans();

        /* 添加用户 */
        $customer_id = $model->addCustomer($data);

        if (!$customer_id) {
            $status = array('code' => 503, 'msg' => '用户数据无法添加');
            $this->_error('503', $status['msg']);
        }

        $data['customer_id'] = $customer_id;

        /* 添加订单 */
        $addOrderInfo = $model->orderModel($data);

        if ($addOrderInfo['status'] == false) {
            M()->rollback();
            $status = array('code' => 503, 'msg' => $addOrderInfo['msg']);
            $this->_error('503', $status['msg']);
        } else {
            M()->commit();
        }
        
//        if ($addOrderInfo['status'] && $data['category'] == 1) {
//            $this->autoSend($addOrderInfo['orderId'], $data);
//        }

        /* 发送短信 不要求返回值 加快返回速度 @todo 引入短信队列 保证可靠性 */
        /** $model->isCategory($data, $addOrderInfo); */

        /** 短信队列 */
        $this->pushNoteQueue($addOrderInfo);

        $this->_callBack($addOrderInfo);
    }

    /**
     * 写入队列
     *
     * @return void
     */
    public function pushNoteQueue($orderInfo)
    {
        /** 初始化redis */
        $redis = new \Redis();
        $redis->connect(C('REDIS_HOST'), C('REDIS_PORT'));

        $data = array();
        $data['orderId'] = $orderInfo['orderId'];
        $data['orderNumber'] = $orderInfo['orderNumber'];
        /** 尝试次数 3次 */
        $data['attempts'] = 3;

        if (!$redis->lPush('noteQueue', json_encode($data))) {
            \Think\Log::write('写入短信队列错误{' . json_encode($data) . '}', 'ERR');
        }

        $redis->close();
    }

    /**
     * 发送短信验证码
     * @author liyang
     * @return void
     */
    public function sendSms()
    {
/**         $mobile = I('post.mobile');
        if (empty($mobile)) {
            die($this->_error(403,'非法访问'));
        }

        $model = D('order');
        $code = $model->generate_code();
        $sms = new \Vendor\aliNote\aliNote();
        $result = $sms->send($mobile,['product'=>$mobile,'code'=>strval($code)],'SMS_5024351');
        if ($result) {
            S('code'.$mobile,$code,1800);
            $this->_callBack();
        } else {
            $this->_error(503,'短信发送失败');
        } */

        return false;
    }

    /**
     * 对比验证码
     * @author liyang
     * @return void
     */
    public function vcode()
    {
        if(S('code'.I('post.mobile')) == I('post.code')){
            $this->_callBack();
        } else {
            $this->_error(401,'验证码错误');
        }
    }



    /**
     * 统计单量 * 2
     * @author liyang
     * @return void
     */
    public function countOrder()
    {
        $num = M('order')->count() * 2;
        $this->_callBack(['num' => $num]);

    }

    public function test(){
        $orderInfo = M('order')->where(array('engineer_id' => 0, 'status' => 1, 'category'=> 1))->order('id desc')->find();
        $this->autoSend($orderInfo['id'], $orderInfo);

    }

    /**
     * 自动派单
     * 
     * @return void
     */
    public function autoSend($orderId, $orderInfo, $isFitting = true, $limitOrderAmount = true)
    {
        $rst = array();
        
        $amap = new \Org\Util\Amap();
        
        $location = $amap->geo($orderInfo['address']);

        if (!$location) {
            $this->sendFail($orderId, '自动派单失败:(无法根据地址获取地理坐标)');
        } else {
            
            /** 范围筛选 1km 3km 10km*/
            
            $radius = array(1000, 3000, 10000);
            
            foreach ($radius as $m) {
                
                $filtrate = $amap->around($location, $m);

                if (!$filtrate['count']) {
                    continue;
                }

                $engineerIds = array();
                
                foreach ($filtrate['datas'] as $key => $value) {
                    $engineerId = $value['userid'];
                
                    /** 判断是否接单 */
                    if (!$this->isWork($engineerId)) {
                        continue;
                    }
                
                    /** 判断物料 */
                    if ($isFitting && !$this->compareFittings($engineerId, $orderId)) {
                        continue;
                    }
                
                    /** 判断订单数量 */
                    if ($limitOrderAmount && !$this->checkOrderAmount($engineerId)) {
                        continue;
                    }
                
                    $engineerIds[] = $engineerId;
                }
                
                if (count($engineerIds) == 1) {
                    $engineerId = end($engineerIds);
                } elseif (count($engineerIds) > 1) {
                    $engineerId = $this->compareOrderCount($engineerIds);
                } else {
                    continue;
                }
                
                $rst = $this->send($orderId, $engineerId, 1, true);
                break;
            }
            
            if ($rst) {
                
                if (!$rst['success']) {
                    D('Admin/order')->writeLog($orderId, $rst['errorMsg']);;
                }
            } else {
                $this->sendFail($orderId, '自动派单失败:(未查询到符合条件的工程师)');
            }
        }
    }
    
    /**
     * 派单
     * 
     * @param   int     $orderId    订单ID
     * @param   array   $engineerId 工程师ID
     * @param   int     $mode       派单模式 1-普通模式  2-强制模式
     * @param   bool    $isAuto     是否自动派单
     * @return  void
     */
    public function send($orderId, $engineerId, $mode = 1, $isAuto = false)
    {
        $rst = array();
        $map = array();
        $map['id'] = $orderId;
        $order = M('order')->where($map)->field('status, number')->find();
        
        if (!$order) {
            $rst['success'] = false;
            $rst['errorMsg'] = "订单不存在！";
            return $rst;
        }
        
        $where = array();
        $where['id'] = $engineerId;
        $engineer = M('engineer')->where($where)->find();

        if (!$engineer) {
            $rst['success'] = false;
            $rst['errorMsg'] = "工程师不存在！";
            return $rst;
        }
        
        if (!in_array($order['status'], array(1, 11, 12))) {
            $rst['success'] = false;
            $rst['errorMsg'] = "订单状态不是下单、取回或改约，不能进行派单！";
            return $rst;
        }
        
        //普通模式:如果物料不符或工程师不在接单状态，不进行派送，返回错误消息
        //强制模式:无论如何都订单派送给工程，返回错误消息
        
        $rst['success'] = true;
        
        if (!$isAuto) {
            
            if (!$this->isWork($engineerId)) {
                $rst['success'] = false;
                $rst['errorMsg'] .= ' 工程师不在接单状态!';
            }
            
            if (!$this->compareFittings($engineerId, $orderId)) {
                $rst['success'] = false;
                $rst['errorMsg'] .= ' 工程师物料不符!';
            }
        }
        
        $isSend = false;

        if (($mode == 1 && $rst['success']) || ($mode == 2)) {
            $data = array();
            $data['is_send'] = 1;
            $data['status'] = 3;
            $data['engineer_id'] = $engineerId;
            $data['receiving_time'] = time();
            $isSend = M('order')->where($map)->save($data);
        }
        
        $isPush = false;
        
        if ($isSend) {
            $data = array();
            $data['order_id'] = $orderId;
            $data['time'] = time();
            $data['action'] = '订单'.($isAuto ? '自动' : '手动').'派单成功！('.(session('userId') ? '操作人：'.session('userInfo.username') : '').' 工程师：'.$engineer['name'].', '.$engineer['cellphone'].')';
            M('order_log')->add($data);
            
            /** 发送推送信息 */
            $isPush = $this->pushOrderInfo($engineer, $orderId);

            /** 发送短信 */
            $cellphone = $engineer['cellphone'];
            $this->sendNote($cellphone, $order['number']);
        }
        
        if ($isSend)
        {
            $rst['success'] = true;
            
            $msg = ($isAuto ? '自动' : '手动')."派单成功！";
            
            if ($isPush) {
                $msg .= "订单推送成功！ ";
            } else {
                $msg .= "订单推送失败！ ";
            }
            
            $rst['errorMsg'] = $msg . (isset($rst['errorMsg']) ? $rst['errorMsg'] : '');
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = ($isAuto ? '自动' : '手动').'派单失败(写入数据失败)！';
        }
        
        return $rst;
    }
    
    /**
     * 派单失败
     *
     * @param int $orderId 订单ID
     * @param string $logMessage 日志信息
     * @return void
     */
    private function sendFail($orderId, $logMessage = '自动派单失败！')
    {
        /** 派单状态 */
        $map = array();
        $map['id'] = $orderId;
        $data = array();
        $data['is_send'] = -1;
        M('order')->where($map)->save($data);
    
        /** 订单日志 */
        $data = array();
        $data['order_id'] = $orderId;
        $data['time'] = time();
        $data['action'] = $logMessage;
        M('order_log')->add($data);
    }
    
    /**
     * 物料对比
     *
     * @param int $engineer_id 工程师ID
     * @param int $order_id 订单ID
     * @return void
     */
    private function compareFittings($engineer_id, $order_id)
    {
        $colorId = M('order')->where(array('id' => $order_id))->getField('color_id');
        
        $malfunction_list = M('order_phomal')->join('op left join phone_malfunction pm on op.phomal_id = pm.id')
                           ->field('pm.fitting, pm.is_color')
                           ->where(array('op.order_id' => $order_id))->select();
    
        $fittingIds = array();
        
        foreach ($malfunction_list as $malfunction) {
            // 没有颜色，按原来的json解析
            if (!$malfunction['is_color']) {
                $malfunction_fittings = json_decode($malfunction['fitting'], true);
                
                foreach ($malfunction_fittings as $fitting) {
                    $fittingIds[$fitting['id']] = $fitting['id'];
                }
            } else {
                // 有颜色，读取订单的color_id去required_part里面去取相应的颜色值
                $mal_list = json_decode($malfunction['fitting'], true);
    
                foreach ($mal_list[$colorId]['items'] as $fitting) {
                    $fittingIds[$fitting['id']] = $fitting['id'];
                }
            }
        }
        
        if (!empty($fittingIds)) {
            /** 工程师物料库 */
            $map = array();
            $map['engineer_id'] = $engineer_id;
            $map['fittings_id'] = array('in', array_values($fittingIds));
            $map['amount'] = array('gt', 0);
            $count = M('engineer_warehouse')->where($map)->count();
    
            if (count($fittingIds) != $count) {
                return false;
            }
        }
    
        return true;
    }
    
    /**
     * 当前工程是否接单
     *
     * @param int $engineerId 工程师ID
     * @return void
     */
    private function isWork($engineerId)
    {
        $map = array();
        $map['id'] = $engineerId;
        $map['status'] = 1;
        $isWork = M('engineer')->where($map)->getField('is_work');
    
        if ($isWork == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 现有订单数量核对
     *
     * @param int $engineerId 工程师ID
     * @return void
     */
    private function checkOrderAmount($engineerId)
    {
        /** 当前未完成付款的订单 */
        $limit = '8';
    
        $map = array();
        $map['engineer_id'] = $engineerId;
        $map['status'] = array('in', array(3, 4, 5));
    
        if (M('order')->where($map)->count() >= $limit) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * 订单数量对比
     *
     * @param array $engineers 工程师ID集合
     * @return void
     */
    private function compareOrderCount($engineerIds)
    {
        /** 当前订单数量 (3~4) */
        $map = array();
        $map['o.status'] = array('in', array(3, 4));
        $map['e.id'] = array('in', $engineerIds);
        $engineerCount = M('engineer')->join('e left join `order` o on e.id=o.engineer_id')
                        ->field('e.id as engineer_id, count(o.id) as count')
                        ->where($map)->group('e.id')->order('count asc')->select();
    
        if (count($engineerCount) > 0) {
            
            if (($engineerCount[0]['count'] < $engineerCount[1]['count'])) {
                return $engineerCount[0]['engineer_id'];
            } else {
                $minCount = $engineerCount[0]['count'];
                $engineerIds = array();
                
                foreach ($engineerCount as $key => $value) {
                    
                    if ($value['count'] == $minCount) {
                        
                        $engineerIds[] = $value['engineer_id'];
                    }
                }
                
                unset($engineerCount);
            }
        }
    
        $year = date('Y');
        $month = date('m');
        $days = date('t');
        $date = date('d');
        $week = date('w');
    
        /** 本周 */
        $thisWeekStart = mktime(0, 0, 0, $month, ($date - $week + 1), $year);
        $thisWeekEnd = mktime(23, 59, 59, $month, ($date - $week + 7), $year);
    
        /** 本周订单数量 (>= 6) */
        $map = array();
        $map['o.status'] = array('egt', 6);
        $map['e.id'] = array('in', $engineerIds);
        $map['o.create_time'] = array(array('gt', $thisWeekStart), array('lt', $thisWeekEnd), 'AND');
        $engineerCount = M('engineer')->join('e left join `order` o on e.id=o.engineer_id')
                        ->field('e.id as engineer_id, count(o.id) as count')
                        ->where($map)->group('e.id')->order('count asc')->select();
    
        if (count($engineerCount) > 0) {
            
            if (($engineerCount[0]['count'] < $engineerCount[1]['count'])) {
                return $engineerCount[0]['engineer_id'];
            } else {
                $minCount = $engineerCount[0]['count'];
                $engineerIds = array();
                
                foreach ($engineerCount as $key => $value) {
                    
                    if ($value['count'] == $minCount) {
                        
                        $engineerIds[] = $value['engineer_id'];
                    }
                }
                
                unset($engineerCount);
            }
        }
    
        /** 本月 */
        $thisMonthStart = mktime(0, 0, 0, $month, 1, $year);
        $thisMonthEnd = mktime(23, 59, 59, $month, $days, $year);
    
        /** 本月订单数量 (>= 6)  订单ID大小 */
        $map = array();
        $map['o.status'] = array('egt', 6);
        $map['e.id'] = array('in', $engineerIds);
        $map['o.create_time'] = array(array('gt', $thisMonthStart), array('lt', $thisMonthEnd), 'AND');
        $engineerCount = M('engineer')->join('e left join `order` o on e.id=o.engineer_id')
                        ->field('e.id as engineer_id, count(o.id) as count')
                        ->where($map)->group('e.id')->order('count asc')->select();
    
        if ((count($engineerCount) > 0) && (($engineerCount[0]['count'] < $engineerCount[1]['count']))) {
            return $engineerCount[0]['engineer_id'];
        } else {
            @ksort($engineerIds);
            return $engineerIds[0];
        }
    }
    
    /**
     * 推送订单
     *
     * @param array $engineerInfo 工程师信息
     * @param string $orderId 订单ID
     * @param int $attempts 尝试次数(默认3次)
     * @return void
     */
    public function pushOrderInfo($engineerInfo = array(), $orderId = '', $attempts = 0)
    {
        if (!$engineerInfo || $orderId < 0) {
            return false;
        }

        $jpush = new \Vendor\Jpush\Jpush();

        if (!empty($engineerInfo['registration_id']) && strpos($engineerInfo['registration_id'], '(null)') === false && $jpush->push($engineerInfo['registration_id'], $orderId)) {

            /** 派单状态 */
            $map = array();
            $map['id'] = $orderId;
            $data = array();
            $data['is_push'] = 1;
            M('order')->where($map)->save($data);
    
            /** 订单日志 */
            $data = array();
            $data['order_id'] = (int)$orderId;
            $data['time'] = time();
            $data['action'] = '订单推送成功！('.$engineerInfo['name'].', '.$engineerInfo['cellphone'].')';
    
            M('order_log')->add($data);
            return true;
        } elseif ($attempts > 0) {
            /** 订单日志 */
            $data = array();
            $data['order_id'] = (int)$orderId;
            $data['time'] = time();
            $data['action'] = '订单推送失败！('.$engineerInfo['name'].', '.$engineerInfo['cellphone'].')';
            M('order_log')->add($data);

            /** 间隔一段时间后尝试 3s */
            /** sleep(3); */

            return $this->pushOrderInfo($engineerInfo, $orderId, $attempts - 1);
        }

        if ($attempts < 1) {
            return false;
        }
    }
    
    /**
     * 订单短信通知
     *
     * @param string $phoneId 手机号码
     * @param string $orderNumber 订单编号
     * @return void
     */
    private function sendNote($number, $orderNumber)
    {
        /** $note = new \Org\Util\Note();
         $msg = '你有新的闪修侠订单! 订单编号：' . $orderNumber;
         $note->send($phoneId, $msg); */
    
        $msg = array();
        $msg['orderNumber'] = $orderNumber;
    
        $note = new \Vendor\aliNote\aliNote();
        return $note->send($number, $msg, 'SMS_5190047');
    }
    
    /**
     * 客户查询订单接口
     * @param  string cellphone
     */
    public function orders()
    {
        $cellphone = trim(I('post.cellphone'));
        $rst = array();
        
        if (!$cellphone) {
            $rst['status'] = 1;
            $rst['data'] = array();
            $this->_callBack($rst);
        }
        
        $baseUrl = 'http://api.shanxiuxia.com';
        
        $map = array('o.cellphone' => $cellphone);
        
        $data = array();
        
        //保险
        $map = array('pio.cellphone' => $cellphone);
        $list = M('phomal_insurance_order')->join('pio left join phomal_insurance pi on pio.phomal_insurance_id = pi.id')
                ->join('left join `phone` p on p.id = pi.phone_id')
                ->join('left join `order` o on o.id = pio.old_order_id')
                ->field('pio.id, pio.number, pio.price, pio.cellphone, pio.status, pio.effect_time, pio.failure_time, 
                    pio.create_time, pio.old_order_id, p.alias as phone_name, p.img, pio.broken_flag, pio.remark, o.color as color_name')
                ->where($map)->order('pio.id desc')->select();
        
        $insurance = array();
        $pio_status = array(
            '-2' => '取消',
            '-1' => '取消',
            '0' => '未付款',
            '1' => '已付款',
            '2' => '在保中',
            '3' => '理赔中',
            '4' => '已过期',
            '5' => '服务完成',
        );
        $pio_color = array(
            '-2' => '#d2d2d2',
            '-1' => '#d2d2d2',
            '0' => '#d2d2d2',
            '1' => '#80c269',
            '2' => '#80c269',
            '3' => '#F37B46',
            '4' => '#d2d2d2',
            '5' => '#ffac1b',
        );
        
        $time = time();
        
        foreach ($list as $item) {
            
            $item['create_time'] = date('Y-m-d', $item['create_time']);
            $item['time'] = date('Y.m.d', $item['effect_time']) . ' - ' . date('Y.m.d', $item['failure_time']); //保险时间
            $item['i_status'] = $pio_status[$item['status']];
            $item['i_color'] = $pio_color[$item['status']];
            $item['img'] = $baseUrl . '/' . $item['img'];
            $item['is_insurance'] = 1;
            
            if ($item['status'] == 0) { //未付款
                $item['pay_url'] = $baseUrl . U("api/pay/handle?id={$item['id']}&number={$item['number']}&type=I");
                $item['wap_pay_url'] = $baseUrl . U("api/alipaywap/insurance?id={$item['id']}&number={$item['number']}");
                $item['pay_img'] = $baseUrl . U('Api/pay/qrcode') . '?url=' . urlencode($item['pay_url']);
                $item['weixin_img'] = $baseUrl . U("api/weixinpay/handle?id={$item['id']}&number={$item['number']}&type=I&show_type=1");
            } else if ($item['status'] == 3) { //出险
                
                if ($item['broken_flag'] == 1) {
                    $item['i_flag'] = '审核通过';
                } else if ($item['broken_flag'] == -1) {
                    $item['i_flag'] = '审核不通过';
                } else {
                    $item['i_flag'] = '审核中';
                }
            }
            
            //是否可以理赔
            $item['claims'] = 0;
            
            if (in_array($item['status'], array(1, 2)) || ($item['status'] == 3 && $item['broken_flag'] < 1)) {
                
                if ($item['effect_time'] <= $time && $time <= $item['failure_time']) {
                    $item['claims'] = 1;
                }
            }
            
            $insurance[$item['old_order_id']] = $item;
        }
        
        $status = array(
            '-1' => '订单已取消',
            '1' => '已下单',
            '2' => '处理中',
            '3' => '工程师准备中',
            '4' => '工程师已出发',
            '5' => '待付款',
            '6' => '订单已完成',
        );
        $color = array(
            '-1' => '#d2d2d2',
            '1' => '#46a4ed',
            '2' => '#46a4ed',
            '3' => '#46a4ed',
            '4' => '#46a4ed',
            '5' => '#46a4ed',
            '6' => '#46a4ed',
        );
        
        //订单
        $map = array('o.cellphone' => $cellphone);
        $list = M('order')->join('o left join engineer e on e.id = o.engineer_id')
                ->join('left join `phone` p on p.id = o.phone_id')
                ->join('left join order_phomal opm on opm.order_id = o.id')
                ->join('left join phone_malfunction pm on opm.phomal_id = pm.id')
                ->join('left join malfunction m on pm.malfunction_id = m.id')
                ->field('o.id, o.number, o.create_time, o.reference_price, o.actual_price, o.status, o.cellphone, o.color as color_name, o.pay_type, o.paid_amount, 
                    e.name as engineer_name, e.cellphone as engineer_phone, p.alias as phone_name, p.img, group_concat(m.name) as malfunctions')
                ->where($map)->group('o.id')->order('o.id asc')->select();
        
        foreach ($list as $k => $val) {
            $val['pay_status'] = $val['status'];
            $val['engineer_phone'] = $val['engineer_phone'] ? $val['engineer_phone'] : '';
            $val['create_time'] = date('Y-m-d', $val['create_time']);
            $val['color'] = isset($color[$val['status']]) ? $color[$val['status']] : '';
            $val['status'] = $status[$val['status']];
            $val['img'] = $baseUrl . '/' . $val['img'];
            $val['is_insurance'] = 0;
            $data[] = $val;
            
            if (isset($insurance[$val['id']]) && $insurance[$val['id']]) {
                $data[] = $insurance[$val['id']];
            }
        }
        
        rsort($data);
        
        $rst['status'] = 1;
        $rst['data'] = $data;
        $this->_callBack($rst);
    }
}