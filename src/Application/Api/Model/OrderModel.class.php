<?php

// +------------------------------------------------------------------------------------------ 
// | Author: liyang <664577655@qq.com>
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 订单验证模型 Dates: 2016-08-4
// +------------------------------------------------------------------------------------------

namespace Api\Model;


class OrderModel
{

    public function __construct()
    {

    }

    private function createNumber()
    {
        // W 日期（年月日时分秒)+ 随机字母（位）
        // W 14 07 17 11 22 56 xxx
        return 'W' . date('ymdHis', time()) . chr(mt_rand(ord('a'), ord('z'))) . chr(mt_rand(ord('a'), ord('z'))) . chr(mt_rand(ord('a'), ord('z')));
    }

    /**
     * 自动派单
     * @param $orderId
     * @return Boolean
     */
    private function autoSendOrder($orderId)
    {
        $appkey = '9dc5de36dc343fb5ae1e86863150cc82';
        $address = 'www.shoujihuaile.com/index.php/api/push/dispatch'; //线上
        //$address = 'background.shoujihuaile.com/index.php/api/push/dispatch'; //测试
        $url = $address . '?appkey=' . urlencode($appkey) . '&';
        $url .= 'orderId=' . urlencode($orderId);
        echo $url;die();
        $this->curlGet($url);
    }

    /**
     * curl get方法
     *
     * @param string $url 请求地址
     * @return unknown
     */
    private function curlGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
    }

    /**
     * 是否上门
     * @param array
     * @return Boolean
     */
    public function isCategory($array,$orderinfo)
    {
        $sms = new \Vendor\aliNote\aliNote();

        if($array['category']==2){
            return $sms->send($array['mobile'],['orderNumber'=>$orderinfo['orderNumber'],
                'msg'=>str_replace('邮寄地址:','',$array['mailAddress']) ],'SMS_15475218');

        } elseif ($array['category'] == 1) {

            return $sms->send($array['mobile'],['orderNumber'=>$orderinfo['orderNumber']],'SMS_15555150');

        } elseif ($array['category'] == 3) {
            return $sms->send($array['mobile'],['orderNumber'=>$orderinfo['orderNumber'],
                'msg'=>trim(str_replace('邮寄地址:','',$array['mailAddress'])) ],'SMS_44445559');
        }
    }

    /**
     * 添加用户信息到custormer
     * @param array
     * @return Boolean
     */
    public function addCustomer($array)
    {
        $data = array(
            'create_time'=>time(),
            'name'=>$array['name'],
            'address'=>$array['address'],
            'cellphone'=>$array['mobile']
        );

        if($customer_id = M('customer')->add($data)){
            return $customer_id;
        } else {
            return false;
        }
    }

    /**
     * 订单模型
     * @param array
     * @return Boolean
     */
    public function orderModel($array)
    {
        if (!$order = $this->addOrder($array)) {
            return ['status'=>false,'msg'=>'添加到用户数据库失败'];
        }

        if (empty($order['orderId'])) {
            return ['status'=>false,'msg'=>'orderId不许为空'];
        }

        /*自动派单*/
        if ($array['category'] == 1) {
            //$this->autoSendOrder($order['orderId']);
        }

        if (!$this->addMalfunction($array['malfunctions'],$order['orderId'])) {
            return ['status'=>false,'msg'=>'添加到故障信息错误'];
        }

        /*小伙伴分成*/
        if (!empty($array['friendshare'])) {
            $this->frendParticipation($order['orderId']);
        }

        /** 下单来源 */
        if (!empty($array['landUrl'])) {
            $this->orderTrace($order, $array);
        }
        
        return array('status' => true, 'orderNumber' => $order['orderNumber'], 'orderId' => $order['orderId']);
    }

    /**
     * 添加订单信息到order
     * @param array
     * @return Boolean
     */
    public function addOrder($array)
    {
        if ($array['date']){
            $array['date'] = date('Y-m-d H:i:s', $array['date']);
        } else {
            $array['date'] = '无';
        }
        $model = D('address');
        $order = array();
        /** 上门类型 category 1 上门 2 邮寄 3 到店 */
        $order['category'] = $array['category'];
        $order['number'] = $this->createNumber();
        $order['customer_id'] = $array['customer_id'];
        $order['customer'] = $array['name'];
        /** 订单类型 type 1 新单 2 返修 3 保险 4 第三方 5 活动 */
        $order['type'] = isset($array['type']) ? $array['type'] : 1;
        /** 付款类型 pay_type 2 预付 1 维修后支付 */
        $order['pay_type'] = isset($array['pay_type']) ? $array['pay_type'] : 1;
        $order['status'] = 1;
        $order['create_time'] = time();
        $order['phone_imei'] = $array['phone_imei'] ? $array['phone_imei'] : '';
        $order['address'] = $array['address'];
        $order['cellphone'] = $array['mobile'];
        $order['is_favorable'] = 0;
        $order['color'] = $array['color_id']? $this->phonecolor($array['color_id']):'';
        $order['color_id'] = $array['color_id'] ? $array['color_id'] : '';
        $order['phone_id'] = $array['phone_id'];
        $order['phone_name'] = $array['phone_id']?$this->phoneName($array['phone_id']):'';
        $order['tax_number'] = $array['tax_number'] ? $array['tax_number'] : '';
        $order['is_personal'] = $array['is_personal'] ? $array['is_personal'] : '';

        if ($order['category'] == 1) {
            $order['province'] = !empty($array['province']) ? $array['province'] : '';
            $order['city'] = !empty($array['city']) ? $array['city'] : '';
            $order['county'] = !empty($array['area']) ? $array['city'] : '';
        } else {
            $order['city'] = $array['mailCiyt'];
        }

        $order['is_invoice'] = $array['is_invoice'] ? $array['is_invoice'] : '';
        $order['reference_price'] = $this->malfunctionPrice($array['malfunctions']);
        $order['actual_price'] = $order['reference_price'];
        
        //使用优惠券
        $this->useCoupon($array, $order);
        
        $order['invoice'] = $array['is_invoice']==1?$array['invoice']:'';
        $order['user_remark'] = '客户预约时间:'.$array['date'].';备注:'.$array['user_remark'];
        $order['malfunction_description'] = $array['malfunction_description']?$array['malfunction_description']:'';
        $orderId = M('order')->add($order);
        
        //回写优惠券使用的订单
        if ($orderId && !empty($array['coupon']) && $order['reference_price'] != $order['actual_price']) {
            $data = array('coupon_utime' => time(), 'coupon_orderid' => $orderId, 'coupon_status' => 2);
            M('preferential_coupon')->where(array('coupon_number' => $array['coupon']))->save($data);
        }
        
        $log = array();
        /** 订单ID */
        $log['order_id'] = $orderId;
        /** 时间 */
        $log['time'] = time();
        $log['action'] ='操作人:'.$array['name'].'--状态:下单';
        
        M('order_log')->add($log);
        
        if (!empty($array['coupon']) && $order['reference_price'] != $order['actual_price']) {
            $log['action'] = '客户使用优惠券:' . $array['coupon'];
            M('order_log')->add($log);
        } else if (!empty($array['coupon']) && $order['reference_price'] == $order['actual_price']){
            $log['action'] = '客户填写优惠券但无法使用:' . $array['coupon'];
            M('order_log')->add($log);
        }
        
        return array('orderId'=>$orderId,'orderNumber'=>$order['number']);
    }
    
    /**
     * 使用优惠券
     */
    private function useCoupon($data, &$order)
    {
        $coupon = trim($data['coupon']);
        $city = trim($order['city']);
        $phone_id = intval($data['phone_id']);
        $phomal_id = $data['malfunctions'];
        
        if (!$coupon) {
            return false;
        }
        
        if (!$city) {
            return false;
        }
        
        if (!$phone_id) {
            return false;
        }
        
        $phone = M('phone')->where(array('id' => $phone_id))->count();
        
        if (!$phone) {
            return false;
        }
        
        if (!$phomal_id) {
            return false;
        }
        
        $phomal_price = M('phone_malfunction')->where(array('id' => array('in', $phomal_id)))->getField('id, price_reference');
        
        if (!$phomal_price) {
            return false;
        }
        
        $item = M('preferential')->join('p left join preferential_coupon pc on pc.preferential_id = p.id')
                ->where(array('coupon_number' => $coupon))->find();
        
        if (!$item) {
            return false;
        }
        
        if ($item['coupon_status'] == 2) { //已使用
            return false;
        } else if ($item['status'] == -1) {//作废
            return false;
        } else if ($item['status'] == 0) {//待激活
            return false;
        }
        
        $time = time();
        
        if ($item['start_time'] > $time || $time > $item['end_time']) {//未开始或已过期
            return false;
        }
        
        //判断使用地区
        if ($city) {
            $address = M('preferential_address')->where(array('preferential_id' => $item['id']))->getField('address_id, preferential_id');
            
            if ($address && !isset($address[$city])) {
                return false;
            }
        }
        
        //判断机型地区
        if ($phone_id) {
            $phones = M('preferential_phone')->where(array('preferential_id' => $item['id']))->getField('phone_id, preferential_id');
            
            if ($phones && !isset($phones[$phone_id])) {
                return false;
            }
        }
        
        //判断故障地区
        $phomals = array();
        
        if ($phomal_id) {
            $phomals = M('preferential_phomal')->where(array('preferential_id' => $item['id']))->getField('phomal_id, preferential_id');
            $phomal_ids = is_array($phomal_id) ? $phomal_id : explode(',', $phomal_id);
            
            if ($phomals && !array_intersect($phomal_ids, array_keys($phomals))) {
                return false;
            }
        }
        
        if ($item['category'] == 1) { //代金券
            
            if ($order['actual_price'] >= $item['threshold_price']) {
                $order['actual_price'] -=  $item['price'];
            }
        } else if ($item['category'] == 2) {//打折券
            
            if ($phomals) { //限定故障
                
                $now_price = 0; //限定故障价格
                $other_price = 0; //其他故障价格
                
                foreach ($phomal_price as $id => $price) {
                    
                    if (isset($phomals[$id])) {
                        $now_price += $price;
                    } else {
                        $other_price += $price;
                    }
                }
                
                $order['actual_price'] = $other_price + round($now_price * $item['discount'] / 100, 2);
            } else { //不限定故障
                $order['actual_price'] = round($order['actual_price'] * $item['discount'] / 100, 2);
            }
        } else if ($item['category'] == 3) { //特价
            
            if ($phomals) { //限定故障
            
                $now_price = 0;
            
                foreach ($phomal_price as $id => $price) {
            
                    if (isset($phomals[$id])) {
                        $now_price += $item['price'];
                    } else {
                        $now_price += $price;
                    }
                }
            
                $order['actual_price'] = $now_price;
            }
        }
    }

    /**
     * 获取手机名称
     * @param id
     * @return array
     */
    public function phoneName($id)
    {
        $model = M('phone');
        $result = $model->find($id);
        return $result['alias'];
    }


    /**
     * 获取颜色名称
     * @param id
     * @return array
     */
    public function phonecolor($id)
    {
        $model = M('goods_color');
        $result = $model->find($id);
        return $result['name'];
    }


    /**
     * 获取公司详细地址
     * @param province
     * @return array
     */
    public function category($city)
    {
        $model = M('organization');
        $companyAddress = $model->where(['name'=>$city])->find();
        if ($companyAddress){
          return $companyAddress;
        }
    }

    /**
     * 添加订单信息到order_phone_malfunction
     * @param array
     * @param orderId
     * @return price
     */
    public function addMalfunction($malfunctions,$orderId)
    {
        $data = array();
        foreach($malfunctions as $key=>$phone_malfunction_id){
            $data[$key]['order_id'] = $orderId;
            $data[$key]['phomal_id'] = $phone_malfunction_id;
        }
       return M('order_phomal')->addAll($data);
    }

    /**
     * 获取故障价格
     * @param array || id
     * @return price
     */
    public function malfunctionPrice($malfunctions)
    {
        return M('phone_malfunction')->where(array('id' => array('in', $malfunctions)))->sum('price_reference');
    }

    /**
     * 小伙伴分成
     * @param $orderId
     * @return Boolean
     */
    public function frendParticipation($orderId)
    {
        $shareInfo = D('shareUser')->where(['user'=>$_POST['friendshare']])->find();

	if (empty($shareInfo)) { return fasle;}
        $data = array();
        $data['user_id'] = $shareInfo['id'];
        $data['order_id'] = $orderId;
        $data['status'] = 0;
        $data['start_time'] = time();
        $data['end_time'] = 0;
        $data['remark'] ='';
        $data['ratio'] = 10;
        D('share')->add($data);

        /** 第三方合作 */
        $this->thirdParty($_POST['friendshare'], $orderId);
    }

    /**
     * 下单追踪
     * @param $orderId
     * @return Boolean
     */
    public function orderTrace($order, $array)
    {
        $source = array();

        // 来源页面
        $source['origin'] = !empty($array['referrer']) ? $array['referrer'] : '';
        // 着路页面
        $source['dedark'] = $array['landUrl'];
        // ip
        $source['ip'] = get_client_ip(0, 1);
        // 合作伙伴
        $engine = $this->searchEngine($array['referrer']);
        $source['partner'] = $engine['from'];
        // 关键词
        $source['keyword'] = $engine['keyword'];
        // 魔法词
        $source['magic'] = !empty($array['tommagic']) ? $array['tommagic'] : '';
        /** 着路时间 */
        $source['start_time'] = time();
        // ip来源地址 (后台根据ip获取)
        import('ORG.Net.IpLocation');// 导入IpLocation类
        $locate = new \Org\Net\IpLocation('UTFWry.dat');
        $area = $locate->getlocation($source['ip']);
        $source['area'] = $area['country'];
        /** 下单时间 */
        $source['end_time'] = time();
        /** 订单编号 */
        $source['order_number'] = $order['orderNumber'];
        /** 省 */
        $source['province'] = !empty($array['province']) ? $array['province'] : '';
        /** 市 */
        $source['city'] = !empty($array['city']) ? $array['city'] : '';
        /** 区 */
        $source['county'] = !empty($array['area']) ? $array['area'] : '';
        /** 类型 */
        $source['type'] = 1;

        if (M('conversion')->add($source) === false) {
            \Think\Log::write('下单来源追踪写入错误[' . json_encode($source) . ']', ERR);
        }
    }

    /**
     * 判断搜索引擎
     *
     * @return void
     */
    private function searchEngine($url)
    {
        $keyword = '';
        $from = '';

        // 百度PC
        if (strstr($url, 'www.baidu.com')) { 
            preg_match("|baidu.+wo?r?d=([^\\&]*)|is", $url, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = '百度PC';
        } elseif (strstr($url, 'm.baidu.com')) { // 手机百度
            preg_match("|baidu.+wo?r?d=([^\\&]*)|is", $url, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = '手机百度';
        } elseif (strstr($url, 'tieba.baidu.com')) {// 百度贴吧
            preg_match("|baidu.+wo?r?d=([^\\&]*)|is", $url, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = '百度贴吧';
        } elseif (strstr($url, 'google.com') or strstr($url, 'google.cn')) { // 谷歌
            preg_match("|google.+q=([^\\&]*)|is", $url, $tmp );
            $keyword = urldecode($tmp[1]);
            $from = '谷歌';
        } elseif (strstr($url, 'haosou.com') or strstr($url, 'so.com')) {  // 360搜索
            preg_match("|so.+q=([^\\&]*)|is", $url, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = '360';
        } elseif (strstr($url, 'sogou.com')) { // 搜狗
            preg_match("|sogou.com.+query=([^\\&]*)|is", $url, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = '搜狗';
        } elseif (strstr($url, 'sm.cn')) { // 神马搜索
            preg_match("|sm.cn.+q=([^\\&]*)|is", $url, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = '神马搜索';
        } elseif (strstr($url, 'weixinbridge.com')){ // 微信
            $keyword = '';
            $from = '微信';
        } elseif (strstr($url, 'bing.com')) { // bing搜索
            preg_match("|bing.com.+query=([^\\&]*)|is", $url, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = '必应';
        }

        return array('keyword' => $keyword, 'from' => $from);
    }

    /**
     * 第三方合作
     * @param share
     * @param orderId
     * @return void
     */
    public function thirdParty($share, $orderId)
    {
        $SharePartner = C('SharePartner');
        $PartnerModel = C('PartnerModel');

        /** 订单关联合作伙伴 */
        if (in_array($share, $SharePartner)) {

            $data = array();
            $data['order_id'] = $orderId;
            $data['partner'] = $SharePartner[$share];
            M('order_partner')->add($data);
        }

        /** 合作商对应后续处理 */
        if (in_array($SharePartner[$share], $PartnerModel)) {
            $model = D($PartnerModel[$SharePartner[$share]]);
            $model->deal($orderId);
        }
    }

    /**
     * 历史订单
     * @param $id
     * @return Boolean
     */
    public function historyOrder($mobile,$page=0)
    {
        $page = $page*10;
        if ($page<0) $page =0;

        $countSql = "SELECT COUNT(*) as count FROM `order` as o
                      LEFT JOIN `customer` as c ON o.customer_id = c.id
                      LEFT JOIN `engineer` as e ON o.engineer_id = e.id WHERE c.cellphone= $mobile";

        $selectSql = "SELECT e.name as engineer_name,o.engineer_id,e.work_number,e.cellphone,o.number as order_number,
                      phone_name,o.color,o.malfunction_description,actual_price,p.img as phone_img,o.create_time
                      FROM `order` as o
                      LEFT JOIN `customer` as c ON o.customer_id = c.id
                      LEFT JOIN `phone` as p ON o.phone_id = p.id
                      LEFT JOIN `engineer` as e ON o.engineer_id = e.id WHERE c.cellphone= $mobile
                      ORDER BY o.id DESC
                      limit $page,10  ";

        return array('count'=>M()->query($countSql)['0']['count'],'list'=> M()->query($selectSql));
    }

    /*
    * 获取订单
    * */
    public function getOrder($mobile, $limit)
    {
        $model = M('order');
        $model->join('o left join `customer` c on o.customer_id = c.id');
        $model->join('left join `order_phomal` op on o.id = op.order_id');
        $model->join('left join `phone_malfunction` pm on pm.id = op.phomal_id');
        $model->join('left join `phone` p on p.id = o.phone_id');
        $model->join('left join `order_partner` opt on o.id = opt.order_id');
        $model->where(array('c.cellphone' => $mobile));
        $model->field('malfunction,actual_price as price_reference, o.number, o.create_time, c.cellphone, c.name, alias, p.img, o.status, opt.third_party_user_no');
        $model->group('o.id');
        $model->order('o.id desc');
        $model->limit($limit);
        $rst = $model->select();

        return $rst;
    }

    /*
    * 获取第三方订单
    * */
    public function getThreeOrder($mobile, $limit)
    {
        $model = M('order_partner');
        $model->join('opt left join `order` o on o.id = opt.order_id');
        $model->join('left join `customer` c on o.customer_id = c.id');
        $model->join('left join `order_phomal` op on o.id = op.order_id');
        $model->join('left join `phone_malfunction` pm on pm.id = op.phomal_id');
        $model->join('left join `phone` p on p.id = o.phone_id');
        $model->where(array('opt.third_party_user_no' => $mobile));
        $model->field('malfunction,actual_price as price_reference, o.number, o.create_time, c.cellphone, c.name, alias, p.img, o.status');
        $model->group('o.id');
        $model->order('o.id desc');
        $model->limit($limit);
        $rst = $model->select();

        return $rst;
    }

    /**
     * 创建验证码
     * @param  length
     * @return Boolean
     */
    function generate_code($length = 4) {
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);
    }

    /**
     * 验证订单号是否存在
     * @param  orderNumber
     * @return orderNumber
     */
    public function vOrder($orderNumber)
    {
        return  M('order')->where(['number'=>$orderNumber])->getField('number');
    }
}
