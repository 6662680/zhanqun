<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 系统管理控制器 Dates: 2015-07-13
// +------------------------------------------------------------------------------------------

namespace Api\Controller;

use Api\Controller;

class YashenghuoController extends BaseController
{

    /**
     * 获取应用APP密钥
     */
    public function getAppInfo()
    {
        $ret = D('yashenghuo')->requestProcess('life.app.getAppInfo');
    }

    /*
     * 消息推送
     *
     * @param array
     * @author liyang
     * @return json
     */
    public function sendMessage()
    {
        $param = array(
            'orderNo' => 225057,
            'mobile' => 18679354419,
            'msgway' => 0,
            'type' => 0,
            'pushway' => 3,
            'msg' => '这是一条测试信息',
        );
        $rst = D('yashenghuo')->sendMessage($param);
    }

    /*
   * 支付
   */
    public function pay()
    {
        $rst = D('yashenghuo')->pay(I('get.orderNumber'));
        $this->_callBack($rst);
    }

    /**
     * 验证用户登录接口
     */
    public function checkLogin()
    {
        $param = I('get.');
        $ret = array ();

        if (empty ($param ['accessToken'])) {

            $ret ['code'] = - 1002;
            $ret ['msg_cn'] = 'accessToken不能为空！';
        } else {

            $time = time ();
            $api_params = array (
                'accessToken' => $param ['accessToken']
            );

            $ret = D('yashenghuo')->requestProcess ('life.login.checkLogin', $api_params );
        }

        return $ret;
    }

    /*
     * 异步支付通知
     */
    public function callbackUrl()
    {
        $get = I('get.');

        $validation = array('orderNo', 'serialNumber', 'securityCode', 'payType', 'payTime', 'payStatus');
        $payType = array('余额积分', '支付宝', '微信', '银联', '安吉拉银联 POS');

        foreach ($validation as $value) {
            if (!isset($get[$value])) {
                $this->_error(503, $value. '验证失败');
            }
        }

         /** 支付成功修改订单状态 **/
        if ($get['payStatus'] == 3 /*&& S('securityCode'. $get['orderNo']) ==  $get['securityCode']*/) {

            M()->startTrans();

            $model = M('order');
            $model->find($get['orderNo']);

            if (empty($model->id)) {
                M()->rollback();
                $this->_error('503', '无效的订单');
            }

            if ($model->status >= 6) {
                M()->rollback();
                $this->_error('503', '订单状态不正确');
            }

            $model->status = 6;
            $model->is_clearing = 1;
            $model->third_party_number = $get['serialNumber'];

            $mobile = M('order_partner')->where(array('order_id' => $get['orderNo']))->getField('third_party_user_no');

            if (!$model->save()) {
                M()->rollback();
                $this->_error('503', '修改失败');
            }

            $rst = D('Admin/order')->writeLog($get['orderNo'], '用户雅生活付款['. $payType[$get['payType'] ]. 'third_party_number :'. $get['serialNumber'].']');

            /** 订单完成 **/
            $get['payType'] = $payType[$get['payType']];
            $rst= $this->payOrder($get);

            /** 消息推送 **/
            $send = array(
                'orderNo' => $get['orderNo'],
                'uid' => '',
                'mobile' =>$mobile,
                'msgway' => 0,
                'type' => 0,
                'pushway' => 3,
                'title' => '订单完成',
                'msg' => '您好，您的订单已完成，感谢您对我们的信赖。如有问题，欢迎拨打我们客服热线：4000105678。【闪修侠】',
            );

            //消息推送
            D('yashenghuo')->sendMessage($send);

            if (!$rst) {
                M()->rollback();
                $this->_error('503', '写入日志失败');
            } else {
                M()->commit();
                echo "0";
            }
        }

    }

    /**
     * 订单完成接口
     */
    public function payOrder($param = array())
    {
        $ret = array ();

        if (empty($param['orderNo'])) {
            $ret ['code'] = - 1002;
            $ret ['msg_cn'] = '订单号不能为空！';
            return;
        }

        $param ['opTime'] = ! empty ( $param ['opTime'] ) ? $param ['opTime'] : time ();
        $api_params = array (
            'orderNo' => $param ['orderNo'],
            'totalAmount' => M('order')->where(array('id' => $param['orderNo']))->getField('actual_price'),
            'amount' => $param ['amount'],
            'payMethod' => $param['payType'],
            'opTime' => date('Y-m-d H:i', $param['opTime']),
            'pays' => '第三方支付'.$param ['amount'],
        );
        $ret = D('yashenghuo')->requestProcess ('life.order.payOrder', $api_params);

        return $ret;
    }
}