<?php

// +------------------------------------------------------------------------------------------ 
// | Author: TCG <tianchunguang@weadoc.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 闪修侠保险订单  Dates: 2016-12-01
// +------------------------------------------------------------------------------------------

namespace Admin\Model;

use Think\Model;

class PhomalInsuranceModel extends Model
{
    /**
     * 生成保险单编号
     *
     * @return void
     */
    static public function createNumber()
    {
        // W 日期（年月日时分秒)+ 随机字母（位）
        // W 14 07 17 11 22 56 xxx
        return 'I' . date('ymdHis', time()) . chr(mt_rand(ord('a'), ord('z'))) . chr(mt_rand(ord('a'), ord('z'))) . chr(mt_rand(ord('a'), ord('z')));
    }
    
    /**
     * 添加保险单日志
     * @param   int $order_id
     * @param   string $action
     * @return  boolean
     */
    public function writeLog($order_id, $action)
    {
        $data['phomal_insurance_order_id'] = $order_id;
        $data['time'] = time();
        $data['action'] = $action;
        
        return M('phomal_insurance_order_log')->add($data);
    }
    
    /**
     * 创建保险单
     */
    public function addInsuranceOrder($old_order_id, $insurance_id, $engineer_id = 0)
    {
        $old_order_id = (int)$old_order_id;
        $engineer_id = (int)$engineer_id;
        $insurance_id = (int)$insurance_id;
        
        if ($old_order_id <= 0 || $insurance_id <= 0)
        {
            return false;
        }
        
        $order = M('order')->where(array('id' => $old_order_id))->field('status, customer, cellphone, type, clearing_time')->find();
        
        if (!$order) {
            throw new \Exception('订单记录不存在，无法购买保险！');
        }
        
        if ($order['status'] != 6) {
            throw new \Exception('订单未入库，无法购买保险！');
        }
        
        if (!in_array($order['type'], array(1, 2, 5))) {
            throw new \Exception('此维修订单没有提供购买保险服务！');
        }
        
        if (M('order_partner')->where(array('order_id' => $old_order_id))->count()) {
            throw new \Exception('第三方维修订单没有提供购买保险服务！');
        }
        
        //保险限定在三天内购买
        if ($order['clearing_time'] + 259200 < time()) {
            throw new \Exception('不好意思，保险必须在订单入库后的3天内购买！');
        }
        
        $insurance = M('phomal_insurance')->where(array('id' => $insurance_id))->field('id, price, duration')->find();
         
        if (!$insurance) {
            throw new \Exception('此维修订单没有可以购买的保险服务！');
        }
        
        $item = M('phomal_insurance_order')->where(array('old_order_id' => $old_order_id))->find();
        
        if ($item) { //已下单
            
            if ($item['engineer_id'] != $engineer_id) {
                M('phomal_insurance_order')->where(array('id' => $item['id']))->setField('engineer_id', $engineer_id);
            }
            
            //下单未付款且在2小时内--返回当前保险id
            if ($item['status'] == 0 && $item['create_time'] + 7200 > time()) {
                return $item;
            }
            
            //status > 0
            if ($item['status'] > 0) {
                throw new \Exception('您已购买过保险服务了！');
            }
            
            $param = array(
                'create_time' => time(),
                'status' => 0,
                'price' => $insurance['price'],
                'phomal_insurance_id' => $insurance_id,
                'effect_time'  => strtotime('tomorrow'),
                'failure_time' => strtotime(date('Y-m-d 23:59:59.999', strtotime($insurance['duration'] . " year"))),
            );
            
            if (M('phomal_insurance_order')->where(array('id' => $item['id']))->save($param) !== false) {
                return $item;
            }
            
            return false;
        } else { //未下单
            $param = array(
                'number' => $this->createNumber(),
                'price'  => $insurance['price'],
                'create_time' => time(),
                'phomal_insurance_id' => $insurance['id'],
                'customer' => ($order['customer'] ? $order['customer'] : ''),
                'cellphone' => ($order['cellphone'] ? $order['cellphone'] : ''),
                'engineer_id' => $engineer_id,
                'old_order_id' => $old_order_id,
                'status' => 0,
                'effect_time'  => strtotime('tomorrow'),
                'failure_time' => strtotime(date('Y-m-d 23:59:59.999', strtotime($insurance['duration'] . " year"))),
            );
            
            $param['id'] = M('phomal_insurance_order')->add($param);
            
            if ($param['id'] === false) {
                return false;
            }
            
            return $param;
        }
    }
    
    /**
     * 保险付款
     */
    public function payInsuranceOrder($param) 
    {
        $id = (int) $param['id'];
        $number = trim($param['number']);
        
        if ($id <= 0 || !$number) {
            return false;
        }
        
        $map = array('id' => $id, 'number' => $number);
        
        $insurance = M('phomal_insurance_order')->where($map)->find();
        
        if (!$insurance) {
            \Think\Log::record('查询到保险单--保险单ID:' . $id, 'ERR');
            return false;
        }
        
        //已付款
        if ($insurance['status'] >= 1) {
            return true;
        }
        
        //取消或两小时内未付款
        if ($insurance['status'] < 0 || ($insurance['status'] == 0 && $insurance['create_time'] + 7200 < time())) {
            return false;
        }
        
        $data = array();
        $data['status'] = 1; //已付款
        $data['pay_time'] = time();
        $data['pay_price'] = $param['pay_price'] ? $param['pay_price'] : 0;
        $data['pay_account'] = $param['pay_account'] ? $param['pay_account'] : '';
        $data['pay_number'] = $param['pay_number'] ? $param['pay_number']  : '';
        
        $flag = true;
        M()->startTrans();
        
        //修改付款信息
        if (M('phomal_insurance_order')->where($map)->save($data) === false) {
            \Think\Log::record('修改保险单付款信息失败--保险单ID:' . $id, 'ERR');
            $flag = false;
        }
        
        //日志
        if ($this->writeLog($id, '客户付款--付款账号：'. $param['pay_account'].'--付款金额:' . $param['pay_price'] . '--交易号:'.$param['pay_number']) === false) {
            $flag = false;
        }
        
        //计算工程师分成
        if ($insurance['engineer_id'] > 0) {
            $where = array(
                'insurance_order_id' => $id,
                'engineer_id' => $insurance['engineer_id'],
            );
            
            if (M('engineer_insurance_divide')->where($where)->count() < 1) {
                $divide = M('phomal_insurance')->where(array('id' => $insurance['phomal_insurance_id']))->getField('divide');
                
                $param = array(
                    'engineer_id' => $insurance['engineer_id'],
                    'insurance_order_id' => $id,
                    'insurance_number' => $insurance['number'],
                    'insurance_name' => '客户购买保险',
                    'earning' => $divide,
                    'is_clear' => 0,
                );
                
                if ($divide > 0 && M('engineer_insurance_divide')->add($param) === false) {
                    \Think\Log::record('计算保险单工程师分成失败--保险单ID:' . $id.'--工程师ID:' . $insurance['engineer_id'], 'ERR');
                    $flag = false;
                }
            }
        }
        
        if ($flag) {
            M()->commit();
        } else {
            M()->rollback();
        }
        
        return $flag;
    }
    
    /**
     * 原订单取消--对应保险取消
     */
    public function cancelPhomalInsuranceOrder($old_order_id)
    {
        $old_order_id = (int) $old_order_id;
        
        if ($old_order_id <= 0) {
            return false;
        }
        
        $map = array(
            'old_order_id' => $old_order_id,
            'status' => array('egt', 0)
        );
        
        $item = M('phomal_insurance_order')->where($map)->find();
        
        if (!$item) {
            return true;
        }
        
        if (($item['status'] == 1 && $item['effect_time'] <= time()) || $item['status'] > 1) {
            return false;
        }
        
        $map = array('id' => $item['id']);
        
        if (M('phomal_insurance_order')->where($map)->setField('status', '-1') === false) {
            return false;
        }
        
        //取消工程师分成
        $where = array('insurance_order_id' => $item['id']);
        
        if (M('engineer_insurance_divide')->where($where)->count()) {
            
            if (M('engineer_insurance_divide')->where($where)->delete() === false) {
                return false;
            }
        }
        
        $action = "操作人：".session('userInfo.username').'--手动取消原维修订单--同步取消保险单';
        
        if ($this->writeLog($item['id'], $action) === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 根据保险单生成维修订单
     */
    public function insuranceToMaintainOrder($insurance_id)
    {
        $map = array(
            'id' => $insurance_id,
        );
        
        $item = M('phomal_insurance_order')->where($map)->field('old_order_id, order_id, phomal_insurance_id')->find();
        
        if (!$item || !$item['old_order_id']) {
            return false;
        }
        
//        if ($item['order_id'] > 0) {
//            return $item['order_id'];
//        }

        $where = array('id' => $item['old_order_id']);
        
        $order = M('order')->where($where)
                ->field('customer_id, customer, cellphone, phone_id, phone_name, phone_imei, category, 
                    color_id, color, province, city, county, address, malfunction_description, create_time')
                ->find();
            
        if (!$order) {
            return false;
        }
        
        $order['number'] = D('Admin/order')->createNumber();
        $order['status'] = 1;
        $order['type'] = 5;//保险
        $order['create_time'] = time();
        
        $where = array('phomal_insurance_id' => $item['phomal_insurance_id']);
        $phomals = M('phomal_insurance_phomals')->where($where)->select();
        
        if (!$phomals) {
            return false;
        }
        
        //产生维修单
        $order_id = M('order')->add($order);
        
        if ($order_id === false) {
            return false;
        }
        
        //维修单故障
        $params = array();
        
        foreach ($phomals as $param) {
            $params[] = array('order_id' => $order_id, 'phomal_id' => $param['phomal_id']);
        }
        
        if (M('order_phomal')->addAll($params) === false) {
            return false;
        }
        
        //维修单日志
        $action = '操作人：' . session('userInfo.username') . '--保险单出险审核通过产生保险维修单--状态：' . C('ORDER_STATUS')[$order['status']];
        
        if (D('Admin/order')->writeLog($order_id, $action) === false) {
            return false;
        }
        
        return $order_id;
    }
}
