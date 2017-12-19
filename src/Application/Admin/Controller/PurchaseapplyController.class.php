<?php

// +------------------------------------------------------------------------------------------ 
// | Author: tcg 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 采购申请 Dates: 2017-03-08
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;
use Think\Exception;

class PurchaseapplyController extends BaseController
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
            $map['proposer_org'] = array('in', array_keys($orgs));
        }

        if (!empty($post['starttime']) && empty($post['endtime'])) {
            $map['pa.create_time'] = array('EGT', strtotime($post['starttime']));
        } else if (!empty($post['endtime'] && empty($post['starttime']))) {
            $map['pa.create_time'] = array('ELT', strtotime($post['endtime'] . '23:59:59'));
        } else if ($post['starttime'] && $post['endtime']) {
            $map['pa.create_time'] = array(array('egt', strtotime($post['starttime'])), array('elt', strtotime($post['endtime'] . '23:59:59')), 'and');
        }
        
        if (!empty($post['proposer_org']) && $post['proposer_org'] != 'all') {
            $map['pa.proposer_org '] = $post['proposer_org'];
        }
        
        if (is_numeric($post['status']) && $post['status'] != 'all') {
            $map['pa.status'] = $post['status'];
        }
        
        if (!empty($post['keyword'])) {
            $where = array();
            $where['f.number'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['f.title'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['pa.remark'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $count = M('purchase_apply')->join('pa left join purchase_apply_fitting paf on paf.purchase_apply_id = pa.id')
                ->join('left join fitting f on f.id = paf.fitting_id')->where($map)->count('distinct(pa.id)');
        $rst['total'] = $count;

        $join = 'pa left join organization o on pa.proposer_org=o.id 
                left join purchase_apply_fitting paf on paf.purchase_apply_id = pa.id
                left join fitting f on f.id = paf.fitting_id
                left join user u on pa.proposer=u.id 
                left join user u2 on pa.auditor=u2.id';

        $list = M('purchase_apply')->join($join)->where($map)->limit($this->page())
            ->field('pa.*, u.username as proposer_name, o.name as proposer_org_name, u2.username as auditor_name')
            ->group('pa.id')
            ->order('id desc')->select();
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
     * 仓库（组织）
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
     * 增加
     *
     * @return void
     */
    public function add()
    {
        $rst = array();
        $data = array();
        $post = I('post.');
        $data['proposer_org'] = $post['proposer_org'];
        $data['proposer'] = session('userId');
        $data['create_time'] = time();
        $data['remark'] = $post['remark'];

        $fittings = array();
        $fittingIds = array();
        
        foreach ($post['fittings'] as $key => $value) {
            $info = explode('_', $value);
        
            if ($info[1] <= 0) {
                continue;
            }
        
            $item = array();
            $item['organization_id'] = $post['proposer_org'];
            $item['fitting_id'] = $info[0];
            $item['amount'] = $info[1];
            
            $fittings[] = $item;
            $fittingIds[] = $info[0];
        }

        /** 配件数据验证 */
        
        if (count($fittings) == 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '采购申请配件不能为空！';
            $this->ajaxReturn($rst);
        }
        
        if (count($fittingIds) != count(array_unique($fittingIds))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '配件存在重复记录,请检查！';
            $this->ajaxReturn($rst);
        }
        
        M()->startTrans();
        
        $id = M('purchase_apply')->add($data);
        
        if ($id === false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '提交采购申请失败！';
            $this->ajaxReturn($rst);
        }
        
        foreach ($fittings as &$item) {
            $item['purchase_apply_id'] = $id;
        }
        
        if (M('purchase_apply_fitting')->addAll($fittings) === false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '提交采购申请失败！';
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
        $item = M('purchase_apply')->where($map)->find();

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

        $rst = array();
        $data = array();
        $post = I('post.');
        $data['proposer_org'] = $post['proposer_org'];
        $data['remark'] = $post['remark'];

        $fittings = array();
        $fittingIds = array();
        
        foreach ($post['fittings'] as $key => $value) {
            $info = explode('_', $value);
        
            if ($info[1] <= 0) {
                continue;
            }
        
            $param = array();
            $param['purchase_apply_id'] = $item['id'];
            $param['organization_id'] = $post['proposer_org'];
            $param['fitting_id'] = $info[0];
            $param['amount'] = $info[1];
            
            $fittings[] = $param;
            $fittingIds[] = $info[0];
        }

        /** 配件数据验证 */
        
        if (count($fittings) == 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '采购申请配件不能为空！';
            $this->ajaxReturn($rst);
        }
        
        if (count($fittingIds) != count(array_unique($fittingIds))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '配件存在重复记录,请检查！';
            $this->ajaxReturn($rst);
        }
        
        $fittinglists = M('purchase_apply_fitting')->where(array('purchase_apply_id' => $item['id']))
                        ->field('purchase_apply_id, organization_id, fitting_id, amount')->select();
        
        M()->startTrans();
        
        if (M('purchase_apply')->where($map)->save($data) === false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '编辑采购申请失败！';
            $this->ajaxReturn($rst);
        }
        
        if ($fittinglists != $fittings) {
            
            if (M('purchase_apply_fitting')->where(array('purchase_apply_id' => $item['id']))->delete() === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '编辑采购申请失败！';
                $this->ajaxReturn($rst);
            }
            
            if (M('purchase_apply_fitting')->addAll($fittings) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '编辑采购申请失败！';
                $this->ajaxReturn($rst);
            }
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
        
        $item = M('purchase_apply')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($item['status'] != 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '采购申请已审核后不可删除！';
            $this->ajaxReturn($rst);
        }
        
        M()->startTrans();
        
        $map = array('purchase_apply_id' => $id);
        
        if (M('purchase_apply_fitting')->where($map)->delete() === false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
            $this->ajaxReturn($rst);
        }
        
        $map = array('id' => $id);

        if (M('purchase_apply')->where($map)->limit(1)->delete() !== false) {
            M()->commit();
            $rst['success'] = true;
        } else {
            M()->rollback();
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
    public function audit()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $item = M('purchase_apply')->where($map)->find();
        
        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }
        
        if ($item['status'] != 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '采购申请已审核完成！';
            $this->ajaxReturn($rst);
        }
        
        $rst = array();
        $data = array();
        $post = I('post.');
        $data['auditor'] = session('userId');
        $data['auditor_time'] = time();
        $data['status'] = $post['status'];
        $data['remark'] = $post['remark'];
        
        //审核拒绝
        if ($data['status'] == -1) {
            
            if (M('purchase_apply')->where($map)->save($data) === false) {
                $rst['success'] = false;
                $rst['errorMsg'] = '采购申请审核失败！';
            } else {
                $rst['success'] = true;
            }
            
            $this->ajaxReturn($rst);
        }
        
        $fittings = array();
        $fittingIds = array();
        
        foreach ($post['fittings'] as $key => $value) {
            $info = explode('_', $value);
        
            if ($info[1] <= 0) {
                continue;
            }
        
            $param = array();
            $param['purchase_apply_id'] = $item['id'];
            $param['organization_id'] = $post['proposer_org'];
            $param['fitting_id'] = $info[0];
            $param['amount'] = $info[1];
        
            $fittings[] = $param;
            $fittingIds[] = $info[0];
        }
        
        /** 配件数据验证 */
        
        if (count($fittings) == 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '采购申请配件不能为空！';
            $this->ajaxReturn($rst);
        }
        
        if (count($fittingIds) != count(array_unique($fittingIds))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '配件存在重复记录,请检查！';
            $this->ajaxReturn($rst);
        }
        
        $fittinglists = M('purchase_apply_fitting')->where(array('purchase_apply_id' => $item['id']))
                        ->field('purchase_apply_id, organization_id, fitting_id, amount')->select();
        M()->startTrans();
        
        if (M('purchase_apply')->where($map)->save($data) === false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '采购申请审核失败！';
            $this->ajaxReturn($rst);
        }
        
        if ($fittinglists != $fittings) {
        
            if (M('purchase_apply_fitting')->where(array('purchase_apply_id' => $item['id']))->delete() === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '采购申请审核失败！';
                $this->ajaxReturn($rst);
            }
        
            if (M('purchase_apply_fitting')->addAll($fittings) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '采购申请审核失败！';
                $this->ajaxReturn($rst);
            }
        }
        
        M()->commit();
        
        $rst['success'] = true;
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
                        ->field('f.id, group_concat(pf.phone_id) as phone_id, group_concat(p.alias) as phone, f.title as fitting, f.number as fitting_number')
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
                'phone' => $fitting['phone'],
                'fitting' => $fitting['fitting'],
                'fitting_number' => $fitting['fitting_number'],
                'fitting_id' => $fitting['id'],
                'amount' => $value[2]
            );
        }
        
        $rst['success'] = true;
        $rst['data'] = $params;
        $this->ajaxReturn($rst);
    }
    
    /**
     * 采购申请配件列表
     */
    public function lists() 
    {
        $id = I('get.id/d');
        $include_warehouse = I('get.include_warehouse/d');
        
        if ($id <= 0) {
            $this->ajaxReturn(array());
        }
        
        $map = array('purchase_apply_id' => $id);
        
        $join = 'paf left join fitting f on f.id = paf.fitting_id
            left join phone_fitting pf on f.id = pf.fitting_id
            left join phone p on p.id = pf.phone_id';
        
        $field = 'paf.id, paf.fitting_id, paf.amount, group_concat(p.alias) as phone, f.title as fitting, f.number as fitting_number';
        
        if ($include_warehouse) {
            $join .= ' left join warehouse w on w.fitting_id = paf.fitting_id and w.organization_id = paf.organization_id';
            $field .= ' , ifnull(w.amount, 0) as warehouse_amount';
        }
        
        $list = M('purchase_apply_fitting')->join($join)
                ->where($map)
                ->group('paf.id')
                ->field($field)
                ->select();
        $this->ajaxReturn($list);
    }
    
    /**
     * 导出汇总采购申请
     */
    public function export()
    {
        $exorders = array();
        $exorders[0] = array( '配件大类', '物料编码', '物料名称');
        
        $map = array();
        $map = array('pa.status' => array('eq', 1));
        $post = I('post.');
        $address = array();
        
        $orgs = M('organization')->where(array('type' => 1))->order('id asc')->getField('id, alias');
        
        foreach ($orgs as $id => $name) {
            $exorders[0][] = $name;
            $address[$id] = 0;
        }
        $exorders[0][] = '总数量';
        
        if (!empty($orgs)) {
            $map['pa.proposer_org'] = array('in', array_keys($orgs));
        }

        if (!empty($post['starttime']) && empty($post['endtime'])) {
            $map['pa.create_time'] = array('EGT', strtotime($post['starttime']));
        } else if (!empty($post['endtime'] && empty($post['starttime']))) {
            $map['pa.create_time'] = array('ELT', strtotime($post['endtime'] . '23:59:59'));
        } else if ($post['starttime'] && $post['endtime']) {
            $map['pa.create_time'] = array(array('egt', strtotime($post['starttime'])), array('elt', strtotime($post['endtime'] . '23:59:59')), 'and');
        }
        
        if (!empty($post['proposer_org']) && $post['proposer_org'] != 'all') {
            $map['pa.proposer_org '] = $post['proposer_org'];
        }
        
        if (!empty($post['keyword'])) {
            $where = array();
            $where['f.number'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['f.title'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['pa.remark'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        
        $join = 'paf left join purchase_apply pa on paf.purchase_apply_id = pa.id 
                left join fitting f on f.id = paf.fitting_id
                left join fitting_category fc on fc.id = f.category_id';

        $list = M('purchase_apply_fitting')->join($join)->where($map)
                ->field('fc.name as category_name, pa.proposer_org, paf.fitting_id, f.number, f.title, paf.amount')
                ->select();
        
        foreach ($list as $item) {
            
            if (!isset($exorders[$item['fitting_id']])) {
                $row['配件大类'] = $item['category_name'];
                $row['物料编码'] = $item['number'];
                $row['物料名称'] = $item['title'];
                $exorders[$item['fitting_id']] = $row + $address;
            }
            
            $exorders[$item['fitting_id']][$item['proposer_org']] += $item['amount'];
            $exorders[$item['fitting_id']]['total'] += $item['amount'];
        }
        
        $filename = date('Y-m-d'). '分城市订料汇总表';
        $this->exportData($filename, $exorders);
    }
}