<?php

// +------------------------------------------------------------------------------------------
// | Author: TCG <tianchunguang@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: 工程师模型 Dates: 2016-09-28
// +------------------------------------------------------------------------------------------

namespace Admin\Model;

use Think\Model\RelationModel;

class EngineerModel extends RelationModel
{
    /**
     * 计算工程师收益
     *
     * @param $order_id
     * @return void
     */
    public function generateEngineerDivide($orderId)
    {
        if (M('engineer_divide')->where(array('order_id'=>$orderId))->find()) {
            return true;
        }
        
        /** 订单 -> 工程师 -> 故障 -> 收益*/
        $map = array();
        $map['o.id'] = $orderId;
        $order = M('order')->join('o left join order_phomal opm on opm.order_id = o.id')
                ->join('left join phone_malfunction pm on opm.phomal_id = pm.id')
                ->field('o.id, o.number, o.phone_name, o.color, o.engineer_id, group_concat(pm.malfunction) as malfunctions')
                ->where($map)->group('o.id')->find();
    
        /** 工程师 */
        $map = array();
        $map['e.id'] = $order['engineer_id'];
        $engineer = M('engineer')->join('e left join engineer_level el on el.id = e.level')
                    ->field('e.type, el.divide')->where($map)->find();
    
        if ($engineer['type'] == 2) {
            $divide = 'divide_platform';
        } else {
            $divide = 'divide_local';
        }
    
        /** 故障 -> 收益*/
        $divide = M('phone_malfunction')->join('pm left join order_phomal op on op.phomal_id = pm.id')
                ->where(array('op.order_id' => $orderId))->sum($divide);
        
        /** 写入数据 */
        $data = array();
        $data['order_id'] = $order['id'];
        $data['order_number'] = $order['number'];
        $data['order_name'] = $order['phone_name'] . '-' . $order['color'] . '-' . $order['malfunctions'];
        $data['engineer_id'] = $order['engineer_id'];
        $data['divide'] = $engineer['divide'];
        $data['earning'] = $divide;
        
        if (M('engineer_divide')->add($data) === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 退还工程师物料
     * 
     * @return void
     */
    public function refundFitting($orderId, $engineerId)
    {
        if (empty($orderId) || empty($engineerId)) {
            return false;
        }
        
        $map = array(
            'engineer_id' => $engineerId,
            'order_id' => $orderId,
        );
        
        $stocks = M('stock')->join('s left join fitting f on f.id = s.fitting_id')
                    ->where($map)->field("s.id, s.fitting_id, concat(f.title, '(', f.number, ')') as fitting")->select();
        
        if (!$stocks) {
            return true;
        }
        
        $flag = true;
        $engineer_inout = array();
        $stock_ids = array();
        $fittings = array();
        $strArr = array();
        
        foreach ($stocks as $stock) {
            
            $stock_ids[] = $stock['id'];
            
            if (!isset($fittings[$stock['fitting']])) {
                $fittings[$stock['fitting']] = 0;
            }
            $fittings[$stock['fitting']]++;
            
            $map = array(
                'engineer_id' => $engineerId,
                'fittings_id' => $stock['fitting_id'],
            );
            
            //工程师物料数量增加
            if (M('engineer_warehouse')->where($map)->setInc('amount', 1) === false) {
                $flag = false;
                break;
            }
            
            if (isset($engineer_inout[$stock['fitting_id']])) {
                $engineer_inout[$stock['fitting_id']]['amount']++;
            } else {
                $engineer_inout[$stock['fitting_id']] = array(
                    'type' => 1,
                    'inout' => 1,
                    'engineer_id' => $engineerId,
                    'fittings_id' => $stock['fitting_id'],
                    'order_id' => $orderId,
                    'amount' => 1,
                    'time' => time(),
                );
            }
        }
        
        foreach ($fittings as $fitting => $amount) {
            $strArr[] = $fitting . '*' . $amount;
        }
        
        //订单日志
        $action = '操作人：' . session('userInfo.username') . '--取消订单----退还物料:[' . implode('，', $strArr) . ']';
        if (D('order')->writeLog($orderId, $action) === false) {
            $flag = false;
        }
        
        //库存实体表还原
        if (!$stock_ids) {
            return false;
        }
        
        //工程师inout记录日志
        if (!$engineer_inout) {
            return false;
        }
        
        $where = array(
            'id' => array('in', $stock_ids)
        );
        
        if (M('stock')->where($where)->save(array('order_id' => 0, 'status' => 3, 'consume_time' => 0)) === false) {
            $flag = false;
        }
        
        if (M('engineer_inout')->addAll(array_values($engineer_inout)) === false) {
            $flag = false;
        }
        
        return $flag;
    }
    
    /**
     * 退还工程师废料
     *
     * @return void
     */
    public function refundWaste($orderId, $engineerId)
    {
        if (empty($orderId) || empty($engineerId)) {
            return false;
        }
        
        $rst = array();
    
        $map = array(
            'engineer_id' => $engineerId,
            'order_id' => $orderId,
        );
    
        $waste = M('waste_refund')->where($map)->find();
        
        if (!$waste || $waste['status'] == -2) {
            return true;
        }
        
        //没审核直接取消
        if ($waste['status'] < 1 || $waste['status'] == 2) {
            return M('waste_refund')->where($map)->setField('status', -2);
        }
        
        $flag = true;
        
        //取消当前记录
        if (M('waste_refund')->where($map)->setField('status', -2) === false) {
            $flag = false;
        }
        
        //扣除废料库存
        $wastes = json_decode($waste['wastes'], true);
        
        $waste_inout = array();
        
        foreach ($wastes as $val) {
            
            $where = array(
                'organization_id' => $waste['organization_id'],
                'waste_id' => $val['waste_id'],
            );
            
            $waste_warehouse = M('waste_warehouse')->where($where)->find();
            
            if ($waste_warehouse['amount'] < $val['amount']) {
                throw new \Exception('取消订单退还工程师废料错误：' . $val['name'] . '库存不足！');
            }
            
            //减库存
            if (M('waste_warehouse')->where($where)->setDec('amount', $val['amount']) === false) {
                $flag = false;
            }
            
            $where = array(
                'engineer_id' => $engineerId,
                'order_id' => $orderId,
                'organization_id' => $waste['organization_id'],
                'waste_id' => $val['waste_id'],
                'status' => 1,
            );
            
            //出库废料实体
            if (M('waste_stock')->where($where)->limit($val['amount'])->save(array('status' => -1, 'organization_id' => 0)) === false) {
                
                unset($where['order_id']);
                unset($where['engineer_id']);
                
                if (M('waste_stock')->where($where)->limit($val['amount'])->save(array('status' => -1, 'organization_id' => 0)) === false) {
                    $flag = false;
                }
            }
            
            $waste_inout[] = array(
                'type' => 1,
                'organization_id' => $waste['organization_id'],
                'waste_id' => $val['waste_id'],
                'user_id' => session('userId'),
                'engineer_id' => $waste['engineer_id'],
                'order_id' => $waste['order_id'],
                'inout' => 2,
                'amount' => $val['amount'],
                'wastes' => json_encode($val),
                'time' => time()
            );
        }
        
        //写入日志
        if ($waste_inout) {
            
            if (M('waste_inout')->addAll($waste_inout) === false) {
                $flag = false;
            }
        }
        
        return $flag;
    }

    /**
     * 根据工程师得到相关的额度
     *
     * @param $engineer_id 工程师ID
     * @return array array('quota' => '工程师总额度', 'warehouse' => '库存物料额度', 'apply' => '申请中的物料额度', 'wasteLock' => '废料未退回锁定的额度')
     */
    public function getEngineerQuota($engineer_id)
    {
        // 工程师总额度
        $quota = M('Engineer')
            ->join('AS `e` LEFT JOIN `engineer_level` AS `el` ON `e`.`level` = `el`.`id`')
            ->where(array('`e`.`id`' => $engineer_id))
            ->getField('quota');
        $quota = floatval($quota);

        // 库存的物料额度
        $map = array('`ew`.`engineer_id`' => $engineer_id, '`ew`.`amount`' => array('GT', 0));

        $warehouseTotal = M('EngineerWarehouse')
            ->join('AS `ew` LEFT JOIN `fitting` AS `f` ON `ew`.`fittings_id` = `f`.`id`')
            ->where($map)
            ->sum('`ew`.`amount` * `f`.`price`');
        $warehouseTotal = floatval($warehouseTotal);

        // 申请中的物料额度
        $applyTotal = M('apply')
            ->where(array('engineer_id' => $engineer_id, 'status' => 1, 'type' => 1))
            ->sum('worth');
        $applyTotal = floatval($applyTotal);

        // 未退回废料订单物料价值
        $wasteLockTotal = M('waste_refund')
            ->join('`wr` left join `engineer_inout` `ei` on `wr`.`order_id` = `ei`.`order_id` left join `fitting` `f` on `ei`.`fittings_id`=`f`.`id`')
            ->where(array('`wr`.`engineer_id`' => $engineer_id, '`wr`.`status`' => 0))
            ->sum("`ei`.`amount` * `f`.`price`");
        $wasteLockTotal = floatval($wasteLockTotal);

        return array('quota' => $quota, 'warehouse' => $warehouseTotal, 'apply' => $applyTotal, 'wasteLock' => $wasteLockTotal);
    }


    /**
     *
     * 获取所有工程师
     * @param null $map
     * @return mixed
     */
    public function getEngineers($map = NULL)
    {
        return $this->field('password, create_time, update_time', true)->where($map)->select();
    }

    /**
     *
     * 根据条件查找工程师信息
     * @param $map
     * @param $fields
     * @return mixed
     */
    public function getEngineer($map, $fields){

        return $this->getOne($map, $fields);
    }


    /**
     *
     * 本来用于common/baseModel 先临时用 后续整理
     * @param $map     array|string
     * @param $fields  可默认tp的filed字段里类型，array|string
     * @return mixed   获取到数据直接返回 或 NULL
     */
    public function getOne($map, $fields)
    {
        if(!$fields) {
            return $this->where($map)->find();
        }

        return $this->field($fields)->where($map)->find();

    }
}
