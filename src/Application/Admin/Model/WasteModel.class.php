<?php

// +------------------------------------------------------------------------------------------ 
// | Author: liyang <664577655@qq.com>
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 进出库模型 Dates: 2016-09-28
// +------------------------------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;
class WasteModel extends Model
{
    /**
     * 发货
     * 
     * @author tianchunguang
     * @return void
     */
    public function inout($data)
    {
        $rst = array('success' => true);
        
        if (!$data || !$data['id']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '调拨记录不存在！';
            return $rst;
        }
        
        $wastes = json_decode($data['wastes'], true);
        
        if (empty($wastes)) {
            $rst['success'] = false;
            $rst['errorMsg'] = '调拨记录没有需要调拨的废料！';
            return $rst;
        }
        
        $organization_id = 0; //发货组织
        
        if ($data['type'] == 1) { //申请
            $organization_id = (int) $data['auditor_org'];
        } else if ($data['type'] == 2) { //退还
            $organization_id = (int) $data['proposer_org'];
        } 
        
        if ($organization_id <= 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '调拨记录仓库数据异常！';
            return $rst;
        }
        
        $stockModel = M('waste_stock');
        
        M()->startTrans();

        /*循环寻找物料是否存在,按时间减去数据库的废料*/
        foreach ($wastes as &$value) {

            /*修改warehouse数量*/
            $map = array();
            $map['waste_id'] = $value['waste_id'];
            $map['organization_id'] = $organization_id;

            $amount = M('waste_warehouse')->where($map)->getField('amount');
            $amount = $amount - $value['amount'];

            if ($amount < 0) {
                $rst['success'] = false;
                $rst['errorMsg']= '废料(' . $value['phone'].$value['name'] .')数量不足！';
                break;
            }

            if (M('waste_warehouse')->where($map)->setField('amount', $amount) === false) {
                $error['success'] = false;
                $rst['errorMsg'] = '更新废料剩余库存出错！';
                break;
            }

            /*查询stock实体*/
            $map = array();
            $map['waste_id'] = $value['waste_id'];
            $map['status']= 1;
            $map['organization_id'] = $organization_id;
            $stocks = $stockModel->where($map)->order('id asc')->limit($value['amount'])->select();

            if (count($stocks) < $value['amount']) {
                $rst['success'] = false;
                $rst['errorMsg']= '废料(' . $value['phone']. $value['name'] .')实体数量不足！';
                break;
            }
            
            $json = array();

            foreach ($stocks as $k => $v) {
                
                if ($stockModel->where(array('id' => $v['id']))->save(array('organization_id' => 0, 'status' => 2)) === false) {
                    $rst['success'] = false;
                    $rst['errorMsg']= '更新废料数据状态失败！';
                    break 2;
                }

                /*为日志添加number*/
                $json[$k]['id'] = $v['id'];
                $json[$k]['number'] = $v['number'];

                /*为订单添加number*/
                $value['number'][] = $v['number'];
            }

            /*发货，添加到日志*/
            $log['type'] = 2;
            $log['organization_id'] = $organization_id;
            $log['waste_id'] = $value['waste_id'];
            $log['user_id'] = session('userId');
            $log['amount'] = $value['amount'];
            $log['inout'] = 2;
            $log['residue'] = $amount;
            $log['wastes']  = json_encode($json);
            $log['price'] = $v['price'];
            $log['order_id'] = 0;
            $log['engineer_id'] = 0;
            $log['time'] = time();

            if (M('waste_inout')->add($log) === false) {
                $rst['success'] = false;
                $rst['errorMsg']= '更新废料数据添加日志失败！';
                break;
            }
        }
        
        if ($rst['success']) {
            
            if (M('waste_allot')->where(array('id' => $data['id']))->save(array('wastes' => json_encode($wastes), 'status' => 2)) === false) {
                $rst['success'] = false;
                $rst['errorMsg']= '更新废料调拨发货信息失败！';
            }
        }

        if ($rst['success']) {
            M()->commit();
        } else {
            M()->rollback();
        }
        
        return $rst;
    }

    /**
     * 收货
     * @author tianchunguang
     * @return void
     */
    public function accept($data)
    {
        $rst = array('success' => true);
    
        if (!$data || !$data['id']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '调拨记录不存在！';
            return $rst;
        }
    
        $wastes = json_decode($data['wastes'], true);
    
        if (empty($wastes)) {
            $rst['success'] = false;
            $rst['errorMsg'] = '调拨记录没有需要调拨的废料！';
            return $rst;
        }
    
        $organization_id = 0; //收货组织
    
        if ($data['type'] == 1) { //申请
            $organization_id = (int) $data['proposer_org'];
        } else if ($data['type'] == 2) { //退还
            $organization_id = (int) $data['auditor_org'];
        }
    
        if ($organization_id <= 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '调拨记录仓库数据异常！';
            return $rst;
        }
    
        $stockModel = M('waste_stock');
    
        M()->startTrans();
    
        /*循环寻找物料是否存在,按时间减去数据库的废料*/
        foreach ($wastes as $key => $value) {
            $map = array();
            $map['number'] = array('in', $value['number']);
            $map['status'] = 2;
            $map['organization_id'] = 0;
            $stocks = $stockModel->where($map)->select();
            
            if (count($stocks) != (int) $value['amount']) {
                $rst['success'] = false;
                $rst['errorMsg'] = '仓库发货数据与实际数量不符！';
                break;
            }
            
            $json = array();
            
            foreach ($stocks as $k => $v) {
                
                if ($stockModel->where(array('id' => $v['id']))->save(array('organization_id' => $organization_id, 'status' => 1)) === false) {
                    $rst['success'] = false;
                    $rst['errorMsg']= '更新废料数据状态失败！';
                    break 2;
                }

                /*为日志添加number*/
                $json[$k]['id'] = $v['id'];
                $json[$k]['number'] = $v['number'];
            }

            /*收货，修改warehouse*/
            $map = array();
            $map['waste_id'] = $value['waste_id'];
            $map['organization_id'] = $organization_id;
            $waste_warehouse = M('waste_warehouse')->where($map)->field('amount')->find();
            
            if ($waste_warehouse) {
                $amount = $waste_warehouse['amount'] + $value['amount'];
                
                if (M('waste_warehouse')->where($map)->setField('amount', $amount) === false) {
                    $rst['success'] = false;
                    $rst['errorMsg']= '更新废料库存失败！';
                    break;
                }
            } else {
                $param = array(
                    'organization_id' => $organization_id,
                    'waste_id' => $value['waste_id'],
                    'amount' => $value['amount'],
                );
                
                $amount = $value['amount'];
                
                if (M('waste_warehouse')->add($param) === false) {
                    $rst['success'] = false;
                    $rst['errorMsg']= '更新废料库存失败！';
                    break;
                }
            }
            
            $log['type'] = 2;
            $log['organization_id'] = $organization_id;
            $log['waste_id'] = $value['waste_id'];
            $log['user_id'] = session('userId');
            $log['amount'] = $value['amount'];
            $log['inout'] = 1;
            $log['residue'] = $amount;
            $log['price'] = $v['price'];
            $log['wastes']  = json_encode($json);
            $log['order_id'] = 0;
            $log['engineer_id'] = 0;
            $log['time'] = time();
            
            if (M('waste_inout')->add($log) === false) {
                $rst['success'] = false;
                $rst['errorMsg']= '更新废料数据添加日志失败！';
                break;
            }
        }

        if ($rst['success']) {
        
            if (M('waste_allot')->where(array('id' => $data['id']))->save(array('status' => 3)) === false) {
                $rst['success'] = false;
                $rst['errorMsg']= '更新废料调拨收货信息失败！';
            }
        }

        if ($rst['success']) {
            M()->commit();
        } else {
            M()->rollback();

        }
        return $rst;
    }

    /**
     * 获取废件名称
     * @author liyang
     * @parameter id   废件ID
     * @return void
     */
    public function getName($id)
    {
        return M('waste')->where(['id'=>['eq'=>$id]])->getField('title');
    }

    /**
     * 获取申请，审核，调拨，收货模型
     * @author liyang
     * @parameter id   $where
     * @return void
     */
    public function getList($where, $page)
    {
        $join = 'wa left join organization o on wa.proposer_org=o.id
            left join organization o2 on wa.auditor_org=o2.id
            left join user u on wa.proposer=u.id
            left join user u2 on wa.auditor=u2.id';
        
        $rst['total'] = M('waste_allot')->join($join)->where($where)->count();
        $rst['rows'] = M('waste_allot')->join($join)
                        ->where($where)->order('id DESC')
                        ->field('wa.*, u.realname as proposer, u2.realname as auditor, o.name as proposer_org, o2.name as auditor_org')
                        ->limit($page)
                        ->select();

        return $rst;
    }

    /**
     * 退还
     *
     * @return void
     */
    public function refund($data)
    {
        $json = array();

        $switch = true;
        $log = array();
        $stock = array();
        $wastes = json_decode($data['wastes'], true);
        $wastesModel = M('waste_warehouse');

        if (!empty($wastes)) {
            $price = 0;

            foreach ($wastes as $key => &$value) {
                $arr = array();
                
                if (!isset($value['waste_id'])) {
                    $value['waste_id'] = $value['id'];
                }

                $value['organization_id'] = $data['organization_id'];
                unset($value['name']);

                $price = M('waste')->where(array('id'=> $value['waste_id']))->getField('price');

                if (empty($price)) {
                    return false;
                }

                $map = array();
                $map['waste_id'] = $value['waste_id'];
                $map['organization_id'] = $value['organization_id'];

                $result = $wastesModel->where($map)->find();

                if ($result) {
                    /*数量相加后写入*/
                    $result['amount'] = $result['amount'] + $value['amount'];
                    $wastesModel->where($map)->save($result);
                } else {
                    $wastesModel->add($value);
                }

                /*按废料数量写入实际库存表*/
                for ($i = 1;$i <= $value['amount'];$i++ ) {

                    $stock['number'] = D('warehouse')->createNumber();
                    $stock['status'] = 1;
                    $stock['organization_id'] = $value['organization_id'];
                    $stock['engineer_id'] = $data['engineer_id'];
                    $stock['order_id'] = $data['order_id'];
                    $stock['waste_id'] = $value['waste_id'];
                    $stock['price'] = $price;
                    $stock['create_time'] = time();
                    $stock['recycle_time'] = time();
                    $result = M('waste_stock')->add($stock);

                    if ($result == false) {
                        $switch == false;
                    } else {
                        array_push($arr,array('id'=>$result,'number'=>$stock['number']));
                    }
                }

                $log['type'] = 3;
                $log['organization_id'] = $data['organization_id'];
                $log['waste_id'] = $value['waste_id'];
                $log['user_id'] = $_SESSION['userId'];
                $log['amount'] = $value['amount'];
                $log['inout'] = 1;
                $log['residue'] = '';
                $log['price'] = $price;
                $log['wastes']  = json_encode($arr);
                $log['order_id'] = $data['order_id'];
                $log['engineer_id'] = $data['engineer_id'];
                $log['time'] = time();

                if (M('waste_inout')->add($log) == false) {
                    $switch == false;
                }
            }
        }

        if ($switch){
            return true;
        } else {
            return false;
        }
    }

    public function findHouse($waste_id,$amount,$num)
    {
        $rst = M('waste_warehouse')->where(array('waste_id' => $waste_id))->order('id desc')->find();
        $rst['amount'] = $rst['amount'] - $amount;

        return M('waste_warehouse')->where(array('waste_id' => $waste_id))->save($rst);
    }
}