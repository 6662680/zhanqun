<?php

// +------------------------------------------------------------------------------------------ 
// | Author: tcg 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 采购入库单 Dates: 2017-03-09
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;
use Think\Exception;

class PurchasereceiptController extends BaseController
{
	/**
	 * 首页
	 *
	 * @return void
	 */
    public function index()
    {
        $this->display();
    }

    /**
     * 列表
     *
     * @return void
     */
    public function rows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        $orgs = session('organizations');

        if (!empty($orgs)) {
            $map['prf.organization_id'] = array('in', array_keys($orgs));
        }
        
        if (!empty($post['starttime']) && empty($post['endtime'])) {
            $map['pr.create_time'] = array('EGT', strtotime($post['starttime']));
        } else if (!empty($post['endtime'] && empty($post['starttime']))) {
            $map['pr.create_time'] = array('ELT', strtotime($post['endtime'] . '23:59:59'));
        } else if ($post['starttime'] && $post['endtime']) {
            $map['pr.create_time'] = array(array('egt', strtotime($post['starttime'])), array('elt', strtotime($post['endtime'] . '23:59:59')), 'and');
        }
        
        if (!empty($post['proposer_org']) && $post['proposer_org'] != 'all') {
            $map['prf.organization_id '] = $post['proposer_org'];
        }
        
        if (is_numeric($post['status']) && $post['status'] != 'all') {
            $map['pr.status'] = $post['status'];
        }
        
        if (!empty($post['keyword'])) {
            $where = array();
            $where['f.number'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['f.title'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['pr.remark'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['pr.batch'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $count = M('purchase_receipt')->join('pr left join purchase_receipt_fitting prf on prf.purchase_receipt_id = pr.id')
                ->join('left join fitting f on f.id = prf.fitting_id')->where($map)->count('distinct(pr.id)');
        $rst['total'] = $count;

        $join = 'pr left join purchase_receipt_fitting prf on prf.purchase_receipt_id = pr.id
                left join organization o on prf.organization_id=o.id 
                left join fitting f on f.id = prf.fitting_id
                left join user u on pr.proposer=u.id
                left join user u2 on pr.payer=u2.id';

        $list = M('purchase_receipt')->join($join)->where($map)->limit($this->page())
            ->field('pr.*, u.username as proposer_name, u2.username as payer')
            ->group('pr.id')
            ->order('id desc')->select();
        $rst['rows'] = $list;
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 收货管理
     *
     * @return void
     */
    public function receiptList()
    {
        $this->display();
    }
    
    /**
     * 机型
     *
     * @return void
     */
    public function phones()
    {
        $map = array();
        $list = M('phone')->where($map)->field('id, alias')->order('alias asc')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 配件
     *
     * @return void
     */
    public function fittings()
    {
        $phoneId = I('get.id/d', 0);
        $sql = "select f.id, concat(f.title, '(', f.number, ')') as name from phone_fitting pf 
                left join fitting f on pf.fitting_id=f.id 
                where pf.phone_id={$phoneId} and f.id > 0";
        $fittings = M()->query($sql);
        $this->ajaxReturn($fittings);
    }

    /**
     * 仓库（组织）
     *
     * @return void
     */
    public function organization()
    {
        $list = M('organization')->select();
        //array_unshift($list,array('alias'=>'全部','id'=>false));
        $this->ajaxReturn($list);
    }
    
    /**
     * 供应商
     *
     * @return void
     */
    public function provider()
    {
        $map = array();
        $list = M('provider')->where($map)->field('id, title')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 增加
     *
     * @return void
     */
    public function add()
    {
        $rst = array();
        $data = array();
        $post = I('post.');
        $data['batch'] = $post['batch'];
        $data['proposer'] = session('userId');
        $data['provider_id'] = $post['provider_id'];
        $data['create_time'] = time();
        $data['price'] = 0;
        $data['remark'] = $post['remark'];

        $fittings = array();
        $fittingIds = array();
        
        foreach ($post['fittings'] as $key => $value) {
            $info = explode('_', $value);
        
            if ($info[2] <= 0) {
                continue;
            }
            
            if (in_array($info[1], $fittingIds[$info[0]])) {
                $rst['success'] = false;
                $rst['errorMsg'] = '同一地区库配件存在重复，请合并后再提交！';
                $this->ajaxReturn($rst);
            }
            
            $fittingIds[$info[0]][] = $info[1];
        
            $item = array();
            $item['organization_id'] = $info[0];
            $item['fitting_id'] = $info[1];
            $item['purchase_amount'] = $info[2];
            $item['price'] = $info[3];
            $data['price'] += ($info[3] * $info[2]);
            
            $fittings[] = $item;
        }
        
        unset($fittingIds);

        /** 配件数据验证 */
        
        if (count($fittings) == 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '采购入库配件不能为空！';
            $this->ajaxReturn($rst);
        }
        
        M()->startTrans();
        
        $id = M('purchase_receipt')->add($data);
        
        if ($id === false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '提交采购入库单失败1！';
            $this->ajaxReturn($rst);
        }
        
        foreach ($fittings as &$item) {
            $item['purchase_receipt_id'] = $id;
        }
        
        if (M('purchase_receipt_fitting')->addAll($fittings) === false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '提交采购入库单失败2！';
            $this->ajaxReturn($rst);
        }
        
        M()->commit();
        
        $rst['success'] = true;
        $this->ajaxReturn($rst);
    }

    /**
     * 更新
     *
     * @return void
     */
    public function edit()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $item = M('purchase_receipt')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] >= 2 || $item['status'] <= -1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '当前记录状态无法进行编辑操作！';
            $this->ajaxReturn($rst);
        }

        $rst = array();
        $data = array();
        $post = I('post.');
        $data['provider_id'] = $post['provider_id'];
        $data['update_time'] = time();
        $data['price'] = 0;
        $data['remark'] = $post['remark'];

        $fittings = array();
        $fittingIds = array();
        $org_fittings = array();
        $is_receipt_all = true; //是否全部收货
        
        $fittinglists = M('purchase_receipt_fitting')->where(array('purchase_receipt_id' => $item['id']))
                    ->field('purchase_receipt_id, organization_id, fitting_id, purchase_amount, price, amount, receipt_time')->select();
        
        foreach ($fittinglists as $val) {
            $org_fittings[$val['organization_id'].'_'.$val['fitting_id']] = $val;
        }
        
        foreach ($post['fittings'] as $key => $value) {
            $info = explode('_', $value);
            $key++;
            
            if ($info[2] <= 0) {
                
                if (isset($org_fittings[$info[0].'_'.$info[1]]) && $org_fittings[$info[0].'_'.$info[1]]['amount'] > 0) {
                    $rst['success'] = false;
                    $rst['errorMsg'] = "第 {$key} 行仓库已经收货，修改数量不能小于收货数量！";
                    $this->ajaxReturn($rst);
                }
                
                continue;
            }
            
            if (isset($org_fittings[$info[0].'_'.$info[1]]) && $org_fittings[$info[0].'_'.$info[1]]['amount'] > $info[2]) {
                $rst['success'] = false;
                $rst['errorMsg'] = "第 {$key} 行仓库已经收货，修改数量不能小于收货数量！";
                $this->ajaxReturn($rst);
            }
            
            if (in_array($info[1], $fittingIds[$info[0]])) {
                $rst['success'] = false;
                $rst['errorMsg'] = '同一仓库配件存在重复，请合并后再提交！';
                $this->ajaxReturn($rst);
            }
            
            $fittingIds[$info[0]][] = $info[1];
            
            $param = array();
            $param['purchase_receipt_id'] = $item['id'];
            $param['organization_id'] = $info[0];
            $param['fitting_id'] = $info[1];
            $param['purchase_amount'] = $info[2];
            $param['price'] = $info[3];
            $param['amount'] = 0;
            $param['receipt_time'] = 0;
            
            $data['price'] += ($info[3] * $info[2]);
            
            if (isset($org_fittings[$info[0].'_'.$info[1]])) {
                $param['amount'] = $org_fittings[$info[0].'_'.$info[1]]['amount'];
                $param['receipt_time'] = $org_fittings[$info[0].'_'.$info[1]]['receipt_time'];
            }
            
            if ($param['purchase_amount'] != $param['amount']) { //采购数量与收货数量不等
                $is_receipt_all = false;
            }
            
            $fittings[] = $param;
        }
        
        unset($fittingIds);
        unset($org_fittings);

        /** 配件数据验证 */
        
        if (count($fittings) == 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '采购入库配件不能为空！';
            $this->ajaxReturn($rst);
        }
        
        $is_receipt_all && $data['status'] = 2; //全部收货
        
        M()->startTrans();
        
        if (M('purchase_receipt')->where($map)->save($data) === false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '编辑采购入库单失败！';
            $this->ajaxReturn($rst);
        }
            
        if ($fittings != $fittinglists) {
            
            if (M('purchase_receipt_fitting')->where(array('purchase_receipt_id' => $item['id']))->delete() === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '编辑采购入库单失败！';
                $this->ajaxReturn($rst);
            }
            
            if (M('purchase_receipt_fitting')->addAll($fittings) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '编辑采购入库单失败！';
                $this->ajaxReturn($rst);
            }
        }
        
        $data = array(
            'purchase_receipt_id' => $item['id'],
            'action' => '操作人：' . session('userInfo.username') . '--修改采购入库单',
            'create_time' => time(),
        );
        
        if (M('purchase_receipt_log')->add($data) === false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '编辑采购申请失败！';
            $this->ajaxReturn($rst);
        }
        
        M()->commit();
        
        $rst['success'] = true;
        $this->ajaxReturn($rst);
    }

   	/**
   	 * 删除
   	 *
   	 * @return void
   	 */
   	public function delete()
   	{
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        
        $item = M('purchase_receipt')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] > 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '当前记录状态无法进行取消操作！';
            $this->ajaxReturn($rst);
        }
        
        M()->startTrans();
        
        $data = array('status' => -1, 'update_time' => time());
        
        if (M('purchase_receipt')->where($map)->save($data) === false) {
            $rst['success'] = false;
            $rst['errorMsg'] = '取消失败！';
        }
        
        $data = array(
            'purchase_receipt_id' => $item['id'],
            'action' => '操作人：' . session('userInfo.username') . '--取消采购入库单',
            'create_time' => time(),
        );
        
        if (M('purchase_receipt_log')->add($data) === false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '编辑采购申请失败！';
            $this->ajaxReturn($rst);
        }
        
        M()->commit();
        
        $rst['success'] = true;
        $this->ajaxReturn($rst);
    }
    
    /**
     * 仓库收货
     */
    public function receipt()
    {
        $redisLock=new \Org\Util\RedisLock();
        if(!$redisLock->acquireLock()){
            $rst['success'] = false;
            $rst['errorMsg'] = "已经有其他人在操作收货,请稍候再试";
            $this->ajaxReturn($rst);
        }
        $rst = array();
        $data = array();
        $post = I('post.');

        $fittings = array();
        $purchase_id = array();
        $ids = array();
        $time = time();
        $is_receipt = 0;
        
        foreach ($post['fittings'] as $key => $value) {
            $info = explode('_', $value);
            $key++;
            
            if ($info[3] < 0) {
                $rst['success'] = false;
                $rst['errorMsg'] = "第 {$key} 行收货数量不能小于0！";
                $this->ajaxReturn($rst);
            }
        
            if ($info[3] > $info[2]) {
                $rst['success'] = false;
                $rst['errorMsg'] = "第 {$key} 行收货数量不能大于采购数量！";
                $this->ajaxReturn($rst);
            }
        
            $param = array();
            $param['id'] = $info[0];
            $param['amount'] = $info[3];
            $param['receipt_time'] = $time;
        
            $fittings[] = $param;
            $purchase_id[$info[1]] = $info[1];
            $ids[] = $info[0];
        }
        
        if (!$purchase_id) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请填写仓库收到的各配件数量！';
            $this->ajaxReturn($rst);
        }
        
        if (count($purchase_id) > 1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '当前填写的配件收货数量不在同一采购单中，请刷新页面重试！';
            $this->ajaxReturn($rst);
        }
        
        $purchase_fittings = M('purchase_receipt_fitting')->where(array('id' => array('in', $ids)))->getField('id, organization_id, fitting_id, purchase_amount, price, amount');
        
        if (!$purchase_fittings || count($fittings) != count($purchase_fittings)) {
            $rst['success'] = false;
            $rst['errorMsg'] = '当前采购单中仓库分配的配件不存在！';
            $this->ajaxReturn($rst);
        }
        
        $item = M('purchase_receipt')->where(array('id'=>current($purchase_id)))->find();
        
        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] >= 2 || $item['status'] <= -1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '当前记录状态无法进行收货操作！';
            $this->ajaxReturn($rst);
        }

        $flag = true;
        M()->startTrans();
        
        foreach ($fittings as $v) {
            
            //如果收货数量和已收货数量相等
            if ($v['amount'] == $purchase_fittings[$v['id']]['amount']) {
                continue;
            }
            
            //填写收货数量
            if (M('purchase_receipt_fitting')->where(array('id'=>$v['id']))->save($v) === false) {
                $flag = false;
                $rst['errorMsg'] = '收货失败(更新仓库收货数量失败)！';
                break;
            }
            
            $organization_id = $purchase_fittings[$v['id']]['organization_id'];
            $fitting_id = $purchase_fittings[$v['id']]['fitting_id'];
            $price = $purchase_fittings[$v['id']]['price'];
            
            //增减库存实体
            /** $where = array(
                'organization_id' => $organization_id,
                'batch' => $item['batch'],
                'fitting_id' => $fitting_id,
            );
            $amount = M('stock')->where($where)->count();
            $amount = $v['amount'] - $amount; */

            $amount = $v['amount'] - $purchase_fittings[$v['id']]['amount'];
            
            $where = array(
                'organization_id' => $organization_id,
                'fitting_id' => $fitting_id,
            );
            
            if ($amount > 0) { //增加
                
                //增加仓库库存
                if (M('warehouse')->where($where)->count()) {
                    
                    if (M('warehouse')->where($where)->setInc('amount', $amount) === false) {
                        $flag = false;
                        $rst['errorMsg'] = '收货失败(更新仓库配件库存数量失败)！';
                        break;
                    }
                } else {
                    
                    $add_param = array(
                        'organization_id' => $organization_id,
                        'fitting_id' => $fitting_id,
                        'amount' => $amount,
                    );
                    
                    if (M('warehouse')->where($where)->add($add_param) === false) {
                        $flag = false;
                        $rst['errorMsg'] = '收货失败(更新仓库配件库存数量失败)！';
                        break;
                    }
                }
                
                $stock_param = array();
                $numbers = array();
                
                for ($i = 1; $i <= $amount; $i++) {
                    $number = D('warehouse')->createNumber();
                    
                    $stock_param[] = array(
                        'number' => $number,
                        'status' => 1,
                        'organization_id' => $organization_id,
                        'fitting_id' => $fitting_id,
                        'price' => $price,
                        'batch' => $item['batch'],
                        'provider_id' => $item['provider_id'],
                        'create_time' => $time,
                    );
                    
                    $numbers[] = $number;
                }
                
                //更新实体
                if (M('stock')->addAll($stock_param) === false) {
                    $flag = false;
                    $rst['errorMsg'] = '收货失败(更新仓库配件实体失败)！';
                    break;
                }
                
                //仓库出入库记录
                $log = array();
                /** 类型 1 出入库 2 调拨 3 工程师申请 4 报损 */
                $log['type'] = 1;
                $log['batch'] = $item['batch'];
                $log['fitting_id'] = $fitting_id;
                $log['user_id'] = session('userId');
                $log['organization_id'] = $organization_id;
                $log['provider_id'] = $item['provider_id'];
                /** 操作类型 1 入库 2 出库 */
                $log['inout'] = 1;
                $log['amount'] = $amount;
                $log['fittings'] = json_encode($numbers);
                $log['price'] = $price;
                $log['time'] = $time;

                if (M('inout')->add($log) === false) {
                    $flag = false;
                    $rst['errorMsg'] = '收货失败(添加出入库信息失败)！';
                    break;
                }
            } else if ($amount < 0) { //减少
                
                $amount = abs($amount);
                
                //减少仓库库存
                if (M('warehouse')->where($where)->setDec('amount', $amount) === false) {
                    $flag = false;
                    $rst['errorMsg'] = '收货失败(更新仓库配件库存数量失败)！';
                    break;
                }
                
                //删除库存实体
                $where = array(
                    'organization_id' => $organization_id,
                    'batch' => $item['batch'],
                    'fitting_id' => $fitting_id,
                );
                $stocks = M('stock')->where($where)->limit($amount)->getField('id, number');

                if (count($stocks) < $amount) {
                    $flag = false;
                    $rst['errorMsg'] = '收货失败(更新仓库配件库存数量失败[仓库库存数量不足])！';
                    break;
                }
                
                if (M('stock')->where(array('id'=>array('in', array_keys($stocks))))->limit($amount)->delete() === false) {
                    $flag = false;
                    $rst['errorMsg'] = '收货失败(更新仓库配件实体失败)！';
                    break;
                }
                
                //仓库出入库记录
                $log = array();
                /** 类型 1 出入库 2 调拨 3 工程师申请 4 报损 */
                $log['type'] = 1;
                $log['batch'] = $item['batch'];
                $log['fitting_id'] = $fitting_id;
                $log['user_id'] = session('userId');
                $log['organization_id'] = $organization_id;
                $log['provider_id'] = $item['provider_id'];
                /** 操作类型 1 入库 2 出库 */
                $log['inout'] = 2;
                $log['amount'] = $amount;
                $log['fittings'] = json_encode($stocks);
                $log['price'] = $price;
                $log['time'] = $time;
                
                if (M('inout')->add($log) === false) {
                    $flag = false;
                    $rst['errorMsg'] = '收货失败(添加出入库信息失败)！';
                    break;
                }
            }
        }

        //更新采购单收货状态
        if ($flag) {
            
            $data = M('purchase_receipt_fitting')->where(array('purchase_receipt_id'=>$item['id']))
                    ->field('sum(purchase_amount) as purchase_amount, sum(amount) as amount')->find();
            $status = 0; //待收货
            
            if ($data['purchase_amount'] == $data['amount']) {
                $status = 2; //全部收货
            } else if ($data['amount'] > 0 && $data['amount'] < $data['purchase_amount']) {
                $status = 1;//部分收货
            }
            
            if ($item['status'] != $status && M('purchase_receipt')->where(array('id' => $item['id']))->setField('status', $status) === false) {
                $flag = false;
                $rst['errorMsg'] = '收货失败(更新采购入库单状态失败)！';
            }
        }
        
        $data = array(
            'purchase_receipt_id' => $item['id'],
            'action' => '操作人：' . session('userInfo.username') . '--填写仓库收货信息--备注：'. trim($post['receipt_remark']),
            'create_time' => time(),
        );
        
        if ($flag && M('purchase_receipt_log')->add($data) === false) {
            $flag = false;
            $rst['errorMsg'] = '收货失败(添加仓库收货信息失败)！';
        }
        
        if ($flag) {
            M()->commit();
        } else {
            M()->rollback();
        }
        $redisLock->releaseLock();
        $rst['success'] = $flag;
        $this->ajaxReturn($rst);
    }

    /**
     * 财务核对
     *
     * @return void
     */
    public function check()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $item = M('purchase_receipt')->where($map)->find();
        
        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }
        
        if ($item['status'] != 2) {
            $rst['success'] = false;
            $rst['errorMsg'] = '当前记录状态无法进行核对操作！';
            $this->ajaxReturn($rst);
        }
        
        $rst = array();
        $fittings = array();
        $post = I('post.');
        $data = array('price' => 0, 'status' => 3);
        
        foreach ($post['fittings'] as $key => $value) {
            $info = explode('_', $value);
            $key++;
            
            if ($info[1] <= 0) {
                $rst['success'] = false;
                $rst['errorMsg'] = "第 {$key} 行配件价格不符合实际！";
                $this->ajaxReturn($rst);
            }
            
            $param = array();
            $param['fitting_id'] = $info[0];
            $param['price'] = $info[2];
            
            $data['price'] += ($info[1] * $info[2]);
        
            $fittings[] = $param;
        }
        
        $flag = true;
        M()->startTrans();
        
        if (M('purchase_receipt')->where($map)->save($data) === false) {
            $flag = false;
            $rst['errorMsg'] = '核对失败（更新采购入库单状态失败）！';
        } else {
            
            foreach ($fittings as $v) {
                $where = array(
                    'purchase_receipt_id' => $item['id'],
                    'fitting_id' => $v['fitting_id'],
                );
                
                //更新采购入库单配件单价
                if (M('purchase_receipt_fitting')->where($where)->setField('price', $v['price']) === false) {
                    $flag = false;
                    $rst['errorMsg'] = '核对失败（更新采购入库单配件单价失败）！';
                    break;
                }
                
                $where = array(
                    'batch' => $item['batch'],
                    'fitting_id' => $v['fitting_id'],
                );
                
                //更新此批次库存实体单价
                if (M('stock')->where($where)->setField('price', $v['price']) === false) {
                    $flag = false;
                    $rst['errorMsg'] = '核对失败（更新库存实体单价失败）！';
                    break;
                }
            
                //更新此批次出入库配件单价
                if (M('inout')->where($where)->setField('price', $v['price']) === false) {
                    $flag = false;
                    $rst['errorMsg'] = '核对失败（更新库存实体单价失败）！';
                    break;
                }
            }
        }
        
        $data = array(
            'purchase_receipt_id' => $item['id'],
            'action' => '操作人：' . session('userInfo.username') . '--财务核对(修改)配件单价信息--备注：'. trim($post['check_remark']),
            'create_time' => time(),
        );
        
        if ($flag && M('purchase_receipt_log')->add($data) === false) {
            $flag = false;
            $rst['errorMsg'] = '核对失败(添加采购单核对日志信息失败)！';
        }
        
        if ($flag) {
            M()->commit();
        } else {
            M()->rollback();
        }
        
        $rst['success'] = $flag;
        $this->ajaxReturn($rst);
    }
    
    /**
     * 财务核对
     *
     * @return void
     */
    public function pay()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $item = M('purchase_receipt')->where($map)->find();
    
        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }
    
        if ($item['status'] != 3) {
            $rst['success'] = false;
            $rst['errorMsg'] = '当前记录状态无法进行付款操作！';
            $this->ajaxReturn($rst);
        }
    
        $rst = array();
        $post = I('post.');
        $data = array(
            'pay_time' => time(), 
            'payer' => session('userId'), 
            'status' => 4
        );
    
        $flag = true;
        M()->startTrans();
    
        if (M('purchase_receipt')->where($map)->save($data) === false) {
            $flag = false;
            $rst['errorMsg'] = '付款失败（更新采购入库单状态失败）！';
        }
    
        $data = array(
            'purchase_receipt_id' => $item['id'],
            'action' => '操作人：' . session('userInfo.username') . '--财务填写采购付款信息--备注：'. trim($post['pay_remark']),
            'create_time' => time(),
        );
    
        if ($flag && M('purchase_receipt_log')->add($data) === false) {
            $flag = false;
            $rst['errorMsg'] = '付款失败(记录采购单付款日志信息失败)！';
        }
    
        if ($flag) {
            M()->commit();
        } else {
            M()->rollback();
        }
    
        $rst['success'] = $flag;
        $this->ajaxReturn($rst);
    }

    /**
     * 导入
     *
     * @return void
     */
    public function import()
    {
        $rst = array();
    
        Vendor('PHPExcel.Classes.PHPExcel.IOFactory');
    
        $inputFileType = \PHPExcel_IOFactory::identify($_FILES['fitting_file']['tmp_name']);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($_FILES['fitting_file']['tmp_name']);
        $data = $objPHPExcel->getSheet(0)->toArray();
        
        $organization = M('organization')->getField('alias, id');
        
        $params = array();
        $numbers = array();
        $fittings = array();
        $orgs = array();
        
        foreach ($data as $k => $value) {
    
            if ($k < 1) {
                
                foreach ($value as $i => $v) {
                    
                    if ($i < 3 || empty($v)) {
                        continue;
                    }
                    
                    $v = trim($v);
                    
                    if (!isset($organization[$v])) {
                        $rst['success'] = false;
                        $rst['errorMsg'] = $v . '仓库不存在，请检查！';
                        $this->ajaxReturn($rst);
                    }
                    
                    $orgs[$i] = $v;
                }
                
                continue;
            }
            
            if (empty($value[0]) && empty($value[2]) && $value[2] <= 0) {
                continue;
            }
            
            if (empty($value[0]) || empty($value[2]) || $value[2] <= 0) {
                $rst['success'] = false;
                $rst['errorMsg'] = '请检查第 ' . ($k + 1) . ' 行地区配件信息！';
                $this->ajaxReturn($rst);
            }

            if (in_array($value[0], $numbers)) {
                $rst['success'] = false;
                $rst['errorMsg'] = '第 ' . ($k + 1) . ' 行配件编号（' . $value[0]  . '）存在重复导入，请检查！';
                $this->ajaxReturn($rst);
            }


            $value[0] = trim($value[0]);

            $map = array(
                'number' => $value[0],
            );
            
            $fitting = M('fitting')->join('f left join phone_fitting pf on pf.fitting_id = f.id')
                        ->join('left join phone p on p.id = pf.phone_id')
                        ->field('f.id, group_concat(pf.phone_id) as phone_id, group_concat(p.alias) as phone, f.title as fitting, f.number as fitting_number')
                        ->where($map)->group('f.id')->select();
            
            if (!$fitting) {
                $rst['success'] = false;
                $rst['errorMsg'] = '第 ' . ($k + 1) . ' 行配件编号（' . $map['number'] . '）不存在，无法导入';
                $this->ajaxReturn($rst);
            }
            
            if (count($fitting) > 1) {
                $rst['success'] = false;
                $rst['errorMsg'] = '第 ' . ($k + 1) . ' 行配件编号（' . $map['number']  . '）不唯一，无法导入！';
                $this->ajaxReturn($rst);
            }
            
            $numbers[] = $map['number'];
            
            $row = array(
                'phone' => $fitting[0]['phone'],
                'fitting' => $fitting[0]['fitting'],
                'fitting_id' => $fitting[0]['id'],
                'price' => $value[2],
                'fitting_number' => $fitting[0]['fitting_number']
            );
            
            $row_amount = 0;
            
            foreach ($orgs as $i => $v) {
                
                $amount = intval($value[$i]);
                
                if ($i < 3 || $amount <= 0) {
                    continue;
                }
                
                $row_amount += $amount;
                
                $row['org_name'] = $v;
                $row['organization_id'] = $organization[$v];
                $row['purchase_amount'] = $amount;
                
                $params[] = $row;
            }
            
            if ($row_amount == 0) {
                $rst['success'] = false;
                $rst['errorMsg'] = '第 ' . ($k + 1) . ' 行各仓库配件数量均为0！';
                $this->ajaxReturn($rst);
            }
        }
        
        $rst['success'] = true;
        $rst['data'] = $params;
        $this->ajaxReturn($rst);
    }
    
    /**
     * 采购配件列表
     */
    public function lists()
    {
        $id = I('get.id/d');
        $is_organization = I('get.is_organization/d');
        $is_check = I('get.is_check/d');
        
        if ($id <= 0) {
            $this->ajaxReturn(array());
        }
        
        $map = array('purchase_receipt_id' => $id);
        
        if ($is_check) {
            
            $join = 'prf left join fitting f on f.id = prf.fitting_id
                left join phone_fitting pf on f.id = pf.fitting_id
                left join phone p on p.id = pf.phone_id';
            
            $field = 'prf.purchase_receipt_id, prf.fitting_id, prf.price, 
                    group_concat(distinct(p.alias)) as phone, f.title as fitting, f.number as fitting_number, 
                    sum(purchase_amount) as purchase_amount, sum(amount) as amount';
            
            $list = M('purchase_receipt_fitting')->join($join)
                    ->where($map)->group('prf.fitting_id')->field($field)->select();
        } else {
            if ($is_organization) {
                $orgs = session('organizations');
                $map['prf.organization_id'] = array('in', array_keys($orgs));
            }
            
            $join = 'prf left join fitting f on f.id = prf.fitting_id
                left join organization o on o.id = prf.organization_id
                left join phone_fitting pf on f.id = pf.fitting_id
                left join phone p on p.id = pf.phone_id';
            
            $field = 'prf.*, group_concat(p.alias) as phone, f.title as fitting, f.number as fitting_number, o.alias as org_name';
            
            $list = M('purchase_receipt_fitting')->join($join)
                    ->where($map)->group('prf.id')->field($field)->order('prf.organization_id asc')->select();
        }
        
        $this->ajaxReturn($list);
    }
    
    /**
     * 批次
     *
     * @return void
     */
    public function batch()
    {
        $this->ajaxReturn(D('warehouse')->allotBatch());
    }
    
    /**
     * 采购入库单操作日志
     */
    public function logs()
    {
        $id = I('get.id/d');
        
        if ($id <= 0) {
            $this->ajaxReturn(array());
        }
        
        $this->ajaxReturn(M('purchase_receipt_log')->where(array('purchase_receipt_id' => $id))->select());
    }
    
    /**
     * 采购入库导出
     */
    public function export()
    {
        $exorders = array();
        $exorders[] = array(
            '城市代码' => '城市代码',
            '批次'    => '批次',
            '操作人'  => '操作人',
            '手机型号' => '手机型号',
            '物料编码' => '物料编码',
            '物料名称' => '物料名称',
            '成本'    => '成本',
            '交易数量' => '交易数量',
            '供应商' => '供应商',
            '总金额' => '总金额',
        );
        
        $map = array();
        $map = array('pr.status' => array('gt', 1));
        
        $post = I('post.');

        $orgs = session('organizations');

        if (!empty($orgs)) {
            $map['prf.organization_id'] = array('in', array_keys($orgs));
        }
        
        if (!empty($post['starttime']) && empty($post['endtime'])) {
            $map['pr.create_time'] = array('EGT', strtotime($post['starttime']));
        } else if (!empty($post['endtime'] && empty($post['starttime']))) {
            $map['pr.create_time'] = array('ELT', strtotime($post['endtime'] . '23:59:59'));
        } else if ($post['starttime'] && $post['endtime']) {
            $map['pr.create_time'] = array(array('egt', strtotime($post['starttime'])), array('elt', strtotime($post['endtime'] . '23:59:59')), 'and');
        }
        
        if (!empty($post['organization_id']) && $post['organization_id'] != 'all') {
            $map['prf.organization_id '] = $post['organization_id'];
        }
        
        if (is_numeric($post['status']) && $post['status'] != 'all') {
            $map['pr.status'] = $post['status'];
        }
        
        if (!empty($post['keyword'])) {
            $where = array();
            $where['f.number'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['f.title'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['pr.remark'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['pr.batch'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        
        $join = 'pr left join purchase_receipt_fitting prf on prf.purchase_receipt_id = pr.id
                left join provider on provider.id = pr.provider_id
                left join organization o on prf.organization_id=o.id
                left join fitting f on f.id = prf.fitting_id
                left join phone_fitting pf on pf.fitting_id = f.id
                left join phone p on p.id = pf.phone_id
                left join user u on pr.proposer=u.id';
        
        $list = M('purchase_receipt')->join($join)->where($map)
                ->field('pr.batch, u.username as proposer_name, o.alias as org_name, group_concat(p.alias) as phone,
                        f.number, f.title, prf.amount, prf.price, provider.title as provider')
                ->group('prf.id')
                ->order('batch asc, prf.organization_id asc')->select();

        foreach ($list as $item) {
            
            $exorders[] = array(
                '城市代码' => $item['org_name'],
                '批次'    => $item['batch'],
                '操作人'  => $item['proposer_name'],
                '手机型号' => $item['phone'],
                '物料编码' => $item['number'],
                '物料名称' => $item['title'],
                '成本'    => $item['price'],
                '交易数量' => $item['amount'],
                '供应商'  => $item['provider'],
                '总金额'  => $item['price'] > 0 ? $item['amount'] * $item['price'] : '0',
            );
        } 
        
        $filename = '采购入库单各城市物料分配表-' . date('Y-m-d H:i:s');
        $this->exportData($filename, $exorders);
    }
}