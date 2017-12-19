<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 报损 Dates: 2016-10-09
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class BreakageController extends BaseController
{
    /**
     * 报损页面
     *
     * @return void
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 报损数据
     *
     * @return void
     */
    public function rows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        $orgs = session('organizations');

        if (!empty($post['starttime']) && empty($post['endtime'])) {
            $map['b.time'] = array('egt', strtotime($post['starttime']));
        }

        if (!empty($post['endtime']) && empty($post['starttime'])) {
            $map['b.time '] = array('elt', strtotime($post['endtime']));
        }


        if ($post['starttime'] && $post['endtime']) {
            $map['b.time '] = array(array('gt',strtotime($post['starttime'])),array('lt',strtotime($post['endtime']) + 24*60*60-1),'and');
        }

        if (!empty($post['type']) && $post['type'] != 'all') {
            $map['b.type'] = $post['type'];
        }
        
        if (is_numeric($post['status']) && $post['status'] != 'all') {
            $map['b.status'] = $post['status'];
        }

        if (!empty($orgs)) {
            $map['b.organization_id'] = array('in', array_keys($orgs));
        } else {
            $this->ajaxReturn($rst);
        }

        if (!empty($post['proposer_org']) && $post['proposer_org'] != 'all') {
            $map['b.organization_id '] = $post['proposer_org'];
        }

        if (!empty($post['keyword'])) {
            $where = array();
            $where['f.number'] = array('like', '%' . $post['keyword'] . '%');
            $where['f.title'] = array('like', '%' . $post['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $join = 'b left join fitting f on b.fitting_id=f.id 
                left join organization as o on b.organization_id=o.id
                left join user u on b.user_id=u.id 
                left join engineer e on b.engineer_id=e.id';

        $count = M('breakage')->where($map)->join($join)->count();
        $rst['total'] = $count;

        $list = M('breakage')->join($join)->where($map)->limit($this->page())
                ->field("b.id, b.type, b.numbers, b.amount, b.status, b.remark, b.time, e.name as engineer, 
                        u.username as auditor, b.organization_id, o.alias as organization, b.fitting_id, 
                        concat(f.title, '(', f.number, ')') as fitting")
                ->group('b.id')->order('b.id')->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 组织地区
     *
     * @return void
     */
    public function organization()
    {
        $orgs = session('organizations');
        array_unshift($orgs,array('alias'=>'全部','id'=>false));
        $this->ajaxReturn(array_values($orgs));
    }
    
    /**
     * 添加报损
     * 
     * @return void
     */
    public function add()
    {
        $rst = array();
        $data = I('post.');
        $flag = true;
        
        if (!in_array($data['type'], array(1, 2))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择报损类型！';
            $this->ajaxReturn($rst);
        }
        
        if (!$data['organization_id']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择仓库！';
            $this->ajaxReturn($rst);
        }
        
        if ($data['type'] == 1 && !$data['engineer_id']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择责任人！';
            $this->ajaxReturn($rst);
        } else if ($data['type'] == 2 && !$data['user_id']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择责任人！';
            $this->ajaxReturn($rst);
        }
        
        if (!$data['fitting_id']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择配件！';
            $this->ajaxReturn($rst);
        }
        
        if ($data['amount'] <= 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择配件报损数量！';
            $this->ajaxReturn($rst);
        }
        
        if (!$data['numbers']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择配件唯一编码！';
            $this->ajaxReturn($rst);
        }
        
        $numbers = explode(',', $data['numbers']);
        
        if ($data['amount'] != count($numbers)) {
            $rst['success'] = false;
            $rst['errorMsg'] = '报损数量与输入编码数量不一致！';
            $this->ajaxReturn($rst);
        }
        
        if ($data['type'] == 1) {
            unset($data['user_id']);
        } else {
            unset($data['engineer_id']);
        }
        
        if ($data['type'] == 1) {//工程师报损
            $map['number'] = array('in', $numbers);
            $map['fitting_id'] = $data['fitting_id'];
            $map['engineer_id'] = $data['engineer_id'];
            $map['status'] = 3;
            $amount = M('stock')->where($map)->count();
            
            if ($data['amount'] > $amount) {
                $rst['success'] = false;
                $rst['errorMsg'] = '工程师配件不足或已使用！';
                $this->ajaxReturn($rst);
            }
        } else { //管理员报损
            $map['number'] = array('in', $numbers);
            $map['organization_id'] = $data['organization_id'];
            $map['fitting_id'] = $data['fitting_id'];
            $map['status'] = 1;
            $amount = M('stock')->where($map)->count();
            
            if ($data['amount'] > $amount) {
                $rst['success'] = false;
                $rst['errorMsg'] = '仓库配件不足或已分配给工程师！';
                $this->ajaxReturn($rst);
            }
        }
        
        $data['time'] = time();
        $data['status'] = 0;
        
        if (M('breakage')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '报损失败！';
        }
        
        $this->ajaxReturn($rst);
    }

    /**
     * 审核
     *
     * @return void
     */
    public function auditor()
    {
        /** 状态 -1 审核不通过 0 待审核 1审核通过 */
        
        $data = I('post.');
        $map =array();
        $map['id'] = $data['id'];
        $item = M('breakage')->where($map)->find();
        
        if ($item) {
            
            if ($item['status'] != 0) {
                $rst['success'] = false;
                $rst['errorMsg'] = '记录不在未审核状态，请刷新页面！';
                $this->ajaxReturn($rst);
            }
            
            if ($data['flag'] == 1) {
                
                $flag = true;
                
                if ($item['type'] == 1) {//工程师
                    
                    $where = array(
                        'engineer_id' => $item['engineer_id'], 
                        'fitting_id' => $item['fitting_id']
                    );
                    $where['status'] = 3;
                    $where['number'] = array('in', $item['numbers']);
                    
                    $stocks = M('stock')->where($where)->limit($item['amount'])->getField('id, price, batch, provider_id');
                    
                    if (count($stocks) < $item['amount']) {
                        $rst['success'] = false;
                        $rst['errorMsg'] = '工程师配件库存数量不足或对应编号配件已使用！';
                        $this->ajaxReturn($rst);
                    }
                    
                    M()->startTrans();
                    
                    //减少工程师库存
                    if (M('engineer_warehouse')->where($where)->setDec('amount', $item['amount']) === false) {
                        $flag = false;
                    }
                    
                    //工程师配件出入库
                    $param = array(
                        'type' => 3, 
                        'inout' => 2,
                        'engineer_id' => $item['engineer_id'],
                        'user_id' => session('userId'),
                        'fittings_id' => $item['fitting_id'],
                        'amount' => $item['amount'],
                        'time' => time()
                    );
                    
                    if (M('engineer_inout')->add($param) === false) {
                        $flag = false;
                    }
                    
                } else { //仓库
                    $where = array(
                        'organization_id' => $item['organization_id'], 
                        'fitting_id' => $item['fitting_id']
                    );
                    $where['status'] = 1;
                    $where['number'] = array('in', $item['numbers']);
                    
                    $stocks = M('stock')->where($where)->limit($item['amount'])->getField('id, price, batch, provider_id');
                    
                    if (count($stocks) < $item['amount']) {
                        $rst['success'] = false;
                        $rst['errorMsg'] = '仓库配件库存数量不足或对应编号配件已分配工程师！';
                        $this->ajaxReturn($rst);
                    }
                    
                    M()->startTrans();
                    
                    //减少仓库库存
                    if (M('warehouse')->where($where)->setDec('amount', $item['amount']) === false) {
                        $flag = false;
                    }
                }
                
                //配件出入库
                $param = array(
                    'type' => 4,
                    'inout' => 2,
                    'batch' => current($stocks)['batch'],
                    'organization_id' => $item['organization_id'],
                    'provider_id' => current($stocks)['provider_id'],
                    'engineer_id' => $item['engineer_id'],
                    'user_id' => $item['user_id'],
                    'fitting_id' => $item['fitting_id'],
                    'amount' => $item['amount'],
                    'price' => current($stocks)['price'],
                    'time' => time()
                );
                
                if (M('inout')->add($param) === false) {
                    $flag = false;
                }
                
                //库存数据更新为报损
                $param = array('status' => -1, 'consume_time' => time());
                
                if (M('stock')->where(array('id' => array('in', array_keys($stocks))))->save($param) === false) {
                    $flag = false;
                }
                
                //设置报损处理状态
                $param = array('status' => 1, 'stock_ids' => implode(',', array_keys($stocks)), 'auditor' => session('userId'));
                
                if (M('breakage')->where(array('id' => $item['id']))->save($param) === false) {
                    $flag = false;
                }
                
                if ($flag) {
                    M()->commit();
                
                    $rst['success'] = true;
                } else {
                    M()->rollback();
                
                    $rst['success'] = false;
                    $rst['errorMsg'] = '操作失败！';
                }
                
            } else {
                $param['status'] = -1;
                $param['user_id'] = session('userId');
                
                if (M('breakage')->where($map)->save($param) !== false) {
                    $rst['success'] = true;
                } else {
                    $rst['success'] = false;
                    $rst['errorMsg'] = '操作失败！';
                }
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '报损记录不存在！';
        }
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 工程师
     */
    public function engineers()
    {
        $get = I('get.');
        $map = array();
        
        if ($get['organization_id'] && !in_array($get['organization_id'], array_keys(session('organizations')))) {
            $this->ajaxReturn(array());
        }
        
        if ($get['organization_id']) {
            $map['organization_id'] = $get['organization_id'];
        } else {
            $map['organization_id'] = array('in', array_keys(session('organizations')));
        }
        
        $list = M('engineer')->where($map)->field('id, name')->select();
        $this->ajaxReturn($list);
    }
    
    /**
     * 物料
     */
    public function fittings() 
    {
        $get = I('get.');
        $map = array();
        
        if (!$get['organization_id'] || !in_array($get['organization_id'], array_keys(session('organizations')))) {
            $this->ajaxReturn(array());
        }
        
        if ($get['organization_id'] && $get['engineer_id']) {
            $map['ew.engineer_id'] = $get['engineer_id'];
            $map['ew.amount'] = array('gt', 0);
            $map['e.organization_id'] = $get['organization_id'];
            
            $list = M('engineer_warehouse')->join('ew left join fitting f on ew.fittings_id = f.id')
                    ->join('left join engineer e on e.id = ew.engineer_id')
                    ->where($map)->field('f.id, concat(f.title, "(", f.number, ")", " * ", amount) as title')->select();
        } else if ($get['organization_id'] && !$get['engineer_id']) {
            
            $map['w.organization_id'] = $get['organization_id'];
            $map['w.amount'] = array('gt', 0);
            $list = M('warehouse')->join('w left join fitting f on w.fitting_id = f.id')
                    ->where($map)->field('f.id, concat(f.title, "(", f.number, ")", " * ", amount) as title')->select();
        } else {
            
            $map['w.amount'] = array('gt', 0);
            $list = M('fitting')->join('f left join warehouse w on w.fitting_id = f.id')
                    ->where($map)->field('f.id, concat(f.title, "(", f.number, ")", " * ", amount) as title')->select();
        }
        
        $this->ajaxReturn($list);
    }
    
    /**
     * 物料数量
     */
    public function fittingsAmount()
    {
        $amount = I('get.amount/d');
        
        if ($amount <= 0) {
            $this->ajaxReturn(array());
        }
        
        for ($i = 1; $i <= $amount; $i++) {
            $list[]['amount'] = $i;
        }
        
        $this->ajaxReturn($list);
    }
    
    /**
     * 操作人
     */
    public function users()
    {
        $list = M('user')->field('id, username')->select();
        $this->ajaxReturn($list);
    }
}