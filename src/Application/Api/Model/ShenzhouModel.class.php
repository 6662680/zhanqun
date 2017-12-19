<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 神州数码 Dates: 2016-06-08
// +------------------------------------------------------------------------------------------

namespace Api\Model;

class ShenzhouModel
{
    /** 请求地址 */
    /** private $getWay = 'http://main.jc.scity.cn/center_myAccount/service/CW0101'; */
    private $getWay = 'http://main.scity.cn/center_myAccount/service/CW0101';
    /** 秘钥 */
    private $secretKey = '';

    /**
     * 推送订单
     *
     * @return void
     */
    public function deal($param)
    {
        $orderId = (int)$param['orderId'];
        
        if ($orderId <= 0) {
            \Think\Log::record('神州数码推送订单OrderId为空', 'ERR');
            return false;
        }
        
        \Think\Log::record('开始滴滴同步----', 'ERR');
        
        $order = M('order')->join('o left join customer as c on o.customer_id = c.id')
                 ->where(array('o.id' => $orderId))->find();

        $malfunctions = M('order_phomal')->join('opm left join phone_malfunction as pm on opm.phomal_id = pm.id')
                        ->join(('left join malfunction as m on pm.malfunction_id = m.id '))
                        ->field('concat(m.name) as malfunctions')
                        ->where(array('opm.order_id' => $orderId))
                        ->group('opm.order_id')->find();
        
        if (!empty($malfunctions)) {
            $malfunctionsStr = trim($malfunctions['malfunctions']);
        } else {
            $malfunctionsStr = '其他';
        }

        $data = array();
        $data['orderID'] = (string)$orderId;
        $data['cellphone'] = (string)$order['cellphone'];
        $data['name'] = (string)$order['name'];
        $data['address'] = (string)$order['address'];

        if (!empty($order['email'])) {
            $data['email'] = (string)$order['email'];
        }

        if (!empty($order['weixin'])) {
            $data['weixin'] = (string)$order['weixin'];
        }

        $data['phone'] = (string)$order['phone_name'];
        $data['color'] = (string)$order['color'];
        $data['province'] = M('address')->where(array('id' => $order['province']))->getField('name');
        $data['city'] = M('address')->where(array('id' => $order['city']))->getField('name');
        $data['county'] = M('address')->where(array('id' => $order['county']))->getField('name');

        if (!empty($order['malfunction_description'])) {
            $data['malfunction_description'] = (string)$order['malfunction_description'];
        }

        $data['is_invoice'] = (string)$order['is_invoice'];

        if (!empty($order['invoice'])) {
            $data['invoice'] = (string)$order['invoice'];
        }

        $data['malfunction_items'] = (string)$malfunctionsStr;
        $data['order_time'] = (string)$order['create_time'];
        $data['status'] = (string)$order['status'];

        $request = array(
            'body' => $data,
            'head' => array(
                'appid' => 'BAS-0512-0001',
                'version' => '1.0',
            ),
        );

        $rst = $this->request($this->getWay, json_encode($request));

        /** 结果处理 */
        $rst = json_decode($rst, true);

        if ($rst['head']['rtnCode'] == '000000') {
            $msg = '[滴滴]订单同步成功[orderId:' . $orderId . ']';
            \Think\Log::record($msg, 'ERR');
            D('Admin/order')->writeLog($orderId, $msg);
        } else {
            $msg = '[滴滴]订单同步失败[orderId:' . $orderId . '], 错误代码：' . $rst['head']['rtnCode'] . ', 错误信息:' . $rst['head']['rtnMsg'];
            \Think\Log::record($msg, 'ERR');
            D('Admin/order')->writeLog($orderId, $msg);
        }
    }

    /**
     * 更新状态
     *
     * @return void
     */
    public function updateStatus($order)
    {
        #code...
    }

    /**
     * 请求
     *
     * @return void
     */
    private function request($url, $param)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($param))
        );
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            $msg = "can't post url [" . $url . "], data [" . json_encode($param) . "]";
            \Think\Log::record($msg, 'ERR');
        }

        return $result;
    }
}