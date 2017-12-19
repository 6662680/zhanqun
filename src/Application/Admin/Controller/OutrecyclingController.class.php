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

class OutrecyclingController extends BaseController
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

        if (!empty($post['starttime']) && empty($post['endtime'])) {
            $map['out_recycling.create_time'] = array('EGT', strtotime($post['starttime']));
        } else if (!empty($post['endtime'] && empty($post['starttime']))) {
            $map['out_recycling.create_time'] = array('ELT', strtotime($post['endtime'] . '23:59:59'));
        } else if ($post['starttime'] && $post['endtime']) {
            $map['out_recycling.create_time'] = array(array('egt', strtotime($post['starttime'])), array('elt', strtotime($post['endtime'] . '23:59:59')), 'and');
        }
        
        if (!empty($post['proposer_org']) && $post['proposer_org'] != 'all') {
            $map['out_recycling.proposer_org '] = $post['proposer_org'];
        }

        if (isset($post['status']) && $post['status'] == 0 && $post['status'] != 'all') {
            $map['out_recycling.status'] = $post['status'];
        }

        $count = M('out_recycling')->where($map)->count();
        $rst['total'] = $count;

        $join = 'left join organization o on out_recycling.proposer_org_id=o.id
                left join user u on out_recycling.proposer_id=u.id
                left join recycling on out_recycling.recycling_id=recycling.id';


        $list = M('out_recycling')->join($join)->where($map)->limit($this->page())
            ->field('out_recycling.*, u.username as proposer_name, o.name as proposer_org_name, recycling.title')
            ->order('id desc')->select();

        $in = '';

        foreach ($list as $value) {
             $in .= $value['id'] . ',';
        }

        $fitting = M('out_recycling_fitting')->where(array('out_recycling_id' => array('in', substr($in, 0, -1) )))->select();

        foreach ($list as &$value) {

            foreach ($fitting as $v) {

                if ($value['id'] == $v['out_recycling_id']) {
                    $value['fittings'][] = $v;
                }
            }
        }

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
        $org_id = I('get.org_id');
        $sql = "select f.id, concat(f.title, '(', f.number, ')') as name from phone_fitting pf
                left join fitting f on pf.fitting_id=f.id
                left join stock s on pf.fitting_id=s.fitting_id
                where pf.phone_id={$phoneId} and f.id > 0 and s.organization_id={$org_id}
                GROUP BY pf.fitting_id ";
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
    public function recycling()
    {
        $map = array();
        $map['status'] = 1;
        $list = M('recycling')->where($map)->field('id, title')->select();
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
        $data['proposer_org_id'] = $post['proposer_org'];
        $data['proposer_id'] = session('userId');
        $data['create_time'] = time();
        $data['remark'] = $post['remark'];
        $data['batch'] = date("YmdHis",time()). session('userId');
        $data['recycling_id'] = $post['recycling'];

        if (empty($post['proposer_org'])){
            $rst['success'] = false;
            $rst['errorMsg'] = '仓库不许为空！';
            $this->ajaxReturn($rst);
        }


        if (empty($data['recycling_id'])){
            $rst['success'] = false;
            $rst['errorMsg'] = '收购商不许为空！';
            $this->ajaxReturn($rst);
        }

        $fittings = array();
        $fittingIds = array();

        M()->startTrans();

        if (!$out_recycling_id = M('out_recycling')->add($data)) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败，请检查数据是否有误';
            $this->ajaxReturn($rst);
        }

        if (count($post['fittings']) > 0) {

            foreach ($post['fittings'] as $key => $value) {
                $info = explode('_,', $value);

                if ($info[4] <= 0) {
                    continue;
                }

                $item = array();
                $item['out_recycling_id'] = $out_recycling_id;
                $item['organization_id'] = $data['proposer_org_id'];
                $item['phone'] = $info[0];
                $item['phone_id'] = $info[1];
                $item['fitting'] = $info[2];
                $item['fitting_id'] = $info[3];
                $item['amount'] = $info[4];
                $item['price'] = $info[5];


                /** 配件数量仓库验证 */
                /*这里*/

                //$stocks_id = $model->countStock($item['fitting_id'], $data['proposer_org_id'], $item['amount']);

                $map['fitting_id'] = $item['fitting_id'];
                $map['organization_id'] = $data['proposer_org_id'];
                $map['status'] = 1;
                $stocks_id = M('stock')->where($map)->field('id')->limit($item['amount'])->select();

                if (count($stocks_id) < $item['amount']) {
                    M()->rollback();
                    $rst['success'] = false;
                    $rst['errorMsg'] = '配件数量不够,请核对库存后再提交';
                    $this->ajaxReturn($rst);
                }

                $fittings[] = $item;
                $fittingIds[] = $info[3];
            }
        } else {
            M()->rollback();
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

        if (!M('out_recycling_fitting')->addAll($fittings)) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败，请检查数据是否有误';
            $this->ajaxReturn($rst);
        } else {
            M()->commit();
            $rst['success'] = true;
            $this->ajaxReturn($rst);
        }
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
        $map['id'] = I('get.id');
        $save_id = $map['id'];
        $post = I('post.');

        $item = D('out_recycling')->where($map)->find();

        if (empty($post['recycling'])){
            $rst['success'] = false;
            $rst['errorMsg'] = '收购商不许为空！';
            $this->ajaxReturn($rst);
        }

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


        $data = array();
        $data['proposer_org_id'] = $post['proposer_org'];
        $data['proposer_id'] = session('userId');
        $data['update_time'] = time();
        $data['remark'] = $post['remark'];

        M()->startTrans();
        $fittings = array();

        if (count($post['fittings']) > 0) {
            
            foreach ($post['fittings'] as $key => $value) {
                $info = explode('_,', $value);
                
                if ($info[4] <= 0) {
                    continue;
                }
                
                $item = array();
                $item['out_recycling_id'] = $map['id'];
                $item['phone'] = $info[0];
                $item['phone_id'] = $info[1];
                $item['fitting'] = $info[2];
                $item['fitting_id'] = $info[3];
                $item['amount'] = $info[4];
                $item['price'] = $info[5];

                /** 配件数量仓库验证 */

                $map1['fitting_id'] = $item['fitting_id'];
                $map1['organization_id'] = $data['proposer_org_id'];
                $map1['status'] = 1;
                $stocks_id = M('stock')->where($map1)->field('id')->limit($item['amount'])->select();

                if (count($stocks_id) < $item['amount']) {
                    M()->rollback();
                    $rst['success'] = false;
                    $rst['errorMsg'] = '配件数量不够,请核对库存后再提交';
                    $this->ajaxReturn($rst);
                }

                $fittings[] = $item;
            }
        }

        if (M('out_recycling')->where($map)->save($data) === 'false') {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '更新失败1！';
            $this->ajaxReturn($rst);
        }

        if (M('out_recycling_fitting')->where(array('out_recycling_id' => $save_id))->delete() === 'false') {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '更新失败2！';
            $this->ajaxReturn($rst);
        }

        if (!M('out_recycling_fitting')->addAll($fittings)) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '更新失败3！';
            $this->ajaxReturn($rst);
        }

        if (!$fittings) {
            $rst['success'] = false;
            $rst['errorMsg'] = '配件数量不对，请仓库确认！';
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
        $item = D('out_recycling')->where($map)->find();

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

        if (D('out_recycling')->where($map)->limit(1)->delete() !== false) {
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
        $data = array();
        $map['id'] = $post['id'];
        $item = M('out_recycling')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($post['flag'] == 1) {
            $data['audit'] = '采购:'. session('userInfo')['realname'];
        }

        if ($post['flag'] == 2) {
            $data['audit'] = $item['audit'].',供应:'. session('userInfo')['realname'];
        }

        if ($post['flag'] == 3) {
            $data['audit'] = $item['audit'].',财务:'. session('userInfo')['realname'];
        }

        if ($post['flag'] == 4) {
            $data['audit'] = $item['audit'].',出库:'. session('userInfo')['realname'];
            $out_recycling = M('out_recycling')->where(array('id' => $item['id']))->find();
            $fittings = M('out_recycling_fitting')->where(array('out_recycling_id' => $item['id']))->select();

            M()->startTrans();

            foreach ($fittings as $value) {
                $number = M('stock')->where(array('id' => array('in', explode(',', $value['stock_id']))))->field('number, id')->select();

                /** 配件数量仓库验证 */

                $map1['fitting_id'] = $value['fitting_id'];
                $map1['organization_id'] = $out_recycling['proposer_org_id'];
                $map1['status'] = 1;

                $stocks_ids = M('stock')->where($map1)->field('id')->limit($value['amount'])->select();

                if (count($stocks_ids) < $value['amount']) {
                    M()->rollback();
                    $rst['success'] = false;
                    $rst['errorMsg'] = '配件数量不够,请核对库存后再提交';
                    $this->ajaxReturn($rst);
                }

                $stocks_id = '';

                foreach ($stocks_ids as $v) {
                    $stocks_id .= $v['id'];
                }

                if (M('stock')->where(array('id' => array('in', $stocks_id)))->save(array('organization_id' => 0)) == false) {
                    M()->rollback();
                    $rst['success'] = false;
                    $rst['errorMsg'] = '配件数量不够,请核对库存后再提交';
                    $this->ajaxReturn($rst);
                }

                $fitting = array();

                foreach ($number as $k => $v) {
                    $fitting[][$v['id']] = $v['number'];
                }

                $param = array(
                    'type' => 4,
                    'batch' => trim($item['batch']),
                    'organization_id' => $item['proposer_org_id'],
                    'fitting_id' => $value['fitting_id'],
                    'user_id' => session('userId'),
                    'provider_id' => 0,
                    'engineer_id' => 0,
                    'inout' => 2,
                    'amount' => $value['amount'],
                    'price' => $value['price'],
                    'fittings' => json_encode($fitting),
                    'time' => time()
                );


                if (M('inout')->add($param) === false) {
                    M()->rollback();
                    $rst['success'] = false;
                    $rst['errorMsg'] = '添加日志失败！';
                    $this->ajaxReturn($rst);
                }

                $map2['fitting_id'] = $value['fitting_id'];
                $map2['organization_id'] = $item['proposer_org_id'];
                $map2['status'] = 1;

                if (M('warehouse')->where($map2)->setDec('amount', $value['amount']) === false) {
                    M()->rollback();
                    $rst['success'] = false;
                    $rst['errorMsg'] = '修改库存失败！';
                    $this->ajaxReturn($rst);
                }

            }
        }

        if ($post['flag'] == -1) {
            $data['audit'] = $item['audit'].',不通过:'. session('userInfo')['realname'];

        }

        $data['status'] = $post['flag'];

        if (M('out_recycling')->where($map)->limit(1)->save($data) !== false) {

            M()->commit();
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '操作失败！';
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

    /**
     * 导出
     *
     * @return void
     */
    public function export()
    {

        $params = array();
        $params[] = array('批次号', '物料' , '申请人', '申请组织', '回收商', '审核人', '创建时间' , '状态' ,'备注');

        $map = array();
        $rst = array();
        $post = I('post.');

        $orgs = session('organizations');

        if (!empty($post['starttime']) && empty($post['endtime'])) {
            $map['out_recycling.create_time'] = array('EGT', strtotime($post['starttime']));
        } else if (!empty($post['endtime'] && empty($post['starttime']))) {
            $map['out_recycling.create_time'] = array('ELT', strtotime($post['endtime'] . '23:59:59'));
        } else if ($post['starttime'] && $post['endtime']) {
            $map['out_recycling.create_time'] = array(array('egt', strtotime($post['starttime'])), array('elt', strtotime($post['endtime'] . '23:59:59')), 'and');
        }

        if (!empty($post['proposer_org']) && $post['proposer_org'] != 'all') {
            $map['out_recycling.proposer_org_id '] = $post['proposer_org'];
        }

        if (isset($post['status']) && $post['status'] == 0 && $post['status'] != 'all') {
            $map['out_recycling.status'] = $post['status'];
        }

        $count = M('out_recycling')->where($map)->count();
        $rst['total'] = $count;

        $join = 'left join organization o on out_recycling.proposer_org_id=o.id
                left join user u on out_recycling.proposer_id=u.id
                left join recycling on out_recycling.recycling_id=recycling.id';


        $list = M('out_recycling')->join($join)->where($map)->limit($this->page())
            ->field('out_recycling.*, u.username as proposer_name, o.name as proposer_org_name, recycling.title')
            ->order('id desc')->select();

        $in = '';

        foreach ($list as $value) {
            $in .= $value['id'] . ',';
        }

        $fitting = M('out_recycling_fitting')->where(array('out_recycling_id' => array('in', substr($in, 0, -1) )))->select();

        foreach ($list as &$value) {

            if ($value['status'] == -1) {
                $value['status'] = '取消';
            }

            if ($value['status'] == 0) {
                $value['status'] = '待审核';
            }

            if ($value['status'] == 1) {
                $value['status'] = '审核中';
            }

            if ($value['status'] == 2) {
                $value['status'] = '审核中';
            }

            if ($value['status'] == 3) {
                $value['status'] = '已出库';
            }


            $str = '';
            foreach ($fitting as $v) {

                if ($value['id'] == $v['out_recycling_id']) {
                    $str .= $v['phone'].$v['fitting']. 'x'. $v['amount'] .',';
                }
            }

            $params[] = array(
                $value['batch'], $str, $value['proposer_name'], $value['proposer_org_name'], $value['title'], $value['audit'],
                date('Y-m-d H:i:s', $value['create_time']),
                $value['status']
            );
        }

        $this->exportData('采购出库-'.date('Y-m-h-H-i-s'), $params);

    }
}