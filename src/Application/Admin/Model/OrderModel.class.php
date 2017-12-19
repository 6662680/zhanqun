<?php

// +------------------------------------------------------------------------------------------ 
// | Author: TCG <tianchunguang@weadoc.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 订单模型 Dates: 2016-09-25
// +------------------------------------------------------------------------------------------

namespace Admin\Model;

use Think\Model\RelationModel;

class OrderModel extends RelationModel
{
    /**
     * 生成订单编号
     *
     * @return void
     */ 
    static public function createNumber()
    {
        // W 日期（年月日时分秒)+ 随机字母（位）
        // W 14 07 17 11 22 56 xxx
        return 'W' . date('ymdHis', time()) . chr(mt_rand(ord('a'), ord('z'))) . chr(mt_rand(ord('a'), ord('z'))) . chr(mt_rand(ord('a'), ord('z')));
    }

    /**
     * 写入订单日志
     *
     * @param int $orderId 订单ID
     * @param string $action 操作
     * @return boolean 成功返回true, 失败返回false
     */
    public function writeLog($orderId, $action)
    {
        $data = array();
        $data['order_id'] = $orderId;
        $data['time'] = time();
        $data['action'] = $action;

        if (M('order_log')->add($data) === false) {
            return false;
        } else {
            return true;
        }
    }

    /** *************************************************************************************************** */
    
    /**
     * 取消订单 状态:-1
     */
    public function cancelOrder($orderId = null, $closeReason = null)
    {
        $rst = array();
        
        if (is_null($orderId)) {
            $rst['success'] = false;
            $rst['errorMsg'] = "传入参数错误，订单ID为空！";
            return $rst;
        }
        
        if (is_null($closeReason)) {
            $rst['success'] = false;
            $rst['errorMsg'] = "传入参数错误，订单取消原因为空！";
            return $rst;
        }
        
        $map = array();
        $map['id'] = $orderId;
        $order = M('order')->where($map)->find();
        
        if (!$order) {
            $rst['success'] = false;
            $rst['errorMsg'] = "订单不存在！";
            return $rst;
        }
        
        if ($order['status'] == -1) {
            $rst['success'] = true;
            return $rst;
        }
        
        $partner = M('order_partner')->where(array('order_id' => $orderId))->getField('partner');
        
        if ($order['status'] >= 5 && $partner == '葡萄生活') {
            $rst['success'] = false;
            $rst['errorMsg'] = "已完成的葡萄订单不能取消！";
            return $rst;
        }
        
        if (M('phomal_insurance_order')->where(array('order_id' => $orderId))->count()) {
            $rst['success'] = false;
            $rst['errorMsg'] = "保险维修订单不能取消！";
            return $rst;
        }
        
        $data = array();
        $data['status'] = -1;
        $data['close_reason'] = trim($closeReason);
        $data['close_time'] = time();
        
        if (in_array($order['status'], array(5, 6))) {
        
            try {
                M()->startTrans();
                $flag = true;
                
                //订单取消
                if (M('order')->where($map)->save($data) === false) {
                    $flag = false;
                }
                
                //退还物料
                if (D('engineer')->refundFitting($order['id'], $order['engineer_id']) === false) {
                    $flag = false;
                }
                
                //退还废料
                if (D('engineer')->refundWaste($order['id'], $order['engineer_id']) === false) {
                    $flag = false;
                }
                
                if ($order['status'] == 6) {
                    //取消工程师收益
                    if (M('engineer_divide')->where(array('engineer_id' => $order['engineer_id'], 'order_id' =>  $order['order_id']))->delete() === false) {
                        $flag = false;
                    }
                    
                    //取消订单购买的保险
                    if (D('phomalInsurance')->cancelPhomalInsuranceOrder($order['id']) === false) {
                        $flag = false;
                    }                    
                }
                
                $action = '操作人：' . session('userInfo.username') . '--手动--取消订单--原因:[' . $closeReason . ']';
                
                if (D('order')->writeLog($order['id'], $action) === false) {
                    $flag = false;
                }
                
                if ($flag) {
                    M()->commit();
                    $rst['success'] = true;
                } else {
                    M()->rollback();
                    $rst['success'] = false;
                    $rst['errorMsg'] = '订单取消失败！';
                }
            } catch (\Exception $e) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = $e->getMessage();
            }
        } else {
        
            M()->startTrans();
            $flag = true;
            
            /** 判断是不是葡萄生活的订单，如果是 */
            
            if ($partner == '葡萄生活') {
                $param = array('orderId' => $orderId, 'closeReason' => $closeReason);
                $output = D('Api/ThirdParty')->factory($partner, 'notifyCloseOrder', $param);
            
                if ($output && $output['status'] == 0) {
                    $flag = false;
                    \Think\Log::record('通知葡萄接口失败!', 'ERR');
                }
            } else {
                D('Api/ThirdParty')->factory($partner, 'deal', array('orderId' => $orderId));
            }
            
            if (M('order')->where($map)->save($data) === false) {
                $flag = false;
            }
            
            $action = '操作人：' . session('userInfo.username') . '--手动--取消订单--原因:[' . $closeReason . ']';
            
            if (D('order')->writeLog($order['id'], $action) === false) {
                $flag = false;
            }
            
            if ($flag) {
                M()->commit();
                $rst['success'] = true;
            } else {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '订单取消失败！';
            }
        }
        
        return $rst;
    }

    /**
     * 解除订单
     *
     * @return void
     */
    public function freeOrder($orderId = null)
    {
        $rst = array();

        if (is_null($orderId)) {
            $rst['success'] = false;
            $rst['errorMsg'] = "传入参数错误，订单ID为空！";
            return $rst;
        }

        $map = array();
        $map['id'] = $orderId;
        $order = M('order')->where($map)->find();

        if (!$order) {
            $rst['success'] = false;
            $rst['errorMsg'] = "订单不存在！";
            return $rst;
        }

        if (!in_array($order['status'], array(3, 4))) {
            $rst['success'] = false;
            $rst['errorMsg'] = "订单状态不在接单或处理中，不能进行解除操作！";
            return $rst;
        }

        $data = array();
        $data['status'] = 1;
        $data['engineer_id'] = 0;

        if (M('order')->where($map)->save($data) !== false) {
            $rst['success'] = true;
            $action = '操作人：' . session('userInfo.username') . '--手动--解除订单--状态：' . C('ORDER_STATUS')[1];
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '订单解除失败！';
            $action = '操作人：' . session('userInfo.username') . '--手动--解除订单失败';
        }
        
        $this->writeLog($orderId, $action);
        
        return $rst;
    }

    /**
     * 取回订单
     *
     * @param int $orderId 订单ID
     * @return array
     */
    public function retrieveOrder($orderId = null)
    {
        $rst = array();

        if (is_null($orderId)) {
            $rst['success'] = false;
            $rst['errorMsg'] = "传入参数错误，订单ID为空！";
            return $rst;
        }

        $map = array();
        $map['id'] = $orderId;
        $order = M('order')->where($map)->find();

        if (!$order) {
            $rst['success'] = false;
            $rst['errorMsg'] = "订单不存在！";
            return $rst;
        }

        if (!in_array($order['status'], array(3, 4))) {
            $rst['success'] = false;
            $rst['errorMsg'] = "订单状态不在接单或处理中，不能进行取回操作！";
            return $rst;
        }

        $data = array();
        $data['status'] = 11;
        $data['engineer_id'] = 0;

        if (M('order')->where($map)->save($data) !== false) {
            $rst['success'] = true;
            $action = '操作人：' . session('userInfo.username') . '--手动--取回订单--状态：' . C('ORDER_STATUS')[11];
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '订单取回失败！';
            $action = '操作人：' . session('userInfo.username') . '--手动--取回订单失败';
        }
        
        $this->writeLog($orderId, $action);
        
        return $rst;
    }

    /**
     * 订单改约
     *
     * @param int $orderId 订单ID
     * @return array
     */
    public function hangupOrder($orderId = null)
    {
        $rst = array();

        if (is_null($orderId)) {
            $rst['success'] = false;
            $rst['errorMsg'] = "传入参数错误，订单ID为空！";
            return $rst;
        }

        $map = array();
        $map['id'] = $orderId;
        $order = M('order')->where($map)->find();

        if (!$order) {
            $rst['success'] = false;
            $rst['errorMsg'] = "订单不存在！";
            return $rst;
        }

        if (!in_array($order['status'], array(3, 4))) {
            $rst['success'] = false;
            $rst['errorMsg'] = "订单状态不在接单或处理中，不能进行改约操作！";
            return $rst;
        }

        $data = array();
        $data['status'] = 12;
        $data['engineer_id'] = 0;
        
        if (M('order')->where($map)->save($data) !== false) {
            $rst['success'] = true;
            $action = '操作人：' . session('userInfo.username') . '--手动--改约订单--状态：' . C('ORDER_STATUS')[12];
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '订单改约失败！';
            $action = '操作人：' . session('userInfo.username') . '--手动--改约订单失败';
        }
        
        $this->writeLog($orderId, $action);
        
        return $rst;
    }
    
    /**
     * 入库
     */
    public function stock($param, $trans = true)
    {
        $map = array('id' => intval($param['id']));
        
        if (isset($param['number']) && $param['number']) {
            $map['number'] = trim($param['number']);
        }
        
        $item = M('order')->where($map)->find();
        
        if ($item) {

            if ($item['status'] != 5 && $item['pay_type'] != 2) {
                $rst['success'] = false;
                $rst['errorMsg'] = '当前订单未结单，入库失败！';
                return $rst;
            }

            $flag = true;
            $trans && M()->startTrans();

            /** 预付 */
            if ($item['pay_type'] == 2) {
                /** 已付款项 */
                if (!empty($item['paid_amount'])) {
                    $param['paid_amount'] += $item['paid_amount'];
                }
                
                /** 第三放交易号 */
                if (!empty($item['third_party_number'])) {
                    $param['third_party_number'] .= ',' . $item['third_party_number'];
                }

                /** 交易账号 */
                if (!empty($item['buyer_email'])) {
                    $param['buyer_email'] .= ',' . $item['buyer_email'];
                }

                /** 结单 / */
                if ($item['status'] == 5) {

                    /** 自动入库 */
                    if ($item['is_clearing']) {
                        $param['status'] = 6;
                        $param['clearing_time'] = time();
                    } else {  /** 预付类型，但是未预付，手动入库 */
                        $param['paid_time'] = time();
                        $param['clearing_time'] = time();
                        $param['is_clearing'] = 1;
                        $param['status'] = 6;
                    }
                } else { /** 第一次付款 */
                    $param['paid_time'] = time();
                    $param['is_clearing'] = 1;
                }
            } else {
                $param['paid_time'] = time();
                $param['clearing_time'] = time();
                $param['is_clearing'] = 1;
                $param['status'] = 6;
            }

            unset($param['id']);

            //填写付款账号数据
            if (M('order')->where($map)->save($param) === false) {
                $flag = false;
            }
            
            //计算工程师收益
            if ($item['pay_type'] == 2) {
                /** 预付款必须付完全款才结算工程师收益 */
                if ($item['actual_price'] == $param['paid_amount'] && $item['status'] == 5) {
                    if (D('Admin/engineer')->generateEngineerDivide($item['id']) === false) {
                        $flag = false;
                    }
                }
            } else {
                if (D('Admin/engineer')->generateEngineerDivide($item['id']) === false) {
                    $flag = false;
                }
            }

            //写入订单日志
            if (isset($param['third_party_number']) && $param['third_party_number']) {
                /** 预付 */
                if ($item['pay_type'] == 2 && $item['actual_price'] == $param['paid_amount']) {
                    $action = '客户付款订单已付清--金额[' . $param['paid_amount'] . ']';
                } else {
                    $action = '客户付款订单自动入库--状态：入库';
                }
                
                if ($this->writeLog($item['id'], $action) === false) {
                    $flag = false;
                }
            } else {
                
                if (session('userId')) {
                    $action = '操作人：' . session('userInfo.username') . '--手动入库--状态：入库';
                    
                    if ($this->writeLog($item['id'], $action) === false) {
                        $flag = false;
                    }
                }
            }

            
            //保险单设置服务完成
            $insurance_order_id = (int) M('phomal_insurance_order')->where(array('order_id' => $item['id']))->getField('id');
            
            if ($insurance_order_id > 0) {
                
                $bind = array(
                    'phomal_insurance_order_id' => $insurance_order_id, 
                    'time' => time(), 
                    'action' => '保险维修订单结单入库，保险自动设置服务完成'
                );
                
                if (M('phomal_insurance_order_log')->add($bind) == false) {
                    $flag = false;
                }
                
                if (M('phomal_insurance_order')->where(array('order_id' => $item['id']))->setField('status', 5) == false) {
                    $flag = false;
                }
            }
            
            // TODO 入库后通知是否可以买保险
            if (in_array($item['type'], array(1, 2, 5)) && $item['cellphone']) {
            
                if (!(M('order_partner')->where(array('order_id' => $item['id']))->count())) {
                
                    $insurance = M('order_phomal')->join('op left join phomal_insurance_phomals pip on op.phomal_id = pip.phomal_id')
                                 ->join('left join phomal_insurance pi on pi.id = pip.phomal_insurance_id')
                                 ->where(array('op.order_id' => $item['id'], 'pi.id' => array('gt', 0), 'pi.status' => 1))->count();
                
                    if ($insurance) {
                        $sms = new \Vendor\aliNote\aliNote();
                        $sms->send($item['cellphone'], array('orderNumber' => '='.$item['number']), 'SMS_34565382');
                    }
                }
            }
            
            if ($flag) {
                $trans && M()->commit();
                $rst['success'] = true;
                
                /** 第三方合作伙伴 */
                $partner = M('order_partner')->where(array('order_id' => $item['id']))->getField('partner');
                D('Api/ThirdParty')->factory($partner, 'deal', array('orderId' => $item['id']));
            } else {
                $trans && M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '入库失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }
        
        return $rst;
    }
}
