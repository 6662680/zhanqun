<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 废件 Dates: 2016-09-06
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class WasteController extends BaseController
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

        if (!empty($post['phone_id'])) {
            $map['pw.phone_id'] = $post['phone_id'];
        }

        if (!empty($post['keyword'])) {
            $where = array();
            $where['w.number']  = array('like', '%' . $post['keyword'] . '%');
            $where['w.title']  = array('like', '%' . $post['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $count = M('waste')->join('w left join phone_waste pw on w.id=pw.waste_id')->where($map)->count('distinct(w.id)');
        $rst['total'] = $count;

        $list = M('waste')
            ->field('w.*, group_concat(p.alias) as phone, group_concat(p.id) as phone_id')
            ->join('w left join phone_waste pw on w.id=pw.waste_id left join phone p on pw.phone_id=p.id')
            ->where($map)->limit($this->page())
            ->group('w.id')
            ->order('w.id desc')
            ->select();
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
        $list = M('phone')->where()->field('id, alias')->order('alias asc')->select();
        array_unshift($list,array('alias'=>'全部','id'=>false));
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
        $data = I('post.');
        $data['number'] = trim($data['number']);
        
        if (M('waste')->where(array('number' => trim($data['number'])))->find()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '物料编号已经存在！';
            $this->ajaxReturn($rst);
        }
        
        M()->startTrans();
        
        $waste_id = M('waste')->add($data);
        
        if ($waste_id == false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
            $this->ajaxReturn($rst);
        }
        
        $phone_ids = explode(',', $data['phone_ids']);
        
        foreach ($phone_ids as $phone_id) {
            $params[] = array('phone_id' => $phone_id, 'waste_id' => $waste_id);
        }
        
        if ($params && M('phone_waste')->addAll($params) === false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败';
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
    public function save()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $item = D('waste')->where($map)->find();

        if ($item) {
            
            if (M('waste')->where(array('number' => $data['number'], 'id' => array('neq', $map['id'])))->find()) {
                $rst['success'] = false;
                $rst['errorMsg'] = '物料编号已经存在！';
                $this->ajaxReturn($rst);
            }
            
            M()->startTrans();

            if (D('waste')->where($map)->save($data) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新失败！';
                $this->ajaxReturn($rst);
            }
            
            if (M('phone_waste')->where(array('waste_id' => $item['id']))->delete() === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新失败！';
                $this->ajaxReturn($rst);
            }
            
            $phone_ids = explode(',', $data['phone_ids']);
            
            foreach ($phone_ids as $phone_id) {
                $params[] = array('phone_id' => $phone_id, 'waste_id' => $item['id']);
            }
            
            if ($params && M('phone_waste')->addAll($params) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '更新失败';
                $this->ajaxReturn($rst);
            }
            
            M()->commit();
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
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
        $item = D('waste')->where($map)->find();

        if ($item) {
            
            M()->startTrans();
            
            if (D('phone_waste')->where(array('waste_id' => $item['id']))->delete() === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '删除失败！';
                $this->ajaxReturn($rst);
            }
            
            if (D('waste')->where($map)->limit(1)->delete() === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '删除失败！';
                $this->ajaxReturn($rst);
            }
            
            M()->commit();
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 加入的组织
     *
     * @return void
     */
    public function joined()
    {
        $id = I('get.id', 0);
        $sql = "select p.id, p.alias from phone_waste pw 
            left join phone p on pw.phone_id=p.id 
            where pw.waste_id = {$id}";
        $list = M()->query($sql);
        $this->ajaxReturn($list);
    }

    /**
     * 未加入的组织
     *
     * @return void
     */
    public function notin()
    {
        $id = I('get.id', 0);
        $sql = "select id, alias from phone where id not in (select phone_id from phone_waste where waste_id = {$id})";
        $list = M()->query($sql);
        $this->ajaxReturn($list);
    }

    /**
     * 加入组织
     *
     * @return void
     */
    public function join()
    {
        $rst = array();
        $data = array();
        $data['phone_id'] = I('post.phoneId');
        $data['waste_id'] = I('post.wasteId');

        if (D('phone_waste')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 退出组织
     *
     * @return void
     */
    public function exits()
    {
        $rst = array();
        $map = array();
        $map['phone_id'] = I('post.phoneId');
        $map['waste_id'] = I('post.wasteId');

        if (D('phone_waste')->where($map)->limit(1)->delete() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
        }
        $this->ajaxReturn($rst);
    }


    /**
     * 废料库存页面
     * @author liyang
     * @return void
     */
    public function stock()
    {

        $this->display();
    }

    public function stockRows()
    {
        $organizations = session('organizations');
        $post = I('post.');

        if ($organizations) {
            $map['organization_id '] = array('in', implode(',', array_keys($organizations)));
        }

        if ($post['id']) {
            $map['organization_id'] = array('eq',$post['id']);
        }

        if ($post['phone']) {
            $map['p.id'] = $post['phone'];
        }

        $rst['rows'] =  M('waste_warehouse')->join('ww left join `waste` w on ww.waste_id = w.id')
                        ->join('left join organization o on o.id = ww.organization_id')
                        ->join('left join `phone_waste` pw on ww.waste_id = pw.waste_id')
                        ->join('left join `phone` p on pw.phone_id = p.id')
                        ->where($map)
                        ->field('ww.*, w.title, w.number, o.alias as org,group_concat(distinct(p.alias)) as phone')
                        ->group('ww.waste_id,organization_id')
                        ->order('o.id asc')
                        ->limit($this->page())
                        ->select();

        $rst['total'] = count(M('waste_warehouse')->join('ww left join `waste` w on ww.waste_id = w.id')
                        ->join('left join organization o on o.id = ww.organization_id')
                        ->join('left join `phone_waste` pw on ww.waste_id = pw.waste_id')
                        ->join('left join `phone` p on pw.phone_id = p.id')
                        ->where($map)
                        ->field('concat(w.title, "(", w.number, ")") as title,group_concat(distinct(p.alias)) as phone')
                        ->group('ww.waste_id,organization_id')
                        ->select());

        $this->ajaxReturn($rst);
    }
    
    /**
     * 导出废料库存
     */
    public function exportWasteStocks()
    {
        $post = I('post.');
        
        if ($post['organization_id']) {
            $map['organization_id'] = array('eq',$post['id']);
        }
        
        if ($post['phone_id']) {
            $map['p.id'] = $post['phone_id'];
        }
        
        $list =  M('waste_warehouse')->join('ww left join `waste` w on ww.waste_id = w.id')
                ->join('left join organization o on o.id = ww.organization_id')
                ->join('left join `phone_waste` pw on ww.waste_id = pw.waste_id')
                ->join('left join `phone` p on pw.phone_id = p.id')
                ->where($map)
                ->field('ww.*, w.title, w.number, o.alias as org,group_concat(distinct(p.alias)) as phone')
                ->group('ww.waste_id,organization_id')
                ->order('o.id asc')
                ->select();
        
        $params = array();
        $params[] = array('组织(地区)', '机型', '废件名称', '废件编号', '数量');
        
        foreach ($list as $v) {
            $params[] = array($v['org'], $v['phone'], $v['title'], $v['number'], $v['amount']);
        }
        
        $this->exportData('废料库存数据'.date('Y_m_d_H_i_s'), $params);
    }

    /**
     * 获取故障类型
     * @author liyang
     * @return void
     */
    public function getType(){
        $rst = M('waste')->select();
        $this->ajaxReturn($rst);
    }

    /**
     * 废料详情列表
     * @author liyang
     * @return void
     */
    public function detail()
    {
        $data = I('get.');
        
        if (!$data['organization_id'] || !$data['waste_id']) {
            $this->ajaxReturn(array());
        }
        
        $map = array();
        $map['waste_id'] = $data['waste_id'];
        $map['organization_id'] = $data['organization_id'];
        
        $rst['rows']  = M('waste_stock')->join('ws left join `waste` w on ws.waste_id = w.id')
                        ->join('left join organization o on o.id = ws.organization_id')
                        ->join('left join `order` on order.id = ws.order_id ')
                        ->field('ws.*, concat(w.title, "(", w.number, ")") as title, o.alias as org, order.number as order_number ')
                        ->where($map)->limit($this->page())->select();

        $rst['total'] = count(M('waste_stock')->join('ws left join `waste` w on ws.waste_id = w.id')
                        ->join('left join organization o on o.id = ws.organization_id')
                        ->join('left join `order` on order.id = ws.order_id ')
                        ->field('ws.*, concat(w.title, "(", w.number, ")") as title, o.alias as org, order.number as order_number ')
                        ->where($map)->select());
        
        $this->ajaxReturn($rst);
    }

    /**
     * 所属公司名称
     * @author liyang
     * @return void
     */
    public function belong()
    {
        $list = session('organizations');
        array_unshift($list,array('alias'=>'全部','id'=>false));
        $this->ajaxReturn($list);
    }

    /**
     * 全部公司名称
     * @author liyang
     * @return void
     */
    public function allOrganization()
    {
        $list = M('organization')->field('id,alias')->select();
        $this->ajaxReturn($list);
    }

    /**
     * 调拨记录页面
     * @author liyang
     * @return void
     */
    public function allotApply()
    {
        $this->display();
    }

    /**
     * 调拨记录
     * @author liyang
     * @return void
     */
    public function applyRows()
    {
        $organizations = array();
        $post = I('post.');

        /*组织*/
        if ($post['id']){
            $map['proposer_org | auditor_org'] = array('eq' , $post['id']);
        } else {

            foreach($_SESSION['organizations'] as $value){
                $organizations[] = $value['id'];
            }

            $map['proposer_org | auditor_org'] = array('in' , implode(',',$organizations));
        }

        /*工程师*/
        if ($post['engineer']) {
            $map['proposer | auditor'] = array('eq' , $post['engineer']);
        }

        /*时间*/
        if (!empty($post['time_start'] && $post['time_end'])) {
            $map['wa.time'] = array('egt' , strtotime($post['create_time_start']));
        }

        if (!empty($post['time_end']) && empty($post['time_start'])) {
            $map['wa.time '] = array('elt' , strtotime($post['create_time_end'])+24*60*60-1);
        }

        if ($post['time_start'] && $post['time_end']) {
            $map['wa.time '] = array(array('gt' , strtotime($post['time_start'])) , array('lt' , strtotime($post['time_end']) +24*60*60-1),'and');
        }

        /*状态*/
        if (!empty($post['status'])) {

            if ($post['status'] == 4) {
                $map['wa.status'] = 0;
            } else {
                $map['wa.status'] = $post['status'];
            }
        }

        /*类型*/
        if (!empty($post['type'])) {
            $map['wa.type'] = $post['type'];
        }

        $rst = D('Waste')->getList($map, $this->page());

        $this->ajaxReturn($rst);
    }

    /**
     * 废料申请列表详情
     * @author liyang
     * @return void
     */
    public function applyDetail()
    {
        $rst['rows'] = json_decode(M('waste_allot')->where(['id'=>I('get.id', 0)])->getField('wastes'),true);

        foreach($rst['rows'] as &$value) {
            $value['phone'] = M('phone')->where(array('id' => $value['id']))->getField('alias');
        }

        $rst['total'] = count($rst['rows']);

        $this->ajaxReturn($rst);
    }

    /**
     * 废料调拨编辑
     * @author liyang
     * @return void
     */
    public function applyEdit()
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

        $model = M('waste_allot');
        $model->find($data['id']);

        if ($model->status != 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = "已经审核的状态无法编辑！";
            $this->ajaxReturn($rst);
        }

        $wastes = array();

        foreach($data['fittings'] as $key => $value) {
            $value  = explode('_', $value);
            
            if ($value[4] <= 0) {
                continue;
            }
            
            $wastes[$key]['phone'] = $value[0];
            $wastes[$key]['phone_id'] = $value[1];
            $wastes[$key]['name'] = $value[2];
            $wastes[$key]['waste_id'] = $value[3];
            $wastes[$key]['amount'] = $value[4];
        }

        $data['wastes'] = json_encode(array_values($wastes));

        $model->wastes = $data['wastes'];
        $model->remark = $data['remark'];
        
        if ($model->save() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = "更新失败！";
        }

        $this->ajaxReturn($rst);
    }


    /**
     * 废料退还页面
     * @author liyang
     * @return void
     */
    public function refund()
    {
        $this->display();
    }

    /**
     * 废料退还导出
     * @author liyang
     * @return void
     */
    public function refundExport()
    {
        $columns = array(
            '废料'    =>  'wastes',
            '订单号'  =>  'number',
            '手机'    =>  'phone_name',
            '颜色'    =>  'color',
            '申请人'  => 'engineer' ,
            '组织'    =>  'organization',
            '审核人'  => 'user',
            '时间'    =>  'time' ,
            '状态'    => 'status' ,
            '备注'    =>  'remark' ,
        );

        $exorders = array_keys($columns);

        $map = array();
        $rst = array();
        $post = I('post.');

        $orgs = session('organizations');

        if (!empty($post['search-starttime'])) {
            $map['a.time'] = array('EGT', strtotime($post['search-starttime']));
        }

        if (!empty($post['search-endtime'])) {
            $map['a.time '] = array('ELT', strtotime($post['search-endtime']));
        }

        if ($post['status'] == 0) {
            $map['a.status'] = 0;
        }

        if (!empty($post['status']) && $post['status'] != 'all') {
            $map['a.status'] = $post['status'];
        }

        $join = 'a left join organization o on a.organization_id=o.id
                left join `order` r on a.order_id=r.id
                left join user u on a.user_id=u.id
                left join engineer e on a.engineer_id=e.id';


        $list = M('waste_refund')->join($join)->where($map)
            ->field('a.id,a.order_id, r.phone_name, r.color, r.number, e.name as engineer, u.username as user, o.name as organization, a.status, a.time, a.wastes, a.remark')
            ->order('id desc')->select();

        $status = array(
            '-1' => '审核不通过',
            '0'  => '待审核',
            '1'  => '审核通过',
            '2'  => '无废料通过',
        );

        foreach ($list as  $key => &$value) {
            $value['status'] = $status[$value['status']];
            $value['time'] = date('Y-m-d H:i:s',$value['time']);
            $value['wastes'] = json_decode($value['wastes'], true);
            $phone = '';

            foreach ($value['wastes'] as $v) {
                $phone .= $v['phone'] . $v['name'] . $v['amount'] . '个 ';
            }
            $value['wastes'] = $phone;

            foreach ($columns as $k => $v) {
                $row[$key][$k] = $value[$v];
            }
        }
        array_unshift($row,$exorders);

        $this->exportData('废料退还' . date('Y-m-d H:i:s'), $row);
    }


    /**
     * 废料申请审核与发货
     * @author liyang
     * @return void
     */
    public function applyAudit()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('post.id/d');

        $item = M('waste_allot')->where($map)->find();

        $orgs = session('organizations');
        $status = I('post.status');
        /** 权限判断 属于目标仓库方可审核 */
        if (!in_array($item['auditor_org'], array_keys($orgs)) && $status != 3) {
            $rst['success'] = false;
            $rst['errorMsg'] = '没有权限审核此申请！请联系目标仓库管理员审核！';
            $this->ajaxReturn($rst);
        }

        if (!in_array($item['proposer_org'], array_keys($orgs)) && $status == 3) {
            $rst['success'] = false;
            $rst['errorMsg'] = '没有权限审核此申请！请联系目标仓库管理员审核！';
            $this->ajaxReturn($rst);
        }

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '调拨记录不存在';
            $this->ajaxReturn($rst);
        }

        if ((($status - 1) != $item['status']) && $status != -1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '调拨记录状态已过期，请刷新页面！';
            $this->ajaxReturn($rst);
        }

        //判断是否审核通过与取消
        if ($status == 2) { //发货
            $rst = D('Waste')->inout($item);
        } else if ($status == 3) { //收货
            $rst = D('Waste')->accept($item);
        } else { //审核
            $data = array(
                'status' => $status,
                'auditor'=> session('userId')
            );
            
            if (M('waste_allot')->where($map)->save($data)) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '调拨记录审核失败(更新数据失败)！';
            }
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 废料调拨回退
     *
     */
    public function allotRollback()
    {
        /** 状态：-1 取消 (审核不通过)  0 待审核 1 审核通过 2 已发货 3 已收货 */
        $post = I('post.');
        $map = array();
        $rst = array();
        $map['id'] = $post['id'];
        $item = D('waste_allot')->where($map)->find();

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

        if (D('waste_allot')->where($map)->limit(1)->save($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '操作失败！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 出入库记录页面
     * @author liyang
     * @return void
     */
    public function inout()
    {
        $this->display();
    }

    /**
     * 出入库记录列表
     * @author liyang
     * @return void
     */
    public function inoutRow()
    {
        $organizations = session('organizations');
        $map = array();
        $post = I('post.');

        if ($organizations) {
            $map['waste_inout.organization_id '] = array('in', implode(',', array_keys($organizations)));
        }

        if ($post['id']) {
            $map['waste_inout.organization_id'] = $post['id'];
        }

        if ($post['type']) {
            $map['waste_inout.type'] = $post['type'];
        }

        if ($post['inout']) {
            $map['waste_inout.inout'] = $post['inout'];
        }

        if ($post['user']) {
            $map['waste_inout.user_id'] = $post['user'];
        }

        if (!empty($post['keyword'])) {
            $like['w.number'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['w.title'] = array('like',  '%' . trim($post['keyword']) . '%');
            $like['o.number'] = array('like',  '%' . trim($post['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        if (!empty($post['starttime'] && $post['endtime'])) {
            $map['waste_inout.time'] = array('egt', strtotime($post['starttime']));
        }

        if (!empty($post['endtime']) && empty($post['starttime'])) {
            $map['waste_inout.time '] = array('elt', strtotime($post['endtime'])+24*60*60-1);
        }

        if ($post['starttime'] && $post['endtime']) {
            $map['waste_inout.time '] = array(array('gt',strtotime($post['starttime'])),array('lt',strtotime($post['endtime']) +24*60*60-1),'and');
        }

        $rst['rows']  = M('waste_inout')
                        ->join('left join organization org ON waste_inout.organization_id = org.id')
                        ->join('left join waste w ON waste_inout.waste_id = w.id')
                        ->join('left join user u ON waste_inout.user_id = u.id')
                        ->join('left join engineer e ON waste_inout.engineer_id = e.id')
                        ->join('left join `order` o on o.id = waste_inout.order_id')
                        ->where($map)
                        ->field('waste_inout.*, o.phone_name , concat(w.title, "(", w.number, ")") as title, u.realname, e.name, org.alias as org, o.number')
                        ->order('waste_inout.id desc')
                        ->limit($this->page())
                        ->select();

        $rst['total'] = count(M('waste_inout')
                        ->join('left join organization org ON waste_inout.organization_id = org.id')
                        ->join('left join waste w ON waste_inout.waste_id = w.id')
                        ->join('left join user u ON waste_inout.user_id = u.id')
                        ->join('left join engineer e ON waste_inout.engineer_id = e.id')
                        ->join('left join `order` o on o.id = waste_inout.order_id')
                        ->where($map)
                        ->field('waste_inout.*, concat(w.title, "(", w.number, ")") as title, u.realname, e.name, org.alias as org, o.number')
                        ->select());

        $this->ajaxReturn($rst);
    }

    /**
     * 新增申请
     * @author liyang
     * @return void
     */
    public function apply()
    {
        $data = I('post.');
        $rst = array();
        
        if ($data['proposer_org'] <= 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = "请选择申请地区仓库！";
            $this->ajaxReturn($rst);
        }
        
        if ($data['auditor_org'] <= 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = "请选择目标地区仓库！";
            $this->ajaxReturn($rst);
        }
        
        if ($data['auditor_org'] == $data['proposer_org']) {
            $rst['success'] = false;
            $rst['errorMsg'] = "申请仓库和目标仓库不能是同一个！";
            $this->ajaxReturn($rst);
        }
        
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
        
        $wastes = array();
        
        foreach($data['fittings'] as $key => $value) {
            $value  = explode('_', $value);
            $wastes[$key]['phone'] = $value[0];
            $wastes[$key]['phone_id'] = $value[1];
            $wastes[$key]['name'] = $value[2];
            $wastes[$key]['waste_id'] = $value[3];
            $wastes[$key]['amount'] = $value[4];
        }

        $data['status'] = 0;
        $data['proposer'] = session('userId');
        $data['time'] = time();
        $data['wastes'] = json_encode($wastes);

        if (M('waste_allot')->add($data)) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '申请失败（写入数据库失败）！';
        }
        
        $this->ajaxReturn($rst);
    }

    /**
     * 废料退还列表
     * @author liyang
     * @return void
     */
    public function refundApply()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        $orgs = session('organizations');

        if (empty($orgs)) {
            $this->ajaxReturn($rst);
        }

        if (!empty($post['starttime']) && empty($post['endttime'])) {
            $map['a.time'] = array('EGT', strtotime($post['starttime']));
        }

        if (!empty($post['endtime']) && empty($post['starttime'])) {
            $map['a.time '] = array('ELT', strtotime($post['endtime']));
        }

        if ($post['starttime'] && $post['endtime']) {
            $map['time '] = array(array('gt' , strtotime($post['starttime'])) , array('lt' , strtotime($post['endtime']) +24*60*60-1),'and');
        }

        if (!empty($post['organization_id']) && $post['organization_id'] != 'all') {
            $map['a.organization_id '] = $post['organization_id'];
        } else {
            $map['a.organization_id'] =  array('in', array_keys($orgs));
        }

        if (!empty($post['engineer'])) {
            $map['a.engineer_id '] = $post['engineer'];
        }

        if (!empty($post['type']) && $post['type'] != 'all') {
            $map['a.type'] = $post['type'];
        }

        if ($post['status'] == 'all') {
            unset($map['status']) ;
        }

        if ($post['status'] === '0' && $post['status'] != 'all') {
            $map['a.status'] = 0;
        }

        if (!empty($post['status']) && $post['status'] != 'all') {
                $map['a.status'] = $post['status'];
        }

        if (!empty($post['number'])) {
            $map['r.number'] =  array('like', '%' . trim($post['number']) . '%');
        }

        $join = 'a left join organization o on a.organization_id=o.id
                left join `order` r on a.order_id=r.id
                left join user u on a.user_id=u.id
                left join engineer e on a.engineer_id=e.id';

        $count = M('waste_refund')->join($join)->where($map)->count();

        $rst['total'] = $count;

        $list = M('waste_refund')->join($join)->where($map)->limit($this->page())
            ->field('a.id,a.order_id, r.phone_name, r.color, r.number, r.type, e.name as engineer, u.username as user, o.name as organization, a.status, a.time, a.wastes, a.remark')
            ->order('id desc')->select();

        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 审核
     * @author liyang
     * @return void
     */
    public function refundAuditor()
    {
        /** 状态：-1 取消 (审核不通过)  0 待审核 1 审核通过 2无废料退还*/
        $post = I('post.');

        $map = array();
        $rst = array();
        $map['id'] = $post['id'] ? $post['id'] : I('get.id') ;
        $item = M('waste_refund')->where($map)->find();

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
        if (!in_array($item['organization_id'], array_keys($orgs))) {
            $rst['success'] = false;
            $rst['errorMsg'] = '没有权限审核此申请！请联系目标仓库管理员审核！';
            $this->ajaxReturn($rst);
        }

        $flag = $post['flag'];
        $data = array();

        M()->startTrans();
        /** 审核通过 or 不通过 */
        if ($flag == 1) {
            $sendRst = D('waste')->refund($item);

            if ($sendRst === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '配件数据错误！';
                $this->ajaxReturn($rst);
            }

            $data['status'] = 1;
        } elseif (I('get.status') == 2) {
           $data['status'] = 2;
           $data['remark'] = $post['comments'];
        } else {
            $data['status'] = $flag;
        }

        /** 审核人 */
        $data['user_id'] = session('userId');

        if (D('waste_refund')->where($map)->limit(1)->save($data) !== false) {
            M()->commit();
            $rst['success'] = true;
        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '操作失败！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 获取废料
     * @author liyang
     * @return void
     */
    public function wastes()
    {
        $model = M('phone');
        $model->join('left join phone_waste on phone.id = phone_waste.phone_id');
        $model->join('left join waste on phone_waste.waste_id = waste.id ');
        $model->where(['phone.id' => I('get.id')]);
        $model->field('waste.id, concat(waste.title, "(", waste.number , ")") as title');
        $rst = $model->select();
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
    
        $inputFileType = \PHPExcel_IOFactory::identify($_FILES['waste_file']['tmp_name']);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($_FILES['waste_file']['tmp_name']);
        $data = $objPHPExcel->getSheet(0)->toArray();
    
        $total_rows = 0; //总行数
        $fail_rows = array(); //导入失败行
        $flag = true;
    
        M()->startTrans();
    
        foreach ($data as $k => $value) {
    
            if ($k < 1) {
                continue;
            }
    
            if (empty($value[0]) && empty($value[1]) && empty($value[2]) && empty($value[3])) {
                continue;
            }
    
            $total_rows++;
    
            if (empty($value[0]) || empty($value[1]) || !is_numeric($value[2]) || $value[2] < 0 || empty($value[3])) {
                $fail_rows[] = "第{$k}行编号、名称、价格或机型不能为空！";
                continue;
            }
    
            if (M('waste')->where(array('number'=>trim($value[0])))->count()) {
                $fail_rows[] = "第{$k}行配件编号已经存在！";
                continue;
            }
    
            $phones = explode(',', $value[3]);
            $map = array(
                'alias' => array('in', $phones),
                'id' => array('in', $phones),
                '_logic' => 'OR'
            );
    
            $phone_ids = array_keys(M('phone')->where($map)->getField('id, alias'));
    
            if (!$phone_ids) {
                $fail_rows[] = "第{$k}行未匹配到对应机型！";
                continue;
            }
    
            $param = array(
                'number' => trim($value[0]),
                'title' => trim($value[1]),
                'price' => trim($value[2]),
                'remark' => trim($value[4]),
            );
    
            $waste_id = M('waste')->add($param);
    
            if ($waste_id === false) {
                $flag = false;
                continue;
            }
    
            foreach ($phone_ids as $phone_id) {
    
                if (M('phone_waste')->add(array('phone_id' => $phone_id, 'waste_id' => $waste_id) ) === false) {
                    $flag = false;
                }
            }
        }
    
        if ($flag) {
            M()->commit();
            $rst['success'] = true;
            $success_rows = $total_rows - count($fail_rows);
            $rst['errorMsg'] = "导入{$total_rows}行数据；成功导入{$success_rows}行！";
    
            if ($fail_rows) {
                $rst['errorMsg'] .= "导入失败：" . implode('', $fail_rows) . '。';
            }
        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '配件导入失败（写入数据失败）！';
        }
    
        $this->ajaxReturn($rst);
    }
    
    /**
     * 导出
     *
     * @return void
     */
    public function export()
    {
        $data = I('post.');
    
        if (!empty($data['phone_id'])) {
            $map['pw.phone_id'] = $data['phone_id'];
        }
    
        if (!empty($data['keyword'])) {
            $where = array();
            $where['w.number']  = array('like', '%' . $data['keyword'] . '%');
            $where['w.title']  = array('like', '%' . $data['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
    
        $params = array();
        $params[] = array('名称', '编号', '价格', '机型', '备注');
    
        $list = M('waste')->field('w.*, group_concat(distinct(p.alias)) as phone')
                ->join('w left join phone_waste pw on w.id=pw.waste_id left join phone p on pw.phone_id=p.id')
                ->where($map)
                ->group('w.id')
                ->select();
    
        foreach ($list as $item) {
            $params[] = array($item['title'], $item['number'], $item['price'], $item['phone'], $item['remark']);
        }
    
        $this->exportData('废件列表', $params);
    }

    /**
     * 废料退还详情
     * @author liyang
     * @return void
     */
    public function refundDetail()
    {
        $model = M('waste_refund');
        $model->join('wr left join `order` on wr.order_id = order.id');
        $model->where(['wr.id'=>I('get.id')]);
        $rst = $model->field('wastes,phone_name')->find();

        $rst['wastes'] = json_decode($rst['wastes'],true);
        foreach ($rst['wastes'] as &$value) {
            $value['phone_name'] = $rst['phone_name'];
        }
        unset($rst['phone_name']);
        $this->ajaxReturn($rst['wastes']);
    }

    /**
     * 获取当前地区工程师
     * @author liyang
     * @return void
     */
    public function engineer()
    {
        $id = I('get.id');
        $map = array('status' => 1);

        if (!empty($id)) {
            $map['organization_id'] = $id;
        }

        $model = M('engineer');
        $model->where($map);
        $model->order('level');
        $model->field('id,name');
        $rst = $model->select();

        array_unshift($rst,array('name'=>'全部','id'=>''));

        $this->ajaxReturn($rst);
    }

    /**
     * 获取审核人
     * @author liyang
     * @return void
     */
    public function auditor()
    {
        $id = I('get.id');
        $map = array('user.status' => 1);

        if (!empty($id)) {
            $map['o.id'] = $id;
        }

        $model = M('user');
        $model->join('left join user_organization as uo on uo.user_id = user.id');
        $model->join('left join organization as o on o.id = uo.organization_id');
        $model->where($map);
        $model->field('user.id as id , realname');
        $model->group('user_id');
        $rst = $model->select();

        array_unshift($rst,array('realname'=>'全部','id'=>''));

        $this->ajaxReturn($rst);
    }
    
    /**
     * 导入配件信息
     *
     * @return void
     */
    public function importWaste()
    {
        $rst = array();
    
        Vendor('PHPExcel.Classes.PHPExcel.IOFactory');
    
        $inputFileType = \PHPExcel_IOFactory::identify($_FILES['fitting_file']['tmp_name']);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($_FILES['fitting_file']['tmp_name']);
        $data = $objPHPExcel->getSheet(0)->toArray();
    
        $params = array();
    
        foreach ($data as $k => $value) {
    
            if ($k < 1) {
                continue;
            }
    
            $value[2] = (int) $value[2];
    
            if (empty($value[0]) || empty($value[1]) || $value[2] < 1) {
                continue;
            }
    
            $phone_id = trim($value[0]);
            $map = array(
                'alias' => trim($value[0]),
                'id' => trim($value[0]),
                '_logic' => 'OR'
            );
    
            $map = array(
                'number' => trim($value[0]),
            );
    
            $fitting = M('waste')->join('w left join phone_waste pw on pw.waste_id = w.id')
                        ->join('phone p on p.id = pw.phone_id')
                        ->field('w.id, group_concat(pw.phone_id) as phone_id, group_concat(p.alias) as phone, concat(w.title, "(", w.number, ")") as fitting')
                        ->where($map)->group('w.id')->select();
    
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
    
            $fitting = $fitting[0];
    
            $params[] = array(
                'phone_id' => $fitting['phone_id'],
                'phone' => $fitting['phone'],
                'fitting' => $fitting['fitting'],
                'fittings_id' => $fitting['id'],
                'amount' => $value[2],
                'price' => $value[3],
            );
        }
    
        $rst['success'] = true;
        $rst['data'] = $params;
        $this->ajaxReturn($rst);
    }
    
    /**
     * 出入库记录
     *
     * @return void
     */
    public function wasteInout()
    {
        $map = array();
        $rst = array();
        $post = I('post.');
    
        $orgs = session('organizations');
    
        if (!empty($orgs)) {
            $map['wi.organization_id'] = array('in', array_keys($orgs));
        } else {
            $this->ajaxReturn($rst);
        }
    
        if (!empty($post['organization_id'])) {
            $map['wi.organization_id'] = $post['organization_id'];
        } else {
            $this->ajaxReturn($rst);
        }
    
        if (!empty($post['waste_id'])) {
            $map['wi.waste_id'] = $post['waste_id'];
        } else {
            $this->ajaxReturn($rst);
        }

        $join = 'wi left join waste w on wi.waste_id=w.id
            left join organization as o on wi.organization_id=o.id
            left join user u on wi.user_id=u.id
            left join engineer e on wi.engineer_id=e.id';
    
        $count = M('waste_inout')->where($map)->join($join)->count();
        $rst['total'] = $count;
    
        $list = M('waste_inout')->join($join)->where($map)->limit($this->page())
                ->field("wi.id, wi.type, wi.inout, o.alias as organization, concat(w.title, '(', w.number, ')') as waste, 
                    wi.amount, wi.price, wi.order_id, u.username as audit, e.name as engineer, wi.time")
                ->order('wi.id desc')->select();
        $rst['rows'] = $list;
    
        $this->ajaxReturn($rst);
    }
    
    /**
     * 更新废料库存
     */
    public function setWasteWarehouse()
    {
        $id = I('get.id/d');
        $data = I('post.');
        $map = array('ww.id' => $id);
        $order_id = 0;
        $engineer_id = 0;
        
        $rst = array();
        
        $warehouse = M('waste_warehouse')->join('ww left join waste w on w.id = ww.waste_id')
                    ->field('ww.*, w.price')->where($map)->find();
        
        if (!$warehouse) {
            $rst['success'] = false;
            $rst['errorMsg'] = '库存不存在！';
            $this->ajaxReturn($rst);
        }
        
        if (empty($data['number'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入增减库存数量！';
            $this->ajaxReturn($rst);
        }
    
        if ($warehouse['amount'] + $data['number'] < 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '库存更新后不能为负值，请设置合理的参数！';
            $this->ajaxReturn($rst);
        }
        
        if ($data['order_number']) {
            $order = M('order')->where(array('number' => trim($data['order_number'])))
                    ->field('id, engineer_id')->find();
            
            if (!$order) {
                $rst['success'] = false;
                $rst['errorMsg'] = '订单不存在！';
                $this->ajaxReturn($rst);
            }
            
            $order_id = $order['id'];
            $engineer_id = $order['engineer_id'];
        }

        $flag = true;
        M()->startTrans();
    
        if ($data['number'] > 0) {//增加库存
    
            $stock = array();
    
            for ($i = 1; $i <= $data['number']; $i++) {
                $stock[] = array(
                    'number' => D('warehouse')->createNumber(),
                    'status' => 1,
                    'waste_id' => $warehouse['waste_id'],
                    'order_id' => $order_id,
                    'engineer_id' => $engineer_id,
                    'price' => $warehouse['price'],
                    'organization_id' => $warehouse['organization_id'],
                    'recycle_time' => time(),
                    'create_time' => time()
                );
            }
    
            if (M('waste_warehouse')->where(array('id' => $id))->setInc('amount', $data['number']) === false) {
                $flag = false;
            }
    
            if (M('waste_stock')->addAll($stock) === false) {
                $flag = false;
            }
            
            $param = array(
                'type' => 1,
                'inout' => 1,
                'organization_id' => $warehouse['organization_id'],
                'waste_id' => $warehouse['waste_id'],
                'order_id' => $order_id,
                'engineer_id' => $engineer_id,
                'user_id' => session('userId'),
                'amount' => $data['number'],
                'price' => $warehouse['price'],
                'time' => time()
            );
            
            if (M('waste_inout')->add($param) === false) {
                $flag = false;
            }
        } else {//减少库存
            $data['number'] = abs($data['number']);
    
            if (M('waste_warehouse')->where(array('id' => $id))->setDec('amount', $data['number']) === false) {
                $flag = false;
            }
    
            $map = array(
                'status' => 1,
                'organization_id' => $warehouse['organization_id'],
                'waste_id' => $warehouse['waste_id'],
            );
            
            if ($order_id) {
                $map['order_id'] = $order_id;
            }
            
            if (M('waste_stock')->where($map)->limit($data['number'])->save(array('organization_id' => 0, 'status' => -1)) === false) {
                $flag = false;
            }
            
            $param = array(
                'type' => 1,
                'inout' => 2,
                'organization_id' => $warehouse['organization_id'],
                'waste_id' => $warehouse['waste_id'],
                'user_id' => session('userId'),
                'order_id' => $order_id,
                'engineer_id' => $engineer_id,
                'amount' => $data['number'],
                'price' => $warehouse['price'],
                'time' => time()
            );
            
            if (M('waste_inout')->add($param) === false) {
                $flag = false;
            }
        }
    
        if ($flag) {
            M()->commit();
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '库存更新失败！';
        }
        $this->ajaxReturn($rst);
    }


    /**
     * 出入库导出
     *
     * @return void
     */
    public function inoutExport()
    {
        $type = array(
            1 => '出入库',
            2 => '调拨',
            3 => '工程师退还',
            4 => '报损',
            5 => '回收出库',
        );
        $map = array();
        $organizations = session('organizations');

        $post = I('post.');

        if ($organizations) {
            $map['waste_inout.organization_id '] = array('in', implode(',', array_keys($organizations)));
        }

        if ($post['id']) {
            $map['waste_inout.organization_id'] = $post['id'];
        }

        if ($post['type']) {
            $map['waste_inout.type'] = $post['type'];
        }

        if ($post['inout']) {
            $map['waste_inout.inout'] = $post['inout'];
        }

        if ($post['user']) {
            $map['waste_inout.user_id'] = $post['user'];
        }

        if (!empty($post['keyword'])) {
            $like['w.number'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['w.title'] = array('like',  '%' . trim($post['keyword']) . '%');
            $like['o.number'] = array('like',  '%' . trim($post['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        if (!empty($post['starttime'] && $post['endtime'])) {
            $map['waste_inout.time'] = array('egt', strtotime($post['starttime']));
        }

        if (!empty($post['endtime']) && empty($post['starttime'])) {
            $map['waste_inout.time '] = array('elt', strtotime($post['endtime'])+24*60*60-1);
        }

        if ($post['starttime'] && $post['endtime']) {
            $map['waste_inout.time '] = array(array('gt',strtotime($post['starttime'])),array('lt',strtotime($post['endtime']) +24*60*60-1),'and');
        }

        $list = M('waste_inout')
            ->join('left join organization org ON waste_inout.organization_id = org.id')
            ->join('left join waste w ON waste_inout.waste_id = w.id')
            ->join('left join user u ON waste_inout.user_id = u.id')
            ->join('left join engineer e ON waste_inout.engineer_id = e.id')
            ->join('left join `order` o on o.id = waste_inout.order_id')
            ->where($map)
            ->field('waste_inout.*, o.phone_name , concat(w.title, "(", w.number, ")") as title, u.realname, e.name, org.alias as org, o.number')
            ->order('waste_inout.id desc')
            ->select();

        $title = array(
            'ID',
            '地区',
            '类型',
            '出入库',
            '废料',
            '机型',
            '数量',
            '工程师',
            '订单号',
            '时间' ,
            '价格',
            '剩余',
            '经手人',
        );
        $exports[] = $title;


        foreach ($list as $value) {
            $item = array();
            $item[] = $value['id'];
            $item[] = $value['org'];
            $item[] = $value['inout'] == 1 ? '入库' : '出库';
            $item[] = $type[$value['type']];
            $item[] = $value['title'];
            $item[] = $value['phone_name'];
            $item[] = $value['amount'];
            $item[] = $value['name'];
            $item[] = $value['number'];
            $item[] = $value['time'] = date('Y-m-d H:i:s',$value['time']);
            $item[] = $value['price'];
            $item[] = $value['residue'];
            $item[] = $value['realname'];

            $exports[] = $item;
        }

        $this->exportData('废件列表', $exports);
    }

}