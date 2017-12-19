<?php
// +------------------------------------------------------------------------------------------
// | Author: TCG <tianchunguang@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: 保险  Dates: 2016-12-05
// +------------------------------------------------------------------------------------------

namespace Api\Controller;

use Think\Controller;
use Think\App;

class InsuranceController extends BaseController
{
    private $baseUrl = 'http://api.shanxiuxia.com';
    
    /**
     * 获取服务器当前时间
     */
    public function date()
    {
        $text = date('Y-m-d H:i:s');
        $font = './Public/fonts/msyh.ttf';
        
        $im = imagecreate(700, 100);
        
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        
        imagefilledrectangle($im, 0, 0, 700, 100, $white);
                      
        imagettftext($im, 50, 0, 20, 75, $black, $font, $text); //在阴影上输出一个黑色的字符串
        
        header("Content-type: image/png");
        imagepng($im);
        imagedestroy($im);
    }
    
    /**
     * 订单购买保险二维码
     */
    public function insuranceQrcode()
    {
        $orderId = intval(I('param.orderId'));
        $engineerId = intval(I('param.engineerId'));
        
        $result = array();
        
        if (empty($orderId)) {
            $result['status'] = 0;
            $result['data'] = '查询不到订单';
            $this->ajaxReturn($result);
        }
        
        $order = M('order')->where(array('id' => $orderId))->field('id, type, status, clearing_time')->find();
        
        if (!$order) {
            $result['status'] = 0;
            $result['data'] = '查询不到订单';
            $this->ajaxReturn($result);
        }
        
        if (!in_array($order['type'], array(1, 2, 5))) {
            $result['status'] = 0;
            $result['data'] = '此维修订单没有提供购买保险服务！';
            $this->ajaxReturn($result);
        }
        
        if ($order['status'] != 6) {
            $result['status'] = 0;
            $result['data'] = '订单不是入库状态，无法购买保险';
            $this->ajaxReturn($result);
        }
        
        if ($order['clearing_time'] + 259200 < time()) {
            $result['status'] = 0;
            $result['data'] = '不好意思，保险必须在订单入库后的3天内购买！';
            $this->ajaxReturn($result);
        }
        
        if (M('order_partner')->where(array('order_id' => $orderId))->count()) {
            $result['status'] = 0;
            $result['data'] = '第三方维修订单没有提供购买保险服务！';
            $this->ajaxReturn($result);
        }
        
        $insurance = M('order_phomal')->join('op left join phomal_insurance_phomals pip on op.phomal_id = pip.phomal_id')
                    ->join('left join phomal_insurance pi on pi.id = pip.phomal_insurance_id')
                    ->where(array('op.order_id' => $orderId, 'pi.id' => array('gt', 0), 'pi.status' => 1))->field('pi.*')->find();
        
        if (!$insurance) {
            $result['status'] = 0;
            $result['data'] = '此维修订单没有可以购买的保险服务！';
            $this->ajaxReturn($result);
        }
        
        $url = "http://insurance.shanxiuxia.com/#/?orderId={$orderId}&engineerId={$engineerId}";
        $this->show("<img alt='扫码买保险' src='" . U('api/insurance/qrcode') . '?url=' . urlencode($url) . "' style='width:240px;height:240px;'/>");
    }
    
    /**
     * 保险单下单页
     */
    public function policy()
    {
        $orderId = intval(I('param.orderId'));
        $number = trim(I('param.orderNumber'));
        $engineerId = intval(I('param.engineerId'));
        
        $result = array();
        $map = array();
        
        if (empty($orderId) && empty($number)) {
            $result['status'] = 0;
            $result['data'] = '查询不到订单';
            $this->_callBack($result);
        }
        
        if ($orderId) {
            $map['id'] = $orderId;
        } else {
            $map['number'] = $number;
        }
        $order = M('order')->where($map)->find();
        
        if (!$order) {
            $result['status'] = 0;
            $result['data'] = '查询不到订单';
            $this->_callBack($result);
        }
        
        if (!in_array($order['type'], array(1, 2, 5))) {
            $result['status'] = 0;
            $result['data'] = '此维修订单没有提供购买保险服务！';
            $this->_callBack($result);
        }
        
        if ($order['status'] != 6) {
            $result['status'] = 0;
            $result['data'] = '订单不是入库状态，无法购买保险';
            $this->_callBack($result);
        }
        
        if ($order['clearing_time']  + 259200 < time()) {
            $result['status'] = 0;
            $result['data'] = '不好意思，保险必须在订单入库后的3天内购买！';
            $this->_callBack($result);
        }
        
        $orderId = intval($order['id']);
        
        if (M('order_partner')->where(array('order_id' => $orderId))->count()) {
            $result['status'] = 0;
            $result['data'] = '第三方维修订单没有提供购买保险服务！';
            $this->_callBack($result);
        }
        
        if (M('phomal_insurance_order')->where(array('old_order_id' => $orderId, 'status' => array('gt', 0)))->count()) {
            $result['status'] = 0;
            $result['data'] = '您已购买过保险服务了！';
            $this->_callBack($result);
        }
        
        $insurance = M('order_phomal')->join('op left join phomal_insurance_phomals pip on op.phomal_id = pip.phomal_id')
                    ->join('left join phomal_insurance pi on pi.id = pip.phomal_insurance_id')
                    ->where(array('op.order_id' => $orderId, 'pi.id' => array('gt', 0), 'pi.status' => 1))->field('pi.*')->find();
        
        if (!$insurance) {
            $result['status'] = 0;
            $result['data'] = '此维修订单没有可以购买的保险服务！';
            $this->_callBack($result);
        }
        
        $data = array();
        $data['order_id'] = $order['id'];
        $data['customer'] = $order['customer'];
        $data['cellphone'] = $order['cellphone'];
        $data['phone_name'] = $order['phone_name'];
        $data['phone_imei'] = $order['phone_imei'];
        $data['engineer_id'] = $engineerId;
        $data['insurance_id'] = $insurance['id'];
        $data['service_title'] = $insurance['title'];
        $data['duration'] = $insurance['duration'];
        $data['price'] = $insurance['price'];
        $data['effect_time'] = date('Y-m-d', strtotime('tomorrow'));
        
        $result['status'] = 1;
        $result['data'] = $data;
        $this->_callBack($result);
    }
    
    /**
     * 投保下单
     */
    public function generatePolicy()
    {
        $orderId = intval(I('post.orderId'));
        $engineerId = intval(I('post.engineerId'));
        $phomalInsuranceId = intval(I('post.insuranceId'));
        
        $result = array();
        $map = array();
        
        if (empty($orderId)) {
            $result['status'] = 0;
            $result['data'] = '查询不到订单';
            $this->_callBack($result);
        }
        
        if (empty($phomalInsuranceId)) {
            $result['status'] = 0;
            $result['data'] = '无法购买保险，请联系客服人员！';
            $this->_callBack($result);
        }
        
        if (M('phomal_insurance_order')->where(array('old_order_id' => $orderId, 'status' => array('gt', 0)))->count()) {
            $result['status'] = 0;
            $result['data'] = '您已购买过保险服务了！';
            $this->_callBack($result);
        }
        
        try {
            //下单
            $insurance = D('Admin/phomalInsurance')->addInsuranceOrder($orderId, $phomalInsuranceId, $engineerId);
            
            if (!$insurance) {
                $result['status'] = 0;
                $result['data'] = '购买保险失败，请联系客服人员！';
                \Think\Log::record("orderID:{$orderId}-engineerId:{$engineerId}-insuranceId:{$phomalInsuranceId}");
                $this->_callBack($result);
            }

            $pay_url = $this->baseUrl . U("api/pay/handle?id={$insurance['id']}&number={$insurance['number']}&type=I");
            $pay_img = $this->baseUrl . U('Api/pay/qrcode') . '?url=' . urlencode($pay_url);
            $wx_img  = $this->baseUrl . U("api/weixinpay/handle?id={$insurance['id']}&number={$insurance['number']}&type=I&show_type=1");


            // TODO 跳转到付款页面
            $result['status'] = 1;
            $result['data']['pay_url'] = $pay_url;
            $result['data']['pay_img'] = $pay_img;
            $result['data']['weixin_img'] = $wx_img;

            $this->_callBack($result);
        } catch (\Exception $e) {
            $result['status'] = 0;
            $result['data'] = '无法购买保险，请联系客服人员！';
            \Think\Log::record("orderId:{$orderId}-engineerId:{$engineerId}-insuranceId:{$phomalInsuranceId}-错误:" . $e->getMessage());
            $this->_callBack($result);
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
     * 理赔页面
     */
    public function policyInfo()
    {
        $id = intval(I('post.id'));
        
        if (!$id) {
            $result['status'] = 0;
            $result['data'] = '查询不到保险单信息！';
            $this->_callBack($result);
        }
        
        $map = array('pio.id' => $id);
        
        $order = M('phomal_insurance_order')->join('pio left join `order` o on o.id = pio.old_order_id')
                ->join('left join `phone` p on p.id = o.phone_id')
                ->field('pio.*, o.phone_name, o.phone_imei, p.img')->where($map)->find();
        
        if (!$order) {
            $result['status'] = 0;
            $result['data'] = '查询不到保险单信息！';
            $this->_callBack($result);
        }
        
        if ($order['status'] < 1 || $order['status'] > 3) {
            $result['status'] = 0;
            $result['data'] = '当前保险单不能申请理赔！';
            $this->_callBack($result);
        }
        
        if ($order['status'] == 3 && $order['broken_flag'] == 0) {
            $result['status'] = 0;
            $result['data'] = '保险单理赔申请正在审核中，无法提交申请信息！';
            $this->_callBack($result);
        }
        
        if ($order['status'] == 3 && $order['broken_flag'] == 1) {
            $result['status'] = 0;
            $result['data'] = '保险单理赔申请审核已通过，不需要重复申请！';
            $this->_callBack($result);
        }
        
        $time = time();
        
        if ($order['effect_time'] > $time)
        {
            $result['status'] = 0;
            $result['data'] = '保险单还未生效，无法申请理赔！';
            $this->_callBack($result);
        }
        
        if ($time > $order['failure_time']) {
            $result['status'] = 0;
            $result['data'] = '保险单已过期，无法申请理赔！';
            $this->_callBack($result);
        }
        
        $data = array();
        $data['id'] = $order['id'];
        $data['customer'] = $order['customer'];
        $data['cellphone'] = $order['cellphone'];
        $data['effect_time'] = date('Y-m-d', $order['effect_time']);
        $data['failure_time'] = date('Y-m-d', $order['failure_time']);
        $data['phone_name'] = $order['phone_name'];
        $data['phone_imei'] = $order['phone_imei'];
        $data['phone_img'] = $this->baseUrl . $order['img'];
        
        $result['status'] = 1;
        $result['data'] = $data;
        $this->_callBack($result);
    }
    
    /**
     * 理赔
     */
    public function broken()
    {
        $code = trim(I('post.code'));
        $mobile = trim(I('post.mobile'));
        $key = 'code' . $mobile;
        
/**         if (S($key) != $code) {
            $result['status'] = 0;
            $result['data'] = '短信验证码错误！';
            $this->_callBack($result);
        } */
        
        $id = intval(I('post.id'));
        
        if (!$id) {
            $result['status'] = 0;
            $result['data'] = '查询不到保险单，无法理赔！';
            $this->_callBack($result);
        }
        
        $map = array('id' => $id);
        
        $order = M('phomal_insurance_order')->where($map)->find();
        
        if (!$order) {
            $result['status'] = 0;
            $result['data'] = '查询不到保险单，无法理赔！';
            $this->_callBack($result);
        }
        
        if ($order['status'] < 0) {
            $result['status'] = 0;
            $result['data'] = '保险已取消，无法理赔！';
            $this->_callBack($result);
        } else if ($order['status'] == 0) {
            $result['status'] = 0;
            $result['data'] = '保险还未付款，无法理赔！';
            $this->_callBack($result);
        } else if ($order['status'] == 1 && $order['effect_time'] > time()) {
            $result['status'] = 0;
            $result['data'] = '保险还未生效，无法理赔！';
            $this->_callBack($result);
        } else if ($order['status'] == 2 && $order['failure_time'] < time()) {
            $result['status'] = 0;
            $result['data'] = '保险已过期，无法理赔！';
            $this->_callBack($result);
        } else if ($order['status'] == 4) {
            $result['status'] = 0;
            $result['data'] = '保险已过期，无法理赔！';
            $this->_callBack($result);
        } else if ($order['status'] == 5) {
            $result['status'] = 0;
            $result['data'] = '保险服务已完成，无法再次理赔！';
            $this->_callBack($result);
        } else if ($order['status'] == 3 && $order['broken_flag'] == 0) {
            $result['status'] = 0;
            $result['data'] = '理赔申请正在审核中，无需重复申请！';
            $this->_callBack($result);
        } else if ($order['status'] == 3 && $order['broken_flag'] == 1) {
            $result['status'] = 0;
            $result['data'] = '理赔申请已审核通过，无需重复申请！';
            $this->_callBack($result);
        }
        
        $info = $this->upload();
        
        if (!$info['success']) {
            $result['status'] = 0;
            $result['data'] = $info['errorMsg'];
            $this->_callBack($result);
        }
        
        $data = array();
        $data['status'] = 3;
        $data['broken_time'] = time();
        $data['broken_flag'] = 0;
        $data['broken_img'] = '/upload/' . $info['info']['file']['savepath'] . $info['info']['file']['savename'];
        
        if (M('phomal_insurance_order')->where($map)->save($data) !== false) {

            $result['status'] = 1;
            S($key, '');

            //通知值班员工
            $list = M('insurance_work')
                ->join('left join user on user.id = insurance_work.user_id')
                ->where(array('switch' => array('EQ', 1)))
                ->field('user.telphone')
                ->select();

            $phone = '';

            foreach ($list as $value) {
                $phone .= $value['telphone'].',';
            }

            $sms = new \Vendor\aliNote\aliNote();
            $rst = $sms->send($phone, array('name' => '保险单'.$order['number']),'SMS_38385145');


        } else {
            $result['status'] = 0;
            $result['data'] = '理赔申请提交出错，请刷新重试！';
        }
        $this->_callBack($result);
    }
    
    /**
     * 发送短信验证码
     * @return void
     */
/**     public function sendSms()
    {
        $mobile = trim(I('post.mobile'));
        
        if (empty($mobile)) {
            $result['status'] = 0;
            $result['data'] = '请输入手机号码！';
            $this->_callBack($result);
        }
        
        if (!D('check')->regexp('mobile', $mobile)) {
            $result['status'] = 0;
            $result['data'] = '请输入手机号码！';
            $this->_callBack($result);
        }
        
        if (intval(S('sendtime'.$mobile)) + 60 > time()) {
            $result['status'] = 0;
            $result['data'] = '短信验证码发送太频繁，请稍后重试！';
            $this->_callBack($result);
        }
        
        $code = D('order')->generate_code(6);
        $sms = new \Vendor\aliNote\aliNote();
        $result = $sms->send($mobile, array('product' => $mobile, 'code' => strval($code)), 'SMS_5024351');
        
        if ($result) {
            S('code'.$mobile, $code, 1800);
            S('sendtime'.$mobile, time(), 60);
            $rst['status'] = 1;
            $this->_callBack($rst);
        } else {
            $rst['status'] = 0;
            $rst['data'] = '短信验证码发送失败，请稍后重试！';
            $this->_callBack($rst);
        }
    } */

    /**
     * 投保下单
     */
    public function generateNumber()
    {
        $orderId = intval(I('post.orderId'));
        $engineerId = intval(I('post.engineerId'));
        $phomalInsuranceId = intval(I('post.insuranceId'));

        $result = array();
        $map = array();

        if (empty($orderId)) {
            $result['status'] = 0;
            $result['data'] = '查询不到订单';
            $this->_callBack($result);
        }

        if (empty($phomalInsuranceId)) {
            $result['status'] = 0;
            $result['data'] = '无法购买保险，请联系客服人员！';
            $this->_callBack($result);
        }

        if (M('phomal_insurance_order')->where(array('old_order_id' => $orderId, 'status' => array('gt', 0)))->count()) {
            $result['status'] = 0;
            $result['data'] = '您已购买过保险服务了！';
            $this->_callBack($result);
        }

        try {
            //下单
            $insurance = D('Admin/phomalInsurance')->addInsuranceOrder($orderId, $phomalInsuranceId, $engineerId);

            if (!$insurance) {
                $result['status'] = 0;
                $result['data'] = '购买保险失败，请联系客服人员！';
                \Think\Log::record("orderID:{$orderId}-engineerId:{$engineerId}-insuranceId:{$phomalInsuranceId}");
                $this->_callBack($result);
            }

            $pay_url = $this->baseUrl . "/api/alipaywap/handlenew?id={$insurance['id']}&orderId={$orderId}&type=I&number={$insurance['number']}";
            $pay_img = $this->baseUrl . '/Api/pay/qrcode' . '?url=' . $this->baseUrl . "/api/alipaywap/handlenew/id/{$insurance['id']}/orderId/{$orderId}/type/I/number/{$insurance['number']}";
            $wx_img  = $this->baseUrl . "/api/weixinpay/handlenew/id/{$insurance['id']}/number/{$insurance['number']}/type/I/show_type/1";
            $wx_url  = array('id' => $orderId, 'number' => $insurance['number']);

            // TODO 跳转到付款页面
            $result['status'] = 1;
            $result['data']['pay_url'] = $pay_url;
            $result['data']['pay_img'] = $pay_img;
            $result['data']['weixin_img'] = $wx_img;
            $result['data']['wx_url'] = $wx_url;


            $this->_callBack($result);
        } catch (\Exception $e) {
            $result['status'] = 0;
            $result['data'] = '无法购买保险，请联系客服人员！';
            \Think\Log::record("orderId:{$orderId}-engineerId:{$engineerId}-insuranceId:{$phomalInsuranceId}-错误:" . $e->getMessage());
            $this->_callBack($result);
        }
    }
}