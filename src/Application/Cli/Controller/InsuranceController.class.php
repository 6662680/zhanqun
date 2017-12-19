<?php

// +------------------------------------------------------------------------------------------
// | Author: TCG <tianchunguang@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: 保险单 Dates: 2016-12-07
// +------------------------------------------------------------------------------------------

namespace Cli\Controller;

use Think\Controller;

class InsuranceController extends Controller
{
    /**
     * 执行更改保险单状态
     */
    public function updateInsuranceStatus()
    {
        // 0-下单未付款且超过两小时 => -1-取消
        $where = array(
            'status' => 0,
            'create_time' => array('lt', time() - 7200)
        );
        $model = M('phomal_insurance_order');
        $time = time();
        $status = C('INSURANCE_STATUS');
        
        $list = $model->where($where)->getField('id, create_time');
        
        if ($list) {
            $model->where(array('id' => array('in', array_keys($list))))->setField('status', '-1');
            
            $param = array();
            
            foreach ($list as $id => $val) {
                $param[] = array(
                    'phomal_insurance_order_id' => $id,
                    'time' => $time,
                    'action' => '客户下单两小时内未付款，系统自动取消订单--状态:' . $status['-1'],
                );
            }
            
            M('phomal_insurance_order_log')->addAll($param);
        }
        
        // 1-已付款 => 2-生效
        $where = array(
            'status' => 1,
            'effect_time' => array('elt', time()),
            'failure_time' => array('gt', time())
        );
        
        $list = $model->where($where)->getField('id, create_time');
        
        if ($list) {
            $model->where(array('id' => array('in', array_keys($list))))->setField('status', '2');
            
            $param = array();
            
            foreach ($list as $id => $val) {
                $param[] = array(
                    'phomal_insurance_order_id' => $id,
                    'time' => $time,
                    'action' => '客户已付款且已生效订单，系统自动切换订单状态为：' . $status['2'],
                );
            }
            
            M('phomal_insurance_order_log')->addAll($param);
        }
        
        // 2-生效 => 4-失效
        $where = array(
            'status' => 2,
            'order_id' => 0,
            'failure_time' => array('lt', time())
        );
        
        $list = $model->where($where)->getField('id, create_time');
        
        if ($list) {
            $model->where(array('id' => array('in', array_keys($list))))->setField('status', '4');
            
            $param = array();
            
            foreach ($list as $id => $val) {
                $param[] = array(
                    'phomal_insurance_order_id' => $id,
                    'time' => $time,
                    'action' => '客户已过期订单，系统自动切换订单状态为：' . $status['4'],
                );
            }
            
            M('phomal_insurance_order_log')->addAll($param);
        }
    }
}