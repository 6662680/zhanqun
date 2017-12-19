<?php

// +------------------------------------------------------------------------------------------ 
// | Author: liyang <664577655@qq.com>
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 订单验证模型 Dates: 2016-08-4
// +------------------------------------------------------------------------------------------

namespace V2\Model;


use Think\Model;

class OrderModel extends Model
{
    //自动映射
    protected $_map=[
        'name'=>'customer',
        'mobile'=>'cellphone'
    ];
    //自动完成
    protected $_auto=[
        ['status',1],
        ['create_time','time',1,'function'],
        ['is_favorable',0,1],
    ];

    /**获取客户订单
     * @param $where
     */
    public function getCustomerOrders($where)
    {
        $list=$this->join('o left join engineer e on e.id = o.engineer_id')
            ->join('left join `phone` p on p.id = o.phone_id')
            ->join('left join order_phomal opm on opm.order_id = o.id')
            ->join('left join phone_malfunction pm on opm.phomal_id = pm.id')
            ->join('left join malfunction m on pm.malfunction_id = m.id')
            ->field('o.id, o.number, o.create_time, o.reference_price, o.actual_price, o.status, o.cellphone, o.color as color_name, o.pay_type, o.paid_amount, 
                    e.name as engineer_name, e.cellphone as engineer_phone, p.alias as phone_name, p.img, group_concat(m.name) as malfunctions')
            ->where($where)->group('o.id')->order('o.id asc')->select();
        return $list;
    }




    /**
     * 插入数据前的相关处理
     * @param $data
     * @return bool
     */
    protected function before_insert(&$data)
    {
        $data['address']=D('address')->getDetailAddress(
            $data['province'], $data['city'], $data['area']
        ).$data['detailed_address'];
        if(!$data['customer_id']=D('customer')->addCustomer($data)){
            return false;
        }
        if ($data['is_invoice']==1 && empty($data['invoice'])){
            return false;
        }

        $data['date'] = $data['date']?date('Y-m-d H:i:s', $data['date']):'无';
        $data['type'] = isset($data['type']) ? $data['type'] : 1;
        $data['color'] = isset($data['color_id'])? $this->phoneColor($data['color_id']):'';
        $data['phone_name'] = isset($data['phone_id'])?$this->phoneName($data['phone_id']):'';
        $data['city'] = $data['category'] != 1?$data['mailCiyt']:$data['city'];
        $data['actual_price']=$data['reference_price'] = $this->malfunctionPrice($data['malfunctions']);
        $data['user_remark'] = '客户预约时间:'.$data['date'].';备注:'.$data['user_remark'];
        $data['number'] = $this->createOrderSn();

        //使用优惠券
        $this->useCoupon($data);

        return true;

    }

    /**
     * 订单入库
     * @param $data
     * @return bool
     */
    public function addOrder($data)
    {
        $this->startTrans();
        if($this->before_insert($data)) {
            $this->create($data);
            if ($orderId=$this->add()) {
                $data['orderId']=$orderId;
                if($this->after_insert($data)){
                    $this->commit();
                    return $data;
                }

            }
        }
        $this->rollback();
        $this->error='下单失败';
        return false;
    }

    /**
     * 添加数据后处理
     * @param $data
     * @param $orderId
     * @return bool\
     */
    protected function after_insert($data)
    {
        $orderId=$data['orderId'];
        //回写优惠券使用的订单
        if ($orderId && !empty($data['coupon']) && $data['reference_price'] != $data['actual_price']) {
            $data = array('coupon_utime' => time(), 'coupon_orderid' => $orderId, 'coupon_status' => 2);
            M('preferential_coupon')->where(array('coupon_number' => $data['coupon']))->save($data);
        }
        //日志处理
        $log = array();
        $log['order_id'] = $orderId;
        $log['time'] = time();
        $log['action'] ='操作人:'.$data['name'].'--状态:下单';
        M('order_log')->add($log);
        if (!empty($data['coupon'])) {
            $log['action'] = $data['reference_price'] != $data['actual_price']?'客户使用优惠券:' . $data['coupon']
                : '客户填写优惠券但无法使用:' . $data['coupon'];
            M('order_log')->add($log);
        }
        //添加到故障信息表
        if(!$this->addMalfunction($data['malfunctions'],$orderId)){
            return false;
        }
        /*小伙伴分成*/
        if (!empty($data['friendshare'])) {
            $this->frendParticipation($orderId,$data['friendshare']);
        }

        /** 下单来源 */
        if (!empty($data['landUrl'])) {
            $this->orderTrace($data);
        }

        return true;
    }

    /**
     * 下单追踪
     * @param $orderId
     * @return Boolean
     */
    public function orderTrace($data)
    {
        $source = array();
        // 来源页面
        $source['origin'] =$data['referrer'];
        // 着路页面
        $source['dedark'] = $data['landUrl'];
        // ip
        $source['ip'] = get_client_ip();
        // 合作伙伴
        $engine = searchEngine($data['referrer']);
        $source['partner'] = $engine['from'];
        // 关键词
        $source['keyword'] = $engine['keyword'];
        // 魔法词
        $source['magic'] =$data['tommagic'];
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
        $source['order_number'] = $data['number'];
        /** 省 */
        $source['province'] =$data['province'];
        /** 市 */
        $source['city'] = $data['city'];
        /** 区 */
        $source['county'] = $data['area'];
        /** 类型 */
        $source['type'] = 1;
        if (M('conversion')->add($source) === false) {
            \Think\Log::write('下单来源追踪写入错误[' . json_encode($source) . ']', ERR);
        }
    }


    /**
     * 小伙伴分成
     * @param $orderId
     * @return Boolean
     */
    public function frendParticipation($orderId,$friendshare)
    {
        $shareInfo = D('shareUser')->where(array('user'=>$friendshare))->find();
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
        $this->thirdParty($friendshare, $orderId);
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
     * 添加订单信息到order_phone_malfunction
     * @param array
     * @param orderId
     * @return price
     */
    public function addMalfunction($malfunctions,$orderId)
    {
        $data = array();
        $malfunctions=is_array($malfunctions)?$malfunctions:explode(',',$malfunctions);
        foreach($malfunctions as $key=>$phone_malfunction_id){
            $data[$key]['order_id'] = $orderId;
            $data[$key]['phomal_id'] = $phone_malfunction_id;
        }
        return M('order_phomal')->addAll($data);
    }

    /**
     * 创建订单号
     * @return string
     */
    public function createOrderSn()
    {

        return 'W' . date('ymdHis', time()) . chr(mt_rand(ord('a'), ord('z')))
            . chr(mt_rand(ord('a'), ord('z')))
            . chr(mt_rand(ord('a'), ord('z')));
    }

    /**
     * 获取颜色名称
     * @param id
     * @return array
     */
    protected function phoneColor($id)
    {
        return M('goods_color')->where(array('id'=>$id))->getField('name');
    }

    /**
     * 获取手机名称
     * @param id
     * @return array
     */
    protected function phoneName($id)
    {
        return M('phone')->where(array('id'=>$id))->getField('alias');
    }

    /**
     * 获取故障价格
     * @param array || id
     * @return price
     */
    protected function malfunctionPrice($malfunctions)
    {
        return M('phone_malfunction')->where(array('id' => array('in', $malfunctions)))
            ->sum('price_reference');
    }

    /**
     * 使用优惠券
     */
    private function useCoupon(&$data)
    {
        $coupon = trim($data['coupon']);
        $city = trim($data['city']);
        $phone_id = intval($data['phone_id']);
        $phomal_id = $data['malfunctions'];

        if (!$coupon || !$city || !$phone_id) {
            return false;
        }
        $phone = M('phone')->where(array('id' => $phone_id))->count();

        if (!$phone || !$phomal_id) {
            return false;
        }
        if(!$phomal_price = M('phone_malfunction')->where(array('id' => array('in', $phomal_id)))
            ->getField('id, price_reference')){
            return false;
        }

        if(!$item = M('preferential')->join('p left join preferential_coupon pc on pc.preferential_id = p.id')
            ->where(array('coupon_number' => $coupon))->find()){
            return false;
        }
        //已使用,作废,待激活
        if ($item['coupon_status'] == 2 || in_array($item['status'],[-1,0])) {
            return false;
        }
        if ($item['start_time'] > time() || time() > $item['end_time']) {//未开始或已过期
            return false;
        }
        //判断使用地区
        $address = M('preferential_address')->where(array('preferential_id' => $item['id']))
            ->getField('address_id, preferential_id');

        if ($address && !isset($address[$city])) {
            return false;
        }
        //判断机型ID
        $phones = M('preferential_phone')->where(array('preferential_id' => $item['id']))
            ->getField('phone_id, preferential_id');

        if ($phones && !isset($phones[$phone_id])) {
            return false;
        }
        //判断故障地区
        $phomals = array();
        $phomals = M('preferential_phomal')->where(array('preferential_id' => $item['id']))
            ->getField('phomal_id, preferential_id');
        $phomal_ids = is_array($phomal_id) ? $phomal_id : explode(',', $phomal_id);
        if ($phomals && !array_intersect($phomal_ids, array_keys($phomals))) {
            return false;
        }
        if ($item['category'] == 1) { //代金券
            if ($data['actual_price'] >= $item['threshold_price']) {
                $data['actual_price'] -=  $item['price'];
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
                $order['actual_price'] = round($data['actual_price'] * $item['discount'] / 100, 2);
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
}
