<?php

// +------------------------------------------------------------------------------------------ 
// | Author: qishanshan <qishanshan@weadoc.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  葡萄生活 Dates: 2016/3/2
// +------------------------------------------------------------------------------------------

namespace Api\Model;


class PutaoModel
{
    private $appkey = "0b0b8cc97c24923c421be7917633a7fb6dd6f725";
    private $secret = "786dcd6f57dbe91de40952ad7f020122";
    
    /**
     * 通知葡萄取消订单
     *
     * @param $orderId
     * @param $closeReason
     * @return void
     */
    public function notifyCloseOrder($param)
    {
        $orderId = (int)$param['orderId'];
        $closeReason = $param['closeReason'];
        
        if ($orderId <= 0 || $closeReason == '') {
            $result['status'] = 0;
            $result['info'] = '通知葡萄取消订单接口参数传入错误!';
            return $result;
        }
        
        $params = array();
        $params["app_key"] = $this->appkey;
        $params['cp_order_no'] = $orderId;
        $params['order_status'] = 14;
        $params["timestamp"] = time();
        $params['refuse_msg'] = $closeReason;
        $params["sign"] = $this->makeSignature($params);
        
        $parameterArr = array();
        foreach ($params as $k => $v) {
            $parameterArr[] = $k."=".urlencode($v);
        }
        
        $parameterString = implode("&", $parameterArr);
        $result = array();
        $result['app_key'] = $this->appkey;
        Vendor('Aes');
        $result['param'] = urlencode(\Aes::encrypt($parameterString, $this->secret));
        
        $res = array();
        foreach ($result as $k => $v) {
            $res[] = $k."=".$v;
        }

        $resString = implode("&", $res);
        
        $output = $this->request("http://open.putao.so/v1/api/order_status/modify?".$resString);
        
        $info_result = json_decode($output, true);
        
        $result = array();
        
        if ($info_result['code'] != 0) {
            $result['status'] = 0;
            $result['info'] = '取消订单通知葡萄接口失败!';
            D('Admin/order')->writeLog($orderId, '通知葡萄接口取消订单失败!');
            return $result;
        } else {
            $result['status'] = 1;
            D('Admin/order')->writeLog($orderId, '通知葡萄接口取消订单成功!');
            return $result;
        }
    }

    /**
     * 通知价格变更
     *
     * @param $orderId
     * @param $newPrice
     * @return void
     */
    public function notifyOrderPrice($param)
    {
        $orderId = (int)$param['orderId'];
        $newPrice = $param['newPrice'];
        
        if ($orderId <= 0 || $newPrice === '') {
            $result['status'] = 0;
            $result['info'] = '通知葡萄订单价格变更接口参数传入错误!';
            return $result;
        }
        
        $params = array();
        $params["app_key"] = $this->appkey;
        $params['cp_order_no'] = $orderId;
        $params['payWay'] = 0;
        $params['realAmount'] = intval($newPrice * 100);
        $params['settlementPrice'] = intval($newPrice * 100);;
        $params["timestamp"] = time();
        $params["sign"] = $this->makeSignature($params);
        
        $parameterArr = array();
        foreach ($params as $k => $v) {
            $parameterArr[] = $k."=".urlencode($v);
        }
        
        $parameterString = implode("&", $parameterArr);
        $result = array();
        $result['app_key'] = $this->appkey;
        Vendor('Aes');
        $result['param'] = urlencode(\Aes::encrypt($parameterString, $this->secret));
        
        $res = array();
        foreach ($result as $k => $v) {
            $res[] = $k."=".$v;
        }

        $resString = implode("&", $res);
        
        $output = $this->request("http://open.putao.so/v1/api/order_status/updateOrderPrice?".$resString); //getUrl放在了common/function中
        $info_result = json_decode($output, true);
        $result = array();
        
        if ($info_result['code'] != 0) {
            $result['status'] = 0;
            $result['info'] = '通知葡萄第三方付款失败';
            return $result;
        } else {
            $result['status'] = 0;
            $result['info'] = '该订单是葡萄订单，请先让用户付款，再结束订单';
            return $result;
        } 
    }

    /**
     * 通知订单状态的变更
     *
     * @param $orderId
     * @param $orderStatus
     * @return void
     */
    public function notifyOrderStatus($param)
    {
        $orderId = (int)$param['orderId'];
        $orderStatus = $param['orderStatus'];
    
        if ($orderId <= 0 || !is_numeric($orderStatus)) {
            $result['status'] = 0;
            $result['info'] = '通知葡萄订单状态的变更接口参数传入错误!';
            return $result;
        }
        
        $params = array();
        $params["app_key"] = $this->appkey;
        $params['cp_order_no'] = $orderId;
        $params['order_status'] = $orderStatus;
        $params["timestamp"] = time();
        $params["sign"] = $this->makeSignature($params);
        
        $parameterArr = array();
        foreach ($params as $k => $v) {
            $parameterArr[] = $k."=".urlencode($v);
        }
        
        $parameterString = implode("&", $parameterArr);
        $result = array();
        $result['app_key'] = $this->appkey;
        Vendor('Aes');
        $result['param'] = urlencode(\Aes::encrypt($parameterString, $this->secret));
        
        $res = array();
        foreach ($result as $k => $v) {
            $res[] = $k."=".$v;
        }

        $resString = implode("&", $res);

        $output = $this->request("http://open.putao.so/v1/api/order_status/modify?".$resString); //getUrl放在了common/function中
        
        $info_result = json_decode($output, true);
        $result = array();
        
        if ($info_result['code'] != 0) {
            D('Admin/order')->writeLog($orderId, '通知葡萄订单状态的变更失败!');
            $result['status'] = 0;
            $result['info'] = '通知葡萄接口状态失败';
            return $result;
        } else {
            D('Admin/order')->writeLog($orderId, '通知葡萄订单状态的变更成功!');
            $result['status'] = 1;
            return $result;
        }
    }

    /**
     * 生成签名
     *
     * @return void
     */
    private function makeSignature($args)
    {
        if(isset($args['sign'])) {
            $oldSign = $args['sign'];
            unset($args['sign']);
        } else {
            $oldSign = '';
        }

        ksort($args);
        $requestString = '';
        foreach($args as $k => $v) {
            $requestString .= $k . '=' . $v;
        }

        $newSign = hash_hmac("md5",strtolower($requestString) , $this->secret);
        return $newSign;
    }
    
    /**
     * 请求
     *
     * @return void
     */
    private function request($url) 
    {
        $ch = curl_init($url) ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ;
    
        return $output = curl_exec($ch) ;
    }
    
    /**
     * 获取可预约时间列表
     * 
     * @return void
     */
    public function getAvailableTimeslots($param)
    {
        $param_sign = trim($param['sign']);
        unset($param['sign']);
        
        $params = array();
        $params['app'] = trim($param['app']);
        $params['city'] = trim($param['city']);
        $params['cityCode'] = trim($param['cityCode']);
        $params['product_id'] = trim($param['product_id']);
        $params['service_address'] = trim($param['service_address']);
        $params['longtitude'] = trim($param['longtitude']);
        $params['latitude'] = trim($param['latitude']);

        if (!empty($param['quantity'])) {
            $params['quantity'] = trim($param['quantity']);
        }

        if (!empty($param['queryDayNum'])) {
            $params['queryDayNum'] = trim($param['queryDayNum']);
        }

        if (!empty($param['sku'])) {
            $params['sku'] = trim($param['sku']);
        }

        $params["timestamp"] = trim($param['timestamp']);
        
        $sign = $this->makeSignature($params);
        
        if ($param_sign != $sign) {
            $result['code'] = 10001;
            $result['msg'] = "签名验证错误！";
            return $result;
        }
        
        $result = array();
        $result['code'] = 0;
        $result['msg'] = 'success';

        for ($i = 0; $i < 7; $i ++) {
            $timelist = array();
            $timelist['date'] = date('Y-m-d', strtotime("+ $i day"));
            $timelist['timeslot'] = '000000000000000000111111000000111111110000000000'; //上午9-12 下午2-6
            $result['body']['timeList'][] = $timelist;
        }
        
        return $result;
    }
    
    /**
     * 创建订单
     * 
     * @return void
     */
    public function createOrder($param)
    {
        $result = array();
        
        /** 用户信息 */
        $param_sign = trim($param['sign']);
        unset($param['sign']);
        
        $params['app'] = trim($param['app']);
        $params['pt_order_id'] = trim($param['pt_order_id']);
        $params['product_id'] = trim($param['product_id']);
        $params['city'] = trim($param['city']);
        $params['cityCode'] = trim($param['cityCode']);
        $params['service_time'] = trim($param['service_time']);

        if (!empty($param['quantity'])) {
            $params['quantity'] = trim($param['quantity']);
        }
        
        $params['longtitude'] = trim($param['longtitude']);
        $params['latitude'] = trim($param['latitude']);
        $params['service_address'] = trim($param['service_address']);
        $params['houseNumber'] = trim($param['houseNumber']);

        if (!empty($param['source_id'])) {
            $params['source_id'] = trim($param['source_id']);
        }
        
        $params['cellphone'] = trim($param['cellphone']);
        $params['orderName'] = trim($param['orderName']);
        $params['price'] = trim($param['price']);

        if (!empty($param['coupon_code'])) {
            $params['coupon_code'] = trim($param['coupon_code']);
        }

        if (!empty($param['comment'])) {
            $params['comment'] = trim($param['comment']);
        }

        if (!empty($param['extraInfo'])) {
            $params['extraInfo'] = trim($param['extraInfo']);
        }

        if (!empty($param['sku'])) {
            $params['sku'] = trim($param['sku']);
        }

        if (!empty($param['customizeInfo'])) {
            $params['customizeInfo'] = trim($param['customizeInfo']);
        }
        
        $params["timestamp"] = trim($param['timestamp']);

        if (empty($params['product_id'])) {
            $result['code'] = 10003;
            $result['msg'] = "product_id不能为空！";
            return $result;
        }

        if (empty($params['service_address'])) {
            $result['code'] = 10003;
            $result['msg'] = "service_address不能为空！";
            return  $result;
        }

        if (empty($params['houseNumber'])) {
            $result['code'] = 10003;
            $result['msg'] = "houseNumber不能为空！";
            return $result; 
        }

        if (!preg_match("/^1[34578][0-9]{9}$/", $params['cellphone'])) {
            $result['code'] = 10003;
            $result['msg'] = "cellphone错误！";
            return $result;
        }

        if (empty($params['orderName'])) {
            $result['code'] = 10003;
            $result['msg'] = "orderName不能为空！";
            return $result;
        }

        if (empty($params['price'])) {
            $result['code'] = 10003;
            $result['msg'] = "price不能为空！";
            return $result;
        }

        if (empty($params['timestamp'])) {
            $result['code'] = 10003;
            $result['msg'] = "timestamp不能为空！";
            return $result;
        }

        $sign = $this->makeSignature($params);

        if ($param_sign != $sign) {
            $result['code'] = 10001;
            $result['msg'] = "签名验证错误！";
            return $result;
        }
        
        /** 客户 **/
        $user['cellphone'] = $params['cellphone'];
        $user['name'] = $params['orderName'];
        $user['address'] = $params['service_address'].$params['houseNumber'];
        $user['flag'] = 1;
        $user['create_time'] = time();

        /** 订单机型故障ID */
        $phone_array = json_decode($params['sku'], true);
        $order_phomal['phomal_id'] = $phone_array['sourceId'];
        
        $phone_malfunction = M('phone_malfunction')->where(array('id' => $phone_array['sourceId']))->find();
        $phone_id = (int) $phone_malfunction['phone_id'];

        /** 订单信息 */
        $order = array();
        $order['number'] = D('Admin/order')->createNumber();
        $order['customer'] = $params['orderName'];
        $order['cellphone'] = $params['cellphone'];
        $order['type'] = 1; /** 订单类型 - 1 新单 */
        $order['status'] = 1; /** 状态 1 下单 */
        $city = M('address')->where(array('name' => array('like', $params['city'] . '%'), 'level' => 2))->getField('id, pid');
        $order['province'] = $city ? current($city) : 0;
        $order['city'] = $city ? key($city) : 0;
        $order['address'] = $user['address'];
        $order['category'] = 1;
        $order['remark'] = '客户预约时间是: '.$params['service_time'];
        $order['engineer_remark'] = '客户预约时间是: '.$params['service_time'];
        $order['phone_id'] = $phone_id;
        $order['phone_name'] = M('phone')->where(array('id' => $phone_id))->getField('alias');
        $order['phone_name'] = $order['phone_name'] ? $order['phone_name'] : '';
        $order['create_time'] = time();
        
        if ($params['price'] == $phone_malfunction['price_reference']) {
            $order['reference_price'] = $params['price'];
            $order['actual_price'] = $params['price'];
        } else {
            $order['reference_price'] = $phone_malfunction['price_reference'];
            $order['actual_price'] = $phone_malfunction['price_reference'];
        }

        $order['reference_price'] = $order['reference_price'] ? $order['reference_price'] : 0;
        $order['actual_price'] = $order['actual_price'] ? $order['actual_price'] : 0;
         
        $flag = true;
        M()->startTrans();

        $customer_id = M('customer')->add($user);

        if ($customer_id === false) {
            $flag = false;
        }

        /** 用户ID */
        $order['customer_id'] = $customer_id;
        $order_id = M('order')->add($order);

        if ($order_id === false) {
            $flag = false;
        }

        $order_partner['order_id'] = $order_id;
        $order_partner['order_number'] = '';
        $order_partner['partner'] = '葡萄生活';
        $order_partner['is_paid'] = 0;
        $order_partner['is_confirm'] = 0;

        if (M('order_partner')->add($order_partner) === false) {
            $flag = false;
        }

        $order_phomal['order_id'] = $order_id;
        $order_phomal['phomal_id'] = $phone_array['sourceId'];

        if (M('order_phomal')->add($order_phomal) === false) {
            $flag = false;
        }

        /** 日志信息 */
        $log = array();
        /** 订单ID */
        $log['order_id'] = $order_id;
        /** 时间 */
        $log['time'] = time();
        /** 操作 - 下单 */
        $log['action'] = '操作人：' . $user['name'].'葡萄下单--状态：下单';

        if (M('order_log')->add($log) === false) {
            $flag = false;
        }

        $data['cp_order_no'] = $order_id;

        if ($flag) {
            M()->commit();
            $result['code'] = 0;
            $result['msg'] = 'success';
            $result['body'] = $data;
        } else {
            M()->rollback();
            $result['code'] = 1;
            $result['msg'] = '创建订单失败！';
        }
        
        return $result;
    }
    
    /**
     * 同步订单的交易状态--trade_status 2:成功
     * 
     * @return void
     */
    public function updateOrderPaied($param)
    {
        $param_sign = trim($param['sign']);

        $params = array();
        $params['app'] = trim($param['app']);
        $params['cp_order_no'] = trim($param['cp_order_no']);
        $params['trade_status'] = trim($param['trade_status']);

        if (!empty($param['trade_price'])) {
            $params['trade_price'] = trim($param['trade_price']);
        }

        if (!empty($param['coupon_code'])) {
            $params['coupon_code'] = trim($param['coupon_code']);
        }

        if (!empty($param['coupon_info'])) {
            $params['coupon_info'] = trim($param['coupon_info']);
        }

        $params['timestamp'] = trim($param['timestamp']);

        $sign = $this->makeSignature($params);

        if ($param_sign != $sign) {
            $result['code'] = 10001;
            $result['msg'] = "签名验证错误！";
            return $result;
        }

        if ($params['trade_status'] == 2) {  //付款
            
            $order_info = M('order_partner')->where(array('order_id' => $params['cp_order_no']))->find();

            if ($order_info) {
                
                $data['is_paid'] = 1;
                $flag = true;
                
                M()->startTrans();

                if (M('order_partner')->where(array('order_id' => $params['cp_order_no']))->save($data) === false) {
                    $flag = false;
                }

                $order_data['actual_price'] = round(trim($param['trade_price']) / 100);
                $order_data['third_party_number'] = '葡萄生活';

                if (M('order')->where(array('id' => $params['cp_order_no']))->save($order_data) === false) {
                    $flag = false;
                }
                
                if (D('Admin/order')->writeLog($params['cp_order_no'], '葡萄订单已付款!') == false) {
                    $flag = false;
                }

                if ($flag) {
                    M()->commit();
                    $result['code'] = 0;
                    $result['msg'] = 'success';
                } else {
                    M()->rollback();
                    $result['code'] = 1;
                    $result['msg'] = 'error';
                }
            } else {
                $result['code'] = 1;
                $result['msg'] = '订单不存在';
            }
        }  else if (($params['trade_status'] == 3)) { //取消订单
            
            $order_info = M('order')->where(array('id' => $params['cp_order_no']))->find();
            
            if (($order_info['status'] < 4) && ($order_info['status'] != -1)) {
                
                $flag = true;
                $data = array();
                $data['status'] = -1;
                $data['close_time'] = time();
                
                M()->startTrans();
                
                if (M('order')->where(array('id' => $params['cp_order_no']))->save($data) === false) {
                    $flag = false;
                }
                
                if (D('Admin/order')->writeLog($params['cp_order_no'], '葡萄用户取消订单!') === false) {
                    $flag = false;
                }
            
                if ($flag) {
                    M()->commit();
                    $result['code'] = 0;
                    $result['msg'] = 'success';
                } else {
                    M()->rollback();
                    $result['code'] = 1;
                    $result['msg'] = '订单取消失败';
                }
            } else if ($order_info['status'] == -1) {
                $result['code'] = 0;
                $result['msg'] = 'success';
            } else {
                $result['code'] = 1;
                $result['msg'] = '工程师已经出发，不能取消订单';
            }
        } 
        return $result;
    }
    
    /**
     * 更新订单信息--取消订单
     */
    public function updateOrderInfo($param)
    {
        $param_sign = trim($param['sign']);

        $params = array();
        $params['app'] = trim($param['app']);
        $params['cp_order_no'] = trim($param['cp_order_no']);
        $params['order_status'] = trim($param['order_status']);
        $params['timestamp'] = trim($param['timestamp']);

        $sign = $this->makeSignature($params);

        if ($param_sign != $sign) {
            $result['code'] = 10001;
            $result['msg'] = "签名验证错误！";
            return $result;
        }

        $result = array();
        
        if ($params['order_status'] == 7) { //取消订单
            $order_info = M('order')->where(array('id' => $params['cp_order_no']))->find();

            if (($order_info['status'] < 4) && ($order_info['status'] != -1)) {

                $flag = true;
                $data = array();
                $data['status'] = -1;
                $data['close_time'] = time();

                M()->startTrans();
                
                if (M('order')->where(array('id' => $params['cp_order_no']))->save($data) === false) {
                    $flag = false;
                }
                
                if (D('Admin/order')->writeLog($params['cp_order_no'], '葡萄用户取消订单!') === false) {
                    $flag = false;
                }
            
                if ($flag) {
                    M()->commit();
                    $result['code'] = 0;
                    $result['msg'] = 'success';
                } else {
                    M()->rollback();
                    $result['code'] = 1;
                    $result['msg'] = '订单取消失败';
                }
            } else {
                $result['code'] = 1;
                $result['msg'] = '工程师已经出发，不能取消订单';
            }
        } else {
            $result['code'] = 1;
            $result['msg'] = '取消状态不是7，请确认';
        }
        
        return $result;
    }
}