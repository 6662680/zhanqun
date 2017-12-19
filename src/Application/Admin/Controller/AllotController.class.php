<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 仓库调拨 Dates: 2016-10-09
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;
use Think\Exception;

class AllotController extends BaseController
{
    /**
     * 调拨页面
     *
     * @return void
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 调拨数据
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
            $where = array();
            $where['proposer_org']  = array('in', array_keys($orgs));
            $where['auditor_org']  = array('in', array_keys($orgs));
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        } else {
            $this->ajaxReturn($rst);
        }

        if (!empty($post['starttime'])) {
            $map['time'] = array('EGT', strtotime($post['starttime']));
        }

        if (!empty($post['endtime'])) {
            $map['time '] = array('ELT', strtotime($post['starttime']));
        }

        if (!empty($post['proposer_org']) && $post['proposer_org'] != 'all') {
            $where = array();
            $where['proposer_org']  = $post['proposer_org'];
            $where['auditor_org']  = $post['proposer_org'];
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        if (!empty($post['type']) && $post['type'] != 'all') {
            $map['a.type'] = $post['type'];
        }

        if (is_numeric($post['status']) && $post['status'] != 'all') {
            $map['a.status'] = $post['status'];
        }

        $join = 'a left join organization o on a.proposer_org=o.id 
            left join organization o2 on a.auditor_org=o2.id 
            left join user u on a.proposer=u.id 
            left join user u2 on a.auditor=u2.id';

        $count = M('allot')->join($join)->where($map)->count();
        $rst['total'] = $count;

        $list = M('allot')->join($join)->where($map)->limit($this->page())
            ->field('a.id, u.username as proposer, o.name as proposer_org, u2.username as auditor, 
                o2.name as auditor_org, a.status, a.type, a.time, a.batch, a.fittings, a.remark')->order('a.id desc')->select();
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
        array_unshift($orgs,array('alias'=>'全部','id'=>''));
        $this->ajaxReturn(array_values($orgs));
    }

    /**
     * 机型
     *
     * @return void
     */
    public function phones()
    {
        $list = M('phone')->where()->field('id, alias')->order('alias asc')->select();
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
        $sql = "select f.id, concat(f.title, '(', f.number, ')') as name from phone_fitting pf left join fitting f on pf.fitting_id=f.id where pf.phone_id={$phoneId} and f.id > 0";
        $fittings = M()->query($sql);
        $this->ajaxReturn($fittings);
    }

    /**
     * 仓库（组织）
     *
     * @return void
     */
    public function proposerorg()
    {
        $orgs = session('organizations');
        $this->ajaxReturn(array_values($orgs));
    }

    /**
     * 仓库（组织）
     *
     * @return void
     */
    public function auditororg()
    {
        $map = array();
        $map['status'] = 1;
        $orgs = M('organization')->where($map)->field('id, alias')->select();
        $this->ajaxReturn($orgs);
    }

    /**
     * 新增
     *
     * @return void
     */
    public function add()
    {
        $rst = array();
        $data = array();
        $post = I('post.');
        $data['type'] = (int)$post['type'];
        $data['proposer_org'] = $post['proposer_org'];
        
        if (!$data['proposer_org']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请设置申请仓库！';
            $this->ajaxReturn($rst);
        }
        
        $data['auditor_org'] = $post['auditor_org'];
        
        if (!$data['proposer_org']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请设置目标仓库！';
            $this->ajaxReturn($rst);
        }
        
        $data['proposer'] = session('userId');
        $data['remark'] = $post['remark'];

        $fittings = array();

        if (count($post['fittings']) > 0) {    
            
            foreach ($post['fittings'] as $key => $value) {
                $info = explode('_,', $value);
                
                if ($info[4] <= 0) {
                    continue;
                }
                
                $item = array();
                $item['phone'] = $info[0];
                $item['phone_id'] = $info[1];
                $item['fitting'] = $info[2];
                $item['fitting_id'] = $info[3];
                $item['amount'] = $info[4];
                $fittings[] = $item;
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '请设置调拨物料！';
            $this->ajaxReturn($rst);
        }

        $data['fittings'] = json_encode($fittings);
        $data['time'] = time();
        
        if (M('allot')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 调拨审核
     *
     * @return void
     */
    public function audit()
    {
        /** 状态：-1 取消 (审核不通过)  0 待审核 1 审核通过 2 已发货 3 已收货 */
        $post = I('post.');
        $map = array();
        $rst = array();
        $map['id'] = $post['id'];
        $item = D('allot')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] != 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '非待审核状态，请刷新页面！';
            $this->ajaxReturn($rst);
        }

        $orgs = session('organizations');

        /** 权限判断 属于目标仓库方可审核 */
        if (!in_array($item['auditor_org'], array_keys($orgs))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '没有权限审核此申请！请联系目标仓库管理员审核！';
            $this->ajaxReturn($rst);
        }

        $flag = $post['flag'];
        $data = array();

        /** 审核通过 or 不通过 */
        if ($flag) {
            $data['status'] = 1;
        } else {
            $data['status'] = -1;
        }

        /** 审核人 */
        $data['auditor'] = session('userId');

        if (D('allot')->where($map)->limit(1)->save($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '操作失败！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 调拨发货
     *
     * @return void
     */
    public function send()
    {
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = M('allot')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] != 1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此记录不在待发货状态，请刷新页面！';
            $this->ajaxReturn($rst);
        }

        $orgs = session('organizations');

        /** 权限判断 申请属于目标仓库方可发货 */
        if ($item['type'] == 1 && !in_array($item['auditor_org'], array_keys($orgs))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '没有权限发货此申请！请联系目标仓库管理员发货！';
            $this->ajaxReturn($rst);
        }

        /** 权限判断 退还属于申请仓库方可发货 */
        if ($item['type'] == 2 && !in_array($item['proposer_org'], array_keys($orgs))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '没有权限发货此申请！请联系申请仓库管理员发货！';
            $this->ajaxReturn($rst);
        }

        try {
            M()->startTrans();
    
            $sendRst = D('warehouse')->send($item);
    
            if ($sendRst === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新仓库数据错误！';
                $this->ajaxReturn($rst);
            }
    
            /** 更新采购单状态 */
            $data = array();
            $data['status'] = 2;
            /** 批次信息 */
            $data['batch'] = json_encode($sendRst);
    
            if (M('allot')->where($map)->save($data) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新调拨申请数据错误！';
                $this->ajaxReturn($rst);
            }
    
            M()->commit();
            $rst['success'] = true;
        } catch (\Exception $e) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = $e->getMessage();
        }

        $this->ajaxReturn($rst);
    }


    /**
     * 调拨收货
     *
     * @return void
     */
    public function receive()
    {
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = M('allot')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] != 2) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此记录不在发货(待收货)状态，请刷新页面！';
            $this->ajaxReturn($rst);
        }

        $orgs = session('organizations');

        /** 权限判断 申请属于申请仓库方可收货 */
        if ($item['type'] == 1 && !in_array($item['proposer_org'], array_keys($orgs))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '没有权限收货此申请！请联系目标仓库管理员收货！';
            $this->ajaxReturn($rst);
        }

        /** 权限判断 退还属于目标仓库方可收货 */
        if ($item['type'] == 2 && !in_array($item['auditor_org'], array_keys($orgs))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '没有权限收货此申请！请联系申请仓库管理员收货！';
            $this->ajaxReturn($rst);
        }

        try {
            M()->startTrans();
    
            if (D('warehouse')->receive($item) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新仓库数据错误！';
                $this->ajaxReturn($rst);
            }
    
            /** 更新采购单状态 */
            $data = array();
            $data['status'] = 3;
    
            if (M('allot')->where($map)->save($data) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新调拨申请数据错误！';
                $this->ajaxReturn($rst);
            }
            
            M()->commit();
            $rst['success'] = true;
        } catch (\Exception $e) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = $e->getMessage();
        }

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
    
        $params = array();
        $numbers = array();
    
        foreach ($data as $k => $value) {
    
            if ($k < 1) {
                continue;
            }
    
            $value[2] = (int) $value[2];
    
            if (empty($value[0]) || empty($value[1]) || $value[2] < 1) {
                continue;
            }
    
            $map = array(
                'number' => trim($value[0]),
            );
    
            $fitting = M('fitting')->join('f left join phone_fitting pf on pf.fitting_id = f.id')
                        ->join('left join phone p on p.id = pf.phone_id')
                        ->field('f.id, group_concat(pf.phone_id) as phone_id, group_concat(p.alias) as phone, concat(f.title, "(", f.number, ")") as fitting')
                        ->where($map)->group('f.id')->select();
    
            if (!$fitting) {
                $rst['success'] = false;
                $rst['errorMsg'] = '配件编号（' . $map['number'] . '）不存在';
                $this->ajaxReturn($rst);
            }
    
            if (count($fitting) > 1) {
                $rst['success'] = false;
                $rst['errorMsg'] = '配件编号（' . $map['number']  . '）不唯一，无法导入！';
                $this->ajaxReturn($rst);
            }
            
            if (in_array($map['number'], $numbers)) {
                $rst['success'] = false;
                $rst['errorMsg'] = '配件编号（' . $map['number']  . '）存在重复导入,请检查！';
                $this->ajaxReturn($rst);
            }
            $numbers[] = $map['number'];
    
            $fitting = $fitting[0];
    
            $params[] = array(
                'phone_id' => $fitting['phone_id'],
                'phone' => $fitting['phone'],
                'fitting' => $fitting['fitting'],
                'fittings_id' => $fitting['id'],
                'amount' => $value[2]
            );
        }
    
        $rst['success'] = true;
        $rst['data'] = $params;
        $this->ajaxReturn($rst);
    }

    /**
     * 回退
     *
     * @return void
     */
    public function rollback()
    {
        /** 状态：-1 取消 (审核不通过)  0 待审核 1 审核通过 2 已发货 3 已收货 */
        $post = I('post.');
        $map = array();
        $rst = array();
        $map['id'] = $post['id'];
        $item = D('allot')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] != 1 && $item['status'] != -1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '非审核状态，请刷新页面！';
            $this->ajaxReturn($rst);
        }

        $orgs = session('organizations');

        /** 权限判断 属于目标仓库方可审核 */
        if (!in_array($item['auditor_org'], array_keys($orgs))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '没有权限审核此申请！请联系目标仓库管理员审核！';
            $this->ajaxReturn($rst);
        }

        $data = array();
        $data['status'] = 0;

        /** 审核人 */
        $data['auditor'] = session('userId');

        if (D('allot')->where($map)->limit(1)->save($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '操作失败！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 回退
     *
     * @return void
     */
    public function edit()
    {
        $data = I('post.');
        $rst = array();

        if (empty($data['fittings'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = "请设置需要调拨的废料！";
            $this->ajaxReturn($rst);
        }

        if (!in_array($data['type'], array(1, 2))) {
            $rst['success'] = false;
            $rst['errorMsg'] = "请设置正确的申请类型！";
            $this->ajaxReturn($rst);
        }

        $model = M('allot');
        $model->find($data['id']);

        if ($model->status != 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = "已经审核的状态无法编辑！";
            $this->ajaxReturn($rst);
        }

        $fittings = array();

        foreach($data['fittings'] as $key => $value) {
            $value  = explode('_', $value);
            
            if ($value[4] <= 0) {
                continue;
            }
            
            $fittings[$key]['phone'] = $value[0];
            $fittings[$key]['phone_id'] = $value[1];
            $fittings[$key]['fitting'] = $value[2];
            $fittings[$key]['fitting_id'] = $value[3];
            $fittings[$key]['amount'] = $value[4];
        }

        $data['fittings'] = json_encode(array_values($fittings));

        $model->fittings = $data['fittings'];
        $model->remark = $data['remark'];
        
        if ($model->save() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = "更新失败！";
        }

        $this->ajaxReturn($rst);
    }

}