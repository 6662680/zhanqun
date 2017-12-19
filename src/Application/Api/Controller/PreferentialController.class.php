<?php
// +------------------------------------------------------------------------------------------
// | Author: TCG <tianchunguang@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: 优惠   Dates: 2017-02-07
// +------------------------------------------------------------------------------------------

namespace Api\Controller;

use Api\Controller;

class PreferentialController extends BaseController
{
    /**
     * 检查优惠券是否可用
     */
    public function couponIsAvailable()
    {
        $coupon = trim(I('post.coupon')); //优惠券
        $city = trim(I('post.city')); //使用地区
        $phone_id = I('post.phone_id/d'); //机型ID
        $phomal_id = trim(I('post.phomal_id')); //机型故障ID
        
        $result = array();
        
        if (!$coupon) {
            $result['status'] = 0;
            $result['data'] = '请输入优惠券码！';
            $this->_callBack($result);
        }
        
        if (!$city) {
            $result['status'] = 0;
            $result['data'] = '请选择您所在的地区！';
            $this->_callBack($result);
        }
        
        if (!$phone_id) {
            $result['status'] = 0;
            $result['data'] = '请选择您的手机机型！';
            $this->_callBack($result);
        }
        
        $phone = M('phone')->where(array('id' => $phone_id))->count();
        
        if (!$phone) {
            $result['status'] = 0;
            $result['data'] = '未知的手机机型信息！';
            $this->_callBack($result);
        }
        
        if (!$phomal_id) {
            $result['status'] = 0;
            $result['data'] = '请选择您手机出现的故障问题！';
            $this->_callBack($result);
        }
        
        $phomal_price = M('phone_malfunction')->where(array('id' => array('in', $phomal_id)))->getField('id, price_reference');
        
        if (!$phomal_price) {
            $result['status'] = 0;
            $result['data'] = '未知的手机故障信息！';
            $this->_callBack($result);
        }
        
        $item = M('preferential')->join('p left join preferential_coupon pc on pc.preferential_id = p.id')
                ->where(array('coupon_number' => $coupon))->find();
        
        if (!$item) {
            $result['status'] = 0;
            $result['data'] = '抱歉！查不到优惠券信息！';
            $this->_callBack($result);
        }
        
        if ($item['coupon_status'] == 2) { //已使用
            $result['status'] = 0;
            $result['data'] = '优惠券已使用过了！';
            $this->_callBack($result);
        } else if ($item['status'] == -1) {//作废
            $result['status'] = 0;
            $result['data'] = '优惠券已停止使用了！';
            $this->_callBack($result);
        } else if ($item['status'] == 0) {//待激活
            $result['status'] = 0;
            $result['data'] = '优惠券还未激活，无法使用哦！';
            $this->_callBack($result);
        }
        
        $time = time();
        
        if ($item['start_time'] > $time || $time > $item['end_time']) {//未开始或已过期
            $result['status'] = 0;
            $result['data'] = '抱歉！优惠券有效时间为：' . date('Y-m-d H:i', $item['start_time']) . ' 至 ' . date('Y-m-d H:i', $item['end_time']);
            $this->_callBack($result);
        }
        
        //判断使用地区
        if ($city) {
            $address = M('preferential_address')->where(array('preferential_id' => $item['id']))->getField('address_id, preferential_id');
            
            if ($address && !isset($address[$city])) {
                $result['status'] = 0;
                $result['data'] = '抱歉，当前地区不能使用此优惠券！';
                $this->_callBack($result);
            }
        }
        
        //判断机型地区
        if ($phone_id) {
            $phones = M('preferential_phone')->where(array('preferential_id' => $item['id']))->getField('phone_id, preferential_id');
            
            if ($phones && !isset($phones[$phone_id])) {
                $result['status'] = 0;
                $result['data'] = '抱歉，当前机型不能使用此优惠券！';
                $this->_callBack($result);
            }
        }
        
        //判断故障地区
        $phomals = array();
        
        if ($phomal_id) {
            $phomals = M('preferential_phomal')->join('pp left join phone_malfunction pf on pf.id = pp.phomal_id')->where(array('preferential_id' => $item['id']))->getField('pp.phomal_id, pf.malfunction');
            $phomal_ids = explode(',', $phomal_id);
            
            if ($phomals && !array_intersect($phomal_ids, array_keys($phomals))) {
                $result['status'] = 0;
                $result['data'] = '抱歉，当前故障不能使用此优惠券！';
                $this->_callBack($result);
            }
        }
        
        if ($item['category'] == 1) { //代金券
            $phomal_price = round(array_sum($phomal_price), 2);
            
            if ($phomal_price >= $item['threshold_price']) {
                $result['status'] = 1;
                $result['data']['category'] = 1;
                $result['data']['price'] = $item['price'];
                $result['data']['info'] = '-￥'. $item['price'];
                $result['data']['new_price'] = $phomal_price - $item['price'];
                $this->_callBack($result);
            } else {
                $result['status'] = 0;
                $result['data'] = '抱歉，订单总金额未达到使用优惠券的最低价格！';
                $this->_callBack($result);
            }
        } else if ($item['category'] == 2) {//打折券
            
            if ($phomals) { //限定故障
                
                $now_price = 0; //限定故障价格
                $other_price = 0; //其他故障价格
                $preferential_phomals = array();
                
                foreach ($phomal_price as $id => $price) {
                    
                    if (isset($phomals[$id])) {
                        $preferential_phomals[] = $phomals[$id];
                        $now_price += $price;
                    } else {
                        $other_price += $price;
                    }
                }
                
                $result['status'] = 1;
                $result['data']['category'] = 2;
                $result['data']['discount'] = $item['discount'];
                $result['data']['info'] = implode(',', $preferential_phomals) . ' ' . $item['discount'] . '折';
                $result['data']['new_price'] = $other_price + round($now_price * $item['discount'] / 100, 2);
                $this->_callBack($result);
            } else { //不限定故障
                $phomal_price = round(array_sum($phomal_price), 2);
                
                $result['status'] = 1;
                $result['data']['category'] = 2;
                $result['data']['discount'] = $item['discount'];
                $result['data']['info'] = $item['discount'] . '折';
                $result['data']['new_price'] = round($phomal_price * $item['discount'] / 100, 2);
                $this->_callBack($result);
            }
        } else if ($item['category'] == 3) { //特价
            
            if ($phomals) { //限定故障
            
                $now_price = 0;
                $preferential_phomals = array();
            
                foreach ($phomal_price as $id => $price) {
            
                    if (isset($phomals[$id])) {
                        $now_price += $item['price'];
                        $preferential_phomals[] = $phomals[$id];
                    } else {
                        $now_price += $price;
                    }
                }
            
                $result['status'] = 1;
                $result['data']['category'] = 3;
                $result['data']['info'] = implode(',', $preferential_phomals) . ' 特价￥' . $item['price'];
                $result['data']['new_price'] = $now_price;
                $this->_callBack($result);
            } else { //不限定故障
                $result['status'] = 0;
                $result['data'] = '抱歉，此优惠券无法使用！';
                $this->_callBack($result);
            }
        }
        
        $result['status'] = 0;
        $result['data'] = '抱歉，无法获取优惠券信息，无法使用优惠券！';
        $this->_callBack($result);
    }
}