<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 采购表 Dates: 2016-09-27
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;
use Think\Exception;

class PurchaseController extends BaseController
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
            $map['p.proposer_org'] = array('in', array_keys($orgs));
        }

        if (!empty($post['starttime']) && empty($post['endtime'])) {
            $map['p.create_time'] = array('EGT', strtotime($post['starttime']));
        } else if (!empty($post['endtime'] && empty($post['starttime']))) {
            $map['p.create_time'] = array('ELT', strtotime($post['endtime'] . '23:59:59'));
        } else if ($post['starttime'] && $post['endtime']) {
            $map['p.create_time'] = array(array('egt', strtotime($post['starttime'])), array('elt', strtotime($post['endtime'] . '23:59:59')), 'and');
        }
        
        if (!empty($post['proposer_org']) && $post['proposer_org'] != 'all') {
            $map['p.proposer_org '] = $post['proposer_org'];
        }

        if (!empty($post['type']) && $post['type'] != 'all') {
            $map['p.type'] = $post['type'];
        }

        if (!empty($post['status']) && $post['status'] != 'all') {
            $map['p.status'] = $post['status'];
        }

        $count = M('purchase as p')->where($map)->count();
        $rst['total'] = $count;

        $join = 'p left join organization o on p.proposer_org=o.id 
            left join organization o2 on p.auditor_org=o2.id 
            left join user u on p.proposer=u.id 
            left join user u2 on p.auditor=u2.id';


        $list = M('purchase')->join($join)->where($map)->limit($this->page())
            ->field('p.*, u.username as proposer_name, o.name as proposer_org_name, u2.username as auditor_name, 
                o2.name as auditor_org_name')->order('id desc')->select();
        $rst['rows'] = $list;
        
        $this->ajaxReturn($rst);
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
        $sql = "select f.id, concat(f.title, '(', f.number, ')') as name from phone_fitting pf left join fitting f on pf.fitting_id=f.id where pf.phone_id={$phoneId} and f.id > 0";
        $fittings = M()->query($sql);
        $this->ajaxReturn($fittings);
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
     * 仓库（组织）
     *
     * @return void
     */
    public function proposerorg()
    {
        $orgs = session('organizations');
        array_unshift($orgs,array('alias'=>'全部','id'=>''));
        $this->ajaxReturn(array_values($orgs));
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
        $data['type'] = $post['type'];
        $data['proposer_org'] = $post['proposer_org'];
        $data['proposer'] = session('userId');
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['remark'] = $post['remark'];

        if ($post['type'] == 2) {
            $data['batch'] = $post['batch'];
            $data['provider_id'] = $post['provider_id'];
        }

        $fittings = array();
        $fittingIds = array();

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
                $item['price'] = $info[5];
                $fittings[] = $item;
                $fittingIds[] = $info[3];
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '配件为空！';
            $this->ajaxReturn($rst);
        }

        /** 配件数据验证 */
        $map = array();
        $map['id'] = array('in', $fittingIds);
        $count = M('fitting')->where($map)->count();
        
        if ($count != count($fittingIds)) {
            $rst['success'] = false;
            $rst['errorMsg'] = '配件存在重复记录,请检查！';
            $this->ajaxReturn($rst);
        }

        $data['fittings'] = json_encode($fittings);
        
        if (M('purchase')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 更新
     *
     * @return void
     */
    public function save()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $item = D('purchase')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] != 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '审核后不可编辑！';
            $this->ajaxReturn($rst);
        }

        $post = I('post.');
        $data = array();
        $data['type'] = $post['type'];
        $data['proposer_org'] = $post['proposer_org'];
        $data['proposer'] = session('userId');
        $data['update_time'] = time();
        $data['remark'] = $post['remark'];

        if ($post['type'] == 2) {
            $data['batch'] = $post['batch'];
            $data['provider_id'] = $post['provider_id'];
        }

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
                $item['price'] = $info[5];
                $fittings[] = $item;
            }
        }

        $data['fittings'] = json_encode($fittings);

        if (D('purchase')->where($map)->save($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '更新失败！';
        }

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
        $item = D('purchase')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] != 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '审核后不可删除！';
            $this->ajaxReturn($rst);
        }

        if (D('purchase')->where($map)->limit(1)->delete() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
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
        $post = I('post.');
        $map = array();
        $rst = array();
        $map['id'] = $post['id'];
        $item = D('purchase')->where($map)->find();

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

        $flag = $post['flag'];
        $data = array();

        /** 审核通过 or 不通过 */
        if ($flag) {

            /** 采购申请 or 入库采购 */
            if ($item['type'] == 1) {
                /** @todo 采购申请 转换为调拨单 */
                $data['status'] = 5;
            } else {
                $data['status'] = 1;
            }
        } else {
            $data['status'] = -1;
        }

        $data['auditor'] = session('userId');

        if (D('purchase')->where($map)->limit(1)->save($data) !== false) {
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
    public function rollback()
    {
        $post = I('post.');
        $map = array();
        $rst = array();
        $map['id'] = $post['id'];
        $item = D('purchase')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] != 1 && $item['status'] != 5) {
            $rst['success'] = false;
            $rst['errorMsg'] = '非审核通过状态，请刷新页面！';
            $this->ajaxReturn($rst);
        }

        $data = array();
        $data['status'] = 0;
        $data['auditor'] = session('userId');

        if (D('purchase')->where($map)->limit(1)->save($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '操作失败！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 转换
     *
     * @return void
     */
    public function transform()
    {
        $post = I('post.');
        $map = array();
        $rst = array();
        $map['id'] = $post['id'];
        $item = D('purchase')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] != 5) {
            $rst['success'] = false;
            $rst['errorMsg'] = '非审核通过状态，请刷新页面！';
            $this->ajaxReturn($rst);
        }

        $data = array();
        $data['proposer'] = $item['proposer'];
        $data['proposer_org'] = $item['proposer_org'];
        $data['auditor_org'] = 11; #总仓 固定
        $data['type'] = 1; #申请 固定
        $data['fittings'] = $item['fittings'];
        $data['time'] = time();
        $data['status'] = 0;
        $data['remark'] = $item['remark'];

        if (D('allot')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '操作失败！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 待入库
     *
     * @return void
     */
    public function waitin()
    {
        $this->display();
    }

    /**
     * 待入库数据
     *
     * @return void
     */
    public function inrows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        if (!empty($post['starttime']) && empty($post['endtime'])) {
            $map['p.create_time'] = array('EGT', strtotime($post['starttime']));
        } else if (!empty($post['endtime'] && empty($post['starttime']))) {
            $map['p.create_time'] = array('ELT', strtotime($post['endtime'] . '23:59:59'));
        } else if ($post['starttime'] && $post['endtime']) {
            $map['p.create_time'] = array(array('egt', strtotime($post['starttime'])), array('elt', strtotime($post['endtime'] . '23:59:59')), 'and');
        }
        
        if (!empty($post['proposer_org']) && $post['proposer_org'] != 'all') {
            $map['p.proposer_org'] = $post['proposer_org'];
        } else if (!empty($orgs)) {
            /** 地区限制  */
            $orgs = session('organizations');
            $map['p.proposer_org'] = array('in', array_keys($orgs));
        }

        if (!empty($post['type']) && $post['type'] != 'all') {
            $map['p.type'] = $post['type'];
        }

        if (!empty($post['status']) && $post['status'] != 'all') {
            $map['p.status'] = $post['status'];
        } else {
            $map['p.status'] = array('in', array(1, 2));
        }


        $join = 'p left join organization o on p.proposer_org=o.id 
            left join organization o2 on p.auditor_org=o2.id 
            left join user u on p.proposer=u.id 
            left join user u2 on p.auditor=u2.id';

        $count = M('purchase')->where($map)->join($join)->count();
        $rst['total'] = $count;

        $list = M('purchase')->join($join)->where($map)->limit($this->page())
            ->field('p.id, u.username as proposer, o.name as proposer_org, u2.username as auditor, 
                o2.name as auditor_org, p.status, p.type, p.create_time, p.batch, p.provider_id, p.fittings, p.update_time, p.remark')->order('id desc')->select();
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 入库
     *
     * @return void
     */
    public function putin()
    {
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $item = M('purchase')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] != 1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此记录不在待入库状态，请刷新页面！';
            $this->ajaxReturn($rst);
        }

        try {
            M()->startTrans();
    
            if (D('warehouse')->putin($item) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新仓库数据错误！';
                $this->ajaxReturn($rst);
            }
    
            /** 更新采购单状态 */
            $data = array();
            $data['status'] = 2;
    
            if (M('purchase')->where($map)->save($data) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新采购单数据错误！';
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
    
            if (empty($value[0]) || empty($value[1]) || $value[2] < 1 || !is_numeric($value[3]) || $value[3] < 0) {
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
                'fitting_id' => $fitting['id'],
                'amount' => $value[2],
                'price' => $value[3],
            );
        }
        
        $rst['success'] = true;
        $rst['data'] = $params;
        $this->ajaxReturn($rst);
    }
}