<?php

// +------------------------------------------------------------------------------------------ 
// | Author: zhujianping <zhujianping@weadoc.com>
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 第三方保险订单  Dates: 2017-06-06
// +------------------------------------------------------------------------------------------

namespace Admin\Model;

use Think\Exception;
use Think\Model;

class ThirdInsuranceModel extends Model
{
    protected $tableName='third_insurance_order';
    /**
     * 生成保险单编号
     *
     * @return void
     */
    static public function createNumber()
    {
        // W 日期（年月日时分秒)+ 随机字母（位）
        // W 14 07 17 11 22 56 xxx
        return 'T' . date('ymdHis', time()) . chr(mt_rand(ord('a'), ord('z'))) . chr(mt_rand(ord('a'), ord('z'))) . chr(mt_rand(ord('a'), ord('z')));
    }
    
    /**
     * 添加保险单日志
     * @param   int $order_id
     * @param   string $action
     * @return  boolean
     */
    public function writeLog($order_id, $action)
    {
        $data['third_insurance_order_id'] = $order_id;
        $data['time'] = time();
        $data['action'] = $action;
        
        return M('third_insurance_order_log')->add($data);
    }

    public function addThirdOrder($data)
    {

    }

    /**
     * 创建第三方保险单
     */
    public function addInsuranceOrder($data)
    {

        $orderNumber=!isset($data['order_number'])?D('Order')->createOrderSn():$data['order_number'];
        //生成第三方订单
        if(!M('third_party_order')->add(array('order_number' => $orderNumber,'create_time'=>time(),
            'color_name' => $data['color_name']
        , 'order_price'=>I('param.order_price'),'comment'=>I('param.comment'),'source' => I('param.source'), 'phone_name' => I('post.phone_name'),'phone_imei'=>I('post.phone_imei')))){
            throw new \Exception('创建第三方订单失败');
        }

        $piId = (int)$data['piId'];

        //$insurance = M('phomal_insurance')->where(array('id' => $piId))->field('id, price, duration')->find();

        $param = array(
            'number' => $this->createNumber(),
            'create_time' => time(),
            'customer' => $data['customer'] ,
            'cellphone' => $data['cellphone'] ,
            'phone_id' => $data['phone_id'],
            'brand_id' => $data['brand_id'],
            'old_order_number' => $orderNumber,
            'status' => 1,
            'invoice_img'=>$data['invoice_img'],
            'imei_img'=>$data['imei_img'],
            'effect_time'  => strtotime('tomorrow'),
            'failure_time' => strtotime(date('Y-m-d 23:59:59.999', strtotime(1 . " year"))),
        );
        $param['id'] = $this->add($param);
        if ($param['id'] === false) {
            throw_exception('无法购买保险，请联系客服人员！');
        }

        return $param;

    }

    public function editInsuranceOrder($data)
    {

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
        
        $insurance = M('third_insurance_order')->where($map)->find();
        
        if (!$insurance) {
            \Think\Log::record('查询到保险单--保险单ID:' . $id, 'ERR');
            return false;
        }
        
        //已付款
        if ($insurance['status'] >= 1) {
            return true;
        }
        
        //已取消付款
        if ($insurance['status'] < 0) {
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
        if (M('third_insurance_order')->where($map)->save($data) === false) {
            \Think\Log::record('修改第三方保险单付款信息失败--保险单ID:' . $id, 'ERR');
            $flag = false;
        }
        
        //日志
        if ($this->writeLog($id, '客户付款--付款账号：'. $param['pay_account'].'--付款金额:' . $param['pay_price'] . '--交易号:'.$param['pay_number']) === false) {
            $flag = false;
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
        
        $item = M('third_insurance_order')->where($map)->field('source,customer,cellphone,old_order_number, order_number, phomal_insurance_id')->find();
        
        if (!$item || !$item['old_order_number']) {
            return false;
        }

        $where = array('order_number' => $item['old_order_number']);
        
        $order = M('third_party_order')->where($where)
                ->field(' phone_name, phone_imei, create_time')
                ->find();
            
        if (!$order) {
            return false;
        }
        
        $order['order_number'] = D('Admin/order')->createNumber();
        $order['source'] = $item['source'];
        $order['status'] = 1;
        $order['type'] = 2;//保险
        $order['create_time'] = time();
        
        $where = array('phomal_insurance_id' => $item['phomal_insurance_id']);
        $phomals = M('phomal_insurance_phomals')->where($where)->select();
        
        if (!$phomals) {
            return false;
        }
        
        //产生维修单
        $order_id = M('third_party_order')->add($order);
        
        if ($order_id === false) {
            return false;
        }

        /*
        //维修单日志
        $action = '操作人：' . session('userInfo.username') . '--保险单出险审核通过产生保险维修单--状态：' . C('ORDER_STATUS')[$order['status']];
        
        if (D('Admin/order')->writeLog($order_id, $action) === false) {
            return false;
        }*/
        
        return $order_id;
    }
}
