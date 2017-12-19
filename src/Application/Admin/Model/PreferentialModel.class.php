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

class PreferentialModel extends RelationModel
{
    /**
     * 检查数据
     */
    private function checkParams(&$param)
    {
        if (empty($param['title'])) {
            throw new \Exception('优惠标题不能为空！');
        }
        
        if (!in_array($param['type'], array(1, 2))) {
            throw new \Exception('优惠类型不存在！');
        }
        
        $param['start_time'] = strtotime($param['start_time']);
        $param['end_time'] = strtotime($param['end_time']);
        
        if (empty($param['start_time'])) {
            throw new \Exception('请设置优惠开始时间！');
        }
        
        if (empty($param['end_time'])) {
            throw new \Exception('请设置优惠结束时间！');
        }
        
        if ($param['end_time'] <= $param['start_time']) {
            throw new \Exception('请设置合理的优惠时间！');
        }
        
        if (!in_array($param['category'], array(1, 2, 3))) {
            throw new \Exception('优惠方式不存在！');
        }
        
        if ($param['category'] == 1) { //代金券
            $param['discount'] = 0;
        
            if ($param['threshold_price'] < 0) {
                throw new \Exception('请设置合理的满减价格！');
            }
        
            if ($param['price'] <= 0) {
                throw new \Exception('请设置合理的优惠券金额！');
            }
        
        } else if ($param['category'] == 2) {//折扣
            $param['threshold_price'] = 0;
            $param['price'] = 0;
        
            if ($param['discount'] > 100 || $param['discount'] < 1) {
                throw new \Exception('请设置合理的优惠折扣！');
            }
        
        } else if ($param['category'] == 3) {//特价
            $param['threshold_price'] = 0;
            $param['discount'] = 0;
        
            if ($param['price'] <= 0) {
                throw new \Exception('请设置合理的优惠券金额！');
            }
        }
        
        if (isset($param['amount']) && $param['amount'] <= 0) {
            throw new \Exception('请设置合理的优惠数量！');
        }
        
        $param['use_times'] = 1; //使用次数
    }
    
    /**
     * 新增优惠信息
     */
    public function addPreferential($param)
    {
        $this->checkParams($param);
        
        $param['create_time'] = time();
        $param['update_time'] = time();
        
        return $this->table('preferential')->add($param);
    }
    
    /**
     * 编辑优惠信息
     */
    public function editPreferential($id, $param)
    {
        $this->checkParams($param);
        
        $map = array('id' => $id);
        
        $item = $this->table('preferential')->where($map)->find();
        
        if (!$item) {
            throw new \Exception('优惠记录不存在！');
        }
        
        if (isset($param['amount']) && $item['amount'] != $param['amount']) {
            throw new \Exception('优惠数量不可更改！');
        }
    
        $param['update_time'] = time();
        
        $this->startTrans();
        
        $this->table('preferential_coupon')->where(array('preferential_id' => $id, 'coupon_status' => array('neq', 2)))->setField('coupon_status', $param['status']);
        $this->table('preferential')->where($map)->save($param);
        
        if ($this->commit()) {
            return true;
        } else {
            $this->rollback();
            return false;
        }
    }
    
    /**
     * 优惠券作废
     */
    public function delete($id)
    {
        if (!$id) {
            return false;
        }
        
        $this->startTrans();
        
        $this->table('preferential_coupon')->where(array('preferential_id' => $id, 'coupon_status' => array('lt', 2)))->setField('coupon_status', '-1');
        $this->table('preferential')->where(array('id' => $id))->setField('status', '-1');
        
        if ($this->commit()) {
            return true;
        } else {
            $this->rollback();
            return false;
        }
    }
    
    /**
     * 生成优惠券码
     * @param   int    $preferential_id 优惠id
     * @param   array  $param           数据
     */
    public function genPreferentialCode()
    {
        $list = $this->table('preferential')->where(array('type' => 2, 'status' => 1, 'flag' => 0))
                ->getField('id, type, category, flag, status, amount');
        
        if (!$list) {
            return false;
        }
        
        //$this->startTrans();
        
        $this->table('preferential')->where(array('id' => array('in', array_keys($list))))->setField('flag', 1);
        
        foreach ($list as $item) {
            $this->addPreferentialCode($item);
        }
        
        $this->table('preferential')->where(array('id' => array('in', array_keys($list))))->setField('flag', 2);
        
        //$flag = $this->commit();
        
        //if (!$flag) {
        //    $this->rollback();
        //}
    }
    
    /**
     * 插入优惠券码
     * @param   array   $preferential   优惠信息
     * @param   int     $num            缺少数量
     */
    private function addPreferentialCode($preferential, $num = 0)
    {
        if ($preferential['type'] != 2 || $preferential['flag']) {
            return false;
        }
        
        $amount = $num ? $num : $preferential['amount'];
        
        $data = array();
        $this->genCode($amount, $data);
        
        $params = array();
        $time = time();
        
        foreach ($data as $k => $code) {
            
            $page = ceil(($k+1) / 10000);
            
            $params[$page][] = array(
                'preferential_id' => $preferential['id'],
                'coupon_number'   => $code,
                'coupon_status'   => $preferential['status'],
                'coupon_ctime'    => $time,
            );
        }
        
        foreach ($params as $item) {
            $sql = $this->table('preferential_coupon')->addAll($item, array('fetch_sql' => true));
            $sql = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $sql);
            $this->execute($sql);
        }
        
        $count = $this->table('preferential_coupon')->where(array('preferential_id' => $preferential['id']))->count();
        
        if ($count < $preferential['amount']) {
            $this->addPreferentialCode($preferential['id'], $preferential, $preferential['amount'] - $count);
        }
        
        return true;
    }
    
    /**
     * 随机产生优惠码
     * 
     * @param   int     $num    生成数量
     * @param   array   $data   生成数据
     * @param   int     $count  总数量
     */ 
    private function genCode($num, &$data, $count = 0)
    {
        if ($count == 0) {
            $count = $num;
        }
        
        $str = array_merge(range(1,9), range('a','n'), range('p','z'), range('A','N'), range('P','Z'));
        $arr = array();
        
        for ($i = 1; $i <= $num; $i++) {
            shuffle($str);
            $arr[] = implode('', array_slice($str, 0, 12));
        }
        
        $data = array_unique(array_merge($data, $arr));
        $num = count($data);
        
        if ($count > $num) {
            $this->genCode($count - $num, $data, $count);
        }
    }
}