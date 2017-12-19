<?php
 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2016-08-05
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;
use Api\Controller\AddressController;

class PhomalinsuranceController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insurance() 
    {
        $this->display();
    }
    
    /**
     * insurance列表
     *
     * @return void
     */
    public function rows()
    {
        $model = M('phomal_insurance');
        $post = I('post.');
        $map = array();

        if (!empty($post['phone_id']) && $post['phone_id'] != 'all') {
            $map['phone_id'] = $post['phone_id'];
        }

        if ($post['keyword']) {
            $like['pi.title'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['p.name'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['p.alias'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pi.remark'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }
        
        $join = "pi left join phone p on p.id = pi.phone_id
                 left join phomal_insurance_phomals pip on pip.phomal_insurance_id = pi.id
                 left join phone_malfunction pm on pm.id = pip.phomal_id
                 left join user u on u.id = pi.user_id";
        $rst['total'] = $model->join($join)->where($map)->count();
        $rst['rows'] = $model->join($join)
                        ->field("pi.*, p.alias as phone,group_concat(pip.phomal_id) as phomal_ids,
                            group_concat(pm.malfunction) as malfunction, u.username")
                        ->where($map)->group('pi.id')->limit($this->page())->order('pi.id desc')->select();

        $this->ajaxReturn($rst);
    }
    
    /**
     * 新增保险
     *
     * @return void
     */
    public function add()
    {
        $data = I('post.');
        $rst = array();
    
        if (empty($data['title'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入保险名称！';
            $this->ajaxReturn($rst);
        }
    
        $data['duration'] = intval($data['duration']);
    
        if ($data['duration'] <= 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入合理的保险年限！';
            $this->ajaxReturn($rst);
        }
    
        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入保险价格！';
            $this->ajaxReturn($rst);
        }
    
        if (!is_numeric($data['divide']) || $data['divide'] < 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入工程师分成！';
            $this->ajaxReturn($rst);
        }
    
        if (empty($data['phone_id']) || $data['phone_id'] <= 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择机型！';
            $this->ajaxReturn($rst);
        }
    
        if (empty($data['phomal_ids'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择机型故障！';
            $this->ajaxReturn($rst);
        }
    
        $where = array(
            'phomal_id' => array('in', $data['phomal_ids']),
        );
    
        if (M('phomal_insurance_phomals')->where($where)->count()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '机型故障不能重复添加保险服务！';
            $this->ajaxReturn($rst);
        }
    
        $data['user_id'] = session('userId');
        $data['create_time'] = time();
        
        $flag = true;
        
        M()->startTrans();
        
        $id = M('phomal_insurance')->add($data);
    
        if ($id === false) {
            $flag = false;
        }
        
        $phomal_ids = explode(',', $data['phomal_ids']);
        $param = array();
        
        foreach ($phomal_ids as $phomal_id) {
            $param[] = array('phomal_insurance_id' => $id, 'phomal_id' => $phomal_id);
        }
        
        if (M('phomal_insurance_phomals')->addAll($param) === false)
        {
            $flag = false;
        }
        
        if ($flag) {
            M()->commit();
            $rst['success'] = true;
        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '保险添加失败！';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 编辑保险
     *
     * @return void
     */
    public function edit()
    {
        $id = I('get.id/d');
        
        if (!M('phomal_insurance')->where(array('id' => $id))->count()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '保险记录不存在！';
            $this->ajaxReturn($rst);
        }
        
        $data = I('post.');
        $rst = array();
    
        if (empty($data['title'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入保险名称！';
            $this->ajaxReturn($rst);
        }
    
        $data['duration'] = intval($data['duration']);
    
        if ($data['duration'] <= 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入合理的保险年限！';
            $this->ajaxReturn($rst);
        }
    
        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入保险价格！';
            $this->ajaxReturn($rst);
        }
    
        if (!is_numeric($data['divide']) || $data['divide'] < 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入工程师分成！';
            $this->ajaxReturn($rst);
        }
    
        if (empty($data['phone_id']) || $data['phone_id'] <= 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择机型！';
            $this->ajaxReturn($rst);
        }
    
        if (empty($data['phomal_ids'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请选择机型故障！';
            $this->ajaxReturn($rst);
        }
    
        $where = array(
            'phomal_id' => array('in', $data['phomal_ids']),
            'phomal_insurance_id' => array('neq', $id)
        );
    
        if (M('phomal_insurance_phomals')->where($where)->count()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '机型故障不能重复添加保险服务！';
            $this->ajaxReturn($rst);
        }
    
        $flag = true;
        M()->startTrans();
    
        if (M('phomal_insurance')->where(array('id' => $id))->save($data) === false) {
            $flag = false;
        }
        
        if (M('phomal_insurance_phomals')->where(array('phomal_insurance_id' => $id))->delete() === false) {
            $flag = false;
        }
        
        $phomal_ids = explode(',', $data['phomal_ids']);
        $param = array();
        
        foreach ($phomal_ids as $phomal_id) {
            $param[] = array('phomal_insurance_id' => $id, 'phomal_id' => $phomal_id);
        }
        
        if (M('phomal_insurance_phomals')->addAll($param) === false)
        {
            $flag = false;
        }
        
        if ($flag) {
            M()->commit();
            $rst['success'] = true;
        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '保险添加失败！';
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
        $id = I('get.id/d');
        
        if (M('phomal_insurance')->where(array('id' => $id))->setField('status', -1)) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '保险删除失败！';
        }
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
        array_unshift($list,array('alias'=>'全部','id'=>''));
        $this->ajaxReturn($list);
    }
    
    /**
     * 机型故障
     *
     * @return void
     */
    public function phomals()
    {
        $map = array();
        $map['phone_id'] = I('get.phone_id/d');
        $list = M('phone_malfunction')->join('pm left join malfunction m on m.id = pm.malfunction_id')
                ->field('pm.id, m.name')
                ->where($map)->select();
        $this->ajaxReturn($list);
    }
    
    /**
     * 工程师
     */
    public function engineers()
    {
        $list = M('engineer')->where(array('status' => array('gt', -1)))->field('id, name')->select();
        array_unshift($list,array('name'=>'全部','id'=>''));
        $this->ajaxReturn($list);
    }
    
    /**
     * 组织
     *
     * @return void
     */
    public function organization()
    {
        $list = M('organization')->where(array('type' => 1))->field('alias, city')->select();
        array_unshift($list, array('alias'=>'全部','city'=>''));
        $this->ajaxReturn($list);
    }
    
    /**
     * 保险单
     */
    public function order()
    {
        $this->display();
    }
    
    /**
     * 保险单列表
     */
    public function orderRows()
    {
        $model = M('phomal_insurance_order');
        $post = I('post.');
        $map = array();
        
        if (!empty($post['create_stime'])) {
            $map['pio.create_time'] = array('egt', strtotime($post['create_stime']));
        }
        
        if (!empty($post['create_etime'])) {
            $map['pio.create_time '] = array('elt', strtotime($post['create_etime'] . ' 23:59:59.999'));
        }
        
        if (!empty($post['pay_stime'])) {
            $map['pio.pay_time'] = array('egt', strtotime($post['pay_stime']));
        }
        
        if (!empty($post['pay_etime'])) {
            $map['pio.pay_time '] = array('elt', strtotime($post['pay_etime'] . ' 23:59:59.999'));
        }
        
        if (!empty($post['city']) && $post['city'] != 'all') {
            $map['order.city'] = $post['city'];
        }
        
        if (is_numeric($post['status'])) {
            $map['pio.status'] = $post['status'];
        }
        
        if (!empty($post['phone_id']) && $post['phone_id'] != 'all') {
            $map['pi.phone_id'] = $post['phone_id'];
        }
        
        if (!empty($post['engineer_id']) && $post['engineer_id'] != 'all') {
            $map['pio.engineer_id'] = $post['engineer_id'];
        }
        
        if ($post['keyword']) {
            $like['pi.title'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['p.name'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['p.alias'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.id'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.number'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.customer'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.cellphone'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.old_order_id'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['order.number'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }
        
        $join = "pio left join phomal_insurance pi on pi.id = pio.phomal_insurance_id
                left join phone p on p.id = pi.phone_id
                left join engineer e on e.id = pio.engineer_id
                left join `order` on order.id = pio.old_order_id
                left join `order` as o2 on o2.id = pio.order_id
                left join `user` u on u.id = pio.auditor
                ";
        
        $rst['total'] = $model->join($join)->where($map)->count();
        $rst['rows'] = $model->join($join)
                        ->field("pio.*, pi.title as pi_title, p.alias as phone, e.name as engineer,
                            order.number as old_order_number, order.phone_imei, order.address as caddress, o2.id as order_id, 
                            o2.number as order_number, u.username")
                        ->where($map)->limit($this->page())->order('pio.create_time desc')->select();
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 付款取消
     */
    public function orderCancel()
    {
        $id = I('post.id/d');
        $rst = array();
        $map = array('id' => $id);
        
        $item = M('phomal_insurance_order')->where($map)->field('status, effect_time')->find();
        
        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此保险记录不存在！';
            $this->ajaxReturn($rst);
        }
        
        if (($item['status'] == 1 && $item['effect_time'] <= time()) || $item['status'] > 1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '保险已生效不能取消，请刷新页面！';
            $this->ajaxReturn($rst);
        }
        
        $flag = true;
        M()->startTrans();
        
        if (M('phomal_insurance_order')->where($map)->setField('status', '-1') === false) {
            $flag = false;
        }
        
        $action = "操作人：".session('userInfo.username').'--手动取消保险单';
        
        if (D('phomalInsurance')->writeLog($id, $action) === false) {
            $flag = false;
        }
        
        if ($flag) {
            M()->commit();
            $rst['success'] = true;
        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '保险取消失败！';
        }
        $this->ajaxReturn($rst);
    }
    
    /**
     * 保险单日志
     */
    public function orderLogs()
    {
        $id = I('get.id/d');
        
        if (!$id) {
            $this->ajaxReturn(array());
        }
        
        $list = M('phomal_insurance_order_log')->where(array('phomal_insurance_order_id' => $id))->select();
        $this->ajaxReturn($list);
    }
    
    /**
     * 出险订单
     */
    public function broken()
    {
        $this->display();
    }
    
    /**
     * 出险订单列表
     */
    public function brokenRows()
    {
        $model = M('phomal_insurance_order');
        $post = I('post.');
        $map = array('pio.status' => array('in', '3,5'));
    
        if (!empty($post['broken_stime'])) {
            $map['pio.broken_time'] = array('egt', strtotime($post['broken_stime']));
        }
    
        if (!empty($post['broken_etime'])) {
            $map['pio.broken_time '] = array('elt', strtotime($post['broken_etime'] . ' 23:59:59.999'));
        }
    
        if (!empty($post['broken_flag']) && $post['broken_flag'] != 'all') {
            $map['pio.broken_flag'] = $post['broken_flag'];
        }
    
        if (!empty($post['phone_id']) && $post['phone_id'] != 'all') {
            $map['pi.phone_id'] = $post['phone_id'];
        }
    
        if ($post['keyword']) {
            $like['pi.title'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['p.name'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['p.alias'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.number'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.customer'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.cellphone'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.old_order_id'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['order.number'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.order_id'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['o2.number'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }
    
        $join = "pio left join phomal_insurance pi on pi.id = pio.phomal_insurance_id
                left join phone p on p.id = pi.phone_id
                left join engineer e on e.id = pio.engineer_id
                left join `order` on order.id = pio.old_order_id
                left join `order` as o2 on o2.id = pio.order_id
                left join `user` u on u.id = pio.auditor
                ";
    
        $rst['total'] = $model->join($join)->where($map)->count();
        $rst['rows'] = $model->join($join)
                        ->field("pio.*, pi.title as pi_title, p.alias as phone, e.name as engineer,
                            order.number as old_order_number, order.phone_imei, order.address as caddress, o2.id as order_id,
                            o2.number as order_number, u.username")
                        ->where($map)->limit($this->page())->order('pio.broken_time desc, pio.create_time desc')->select();
        $this->ajaxReturn($rst);
    }
    
    /**
     * 取消报险申请
     */
    public function brokenCancel()
    {
        $id = I('get.id/d');
        $data = I('post.');
        $rst = array();
        $map = array('id' => $id);
        
        $item = M('phomal_insurance_order')->where($map)->field('broken_flag, failure_time, order_id')->find();
        
        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此保险记录不存在！';
            $this->ajaxReturn($rst);
        }
        
//        if ($item['broken_flag'] == 1 || $item['order_id'] > 0) {
//            $rst['success'] = false;
//            $rst['errorMsg'] = '此报险已审核通过，不能取消！';
//            $this->ajaxReturn($rst);
//        }
        
        $data['status'] = $item['failure_time'] < time() ? 4 : 2;//4-失效 2-生效
        $data['auditor'] = session('userId');
        $data['broken_time'] = 0;
        $data['broken_img'] = '';
        $data['broken_flag'] = 0;
        
        $action = "操作人：".session('userInfo.username').'--手动取消客户报险--备注：' . $data['remark'] . '--状态:' . C('INSURANCE_STATUS')[$data['status']];
        
        $flag = true;
        M()->startTrans();
        
        if (M('phomal_insurance_order')->where($map)->save($data) === false) {
            $flag = false;
        }
        
        if (D('phomalInsurance')->writeLog($id, $action) === false) {
            $flag = false;
        }
        
        if ($flag) {
            M()->commit();
            $rst['success'] = true;
        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '报险申请取消失败！';
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 回滚
     */
    public function rollBack()
    {
        $id = I('get.id/d');
        $map = array('id' => $id);
        $model = M('phomal_insurance_order');

        M()->startTrans();
        $model->where($map)->find();

        if (!$model->id) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此保险记录不存在！';
            $this->ajaxReturn($rst);
        }

        if ($model->broken_flag == 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此报险记录已是申请状态，请刷新页面！';
            $this->ajaxReturn($rst);
        }

        $order_rst = M('order')->where(array('id' => $model->order_id))->save(array('status' => '-1', 'close_reason' => '保险单取消'));

        if (!$order_rst){
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '回退失败！';
            $this->ajaxReturn($rst);
        }
        //维修单日志
        $action = '操作人：' . session('userInfo.username') . '状态：保险取消';
        $order_log = D('Admin/order')->writeLog($model->old_order_id, $action);
        $phomalInsurance_log = D('phomalInsurance')->writeLog($id, $action);

        if ($order_log == false || $phomalInsurance_log == false) {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '添加退回日志失败！';
            $this->ajaxReturn($rst);
        }

        $model->broken_flag = 0;

        if ($model->save()) {
            M()->commit();
            $rst['success'] = true;
            $this->ajaxReturn($rst);
        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '回退失败！';
            $this->ajaxReturn($rst);
        }
    }

    /**
     * 审核
     */
    public function audit()
    {
        $id = I('get.id/d');
        $data = I('post.');
        $rst = array();
        $map = array('id' => $id);
        
        $item = M('phomal_insurance_order')->where($map)->field('status, broken_flag, failure_time, cellphone')->find();
        
        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此保险记录不存在！';
            $this->ajaxReturn($rst);
        }
        
        if ($item['broken_flag'] != 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此报险记录已审核，请刷新页面！';
            $this->ajaxReturn($rst);
        }
        
        if ($data['flag'] != 1 && empty($data['remark'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '审核不通过，请输入备注信息！';
            $this->ajaxReturn($rst);
        }
        
        $flag = true;
        M()->startTrans();
        
        if ($data['flag'] == 1) {//审核通过
            $order_id = D('phomalInsurance')->insuranceToMaintainOrder($id); //根据保险单产生维修单
            
            if ($order_id === false) {
                $flag = false;
            }
            
            $data['broken_flag'] = 1;
            $data['auditor'] = session('userId');
            $data['order_id'] = (int) $order_id;
            
            $action = "操作人：".session('userInfo.username').'--客户报险审核通过--备注：' . $data['remark'] . '--状态:' . C('INSURANCE_STATUS')[$item['status']];
        } else {
            $data['broken_flag'] = -1;
            $data['auditor'] = session('userId');
            
            $action = "操作人：".session('userInfo.username').'--客户报险审核不通过--备注：' . $data['remark'] . '--状态:' . C('INSURANCE_STATUS')[$item['status']];
        }
        
        if (M('phomal_insurance_order')->where($map)->save($data) === false) {
            $flag = false;
        }
        
        if (D('phomalInsurance')->writeLog($id, $action) === false) {
            $flag = false;
        }
        
        if ($flag) {
            M()->commit();
            $rst['success'] = true;
            
            //审核不通过-短信通知客户
            if ($data['flag'] != 1 && $item['cellphone']) {
                $sms = new \Vendor\aliNote\aliNote();
                $sms->send($item['cellphone'], array('msg' => $data['remark']), 'SMS_33625107');
            }
        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '审核失败，请重试！';
        }
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 导出
     */
    public function export()
    {
        set_time_limit(0);
        $model = M('phomal_insurance_order');
        $post = I('post.');
        $map = array();
        
        if (!empty($post['create_stime'])) {
            $map['pio.create_time'] = array('egt', strtotime($post['create_stime']));
        }
        
        if (!empty($post['create_etime'])) {
            $map['pio.create_time '] = array('elt', strtotime($post['create_etime'] . ' 23:59:59.999'));
        }
        
        if (!empty($post['pay_stime'])) {
            $map['pio.pay_time'] = array('egt', strtotime($post['pay_stime']));
        }
        
        if (!empty($post['pay_etime'])) {
            $map['pio.pay_time '] = array('elt', strtotime($post['pay_etime'] . ' 23:59:59.999'));
        }
        
        if (!empty($post['city']) && $post['city'] != 'all') {
            $map['order.city'] = $post['city'];
        }
        
        if (!empty($post['status']) && $post['status'] != 'all') {
            $map['pio.status'] = $post['status'];
        }
        
        if (!empty($post['phone_id']) && $post['phone_id'] != 'all') {
            $map['pi.phone_id'] = $post['phone_id'];
        }
        
        if (!empty($post['engineer_id']) && $post['engineer_id'] != 'all') {
            $map['pio.engineer_id'] = $post['engineer_id'];
        }
        
        if ($post['keyword']) {
            $like['pi.title'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['p.name'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['p.alias'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.number'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.customer'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.cellphone'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['pio.old_order_id'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['order.number'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }
        
        $join = "pio left join phomal_insurance pi on pi.id = pio.phomal_insurance_id
                left join phone p on p.id = pi.phone_id
                left join engineer e on e.id = pio.engineer_id
                left join `order` on order.id = pio.old_order_id
                left join address adr on adr.id= order.city
                left join `order` as o2 on o2.id = pio.order_id
                left join `user` u on u.id = pio.auditor
                ";
        
        $list = $model->join($join)
                ->field("pio.*, pi.title as pi_title, p.alias as phone, e.name as engineer,
                    order.number as old_order_number, order.phone_imei, order.address as caddress, o2.id as order_id, 
                    o2.number as order_number, u.username, adr.name as city_name")
                ->where($map)->order('pio.create_time desc')->select();
        
        $status = C('INSURANCE_STATUS');
        $flag = array(-1 => '审核不通过', 0 => '申请', 1 => '审核通过');
        $exorders = array();
        $exorders[] = array(
            '城市' => '城市',
            '保险单ID' => '保险单ID',
            '保险名称'  => '保险名称',
            '保险单号'  => '保险单号',
            '保险价格'  => '保险价格',
            '保险机型'  => '保险机型',
            'IMEI号'  => 'IMEI号',
            '客户' => '客户',
            '联系电话' => '联系电话',
            '联系地址' => '联系地址',
            '推荐人'  => '推荐人',
            '状态' => '状态',
            '生效时间' => '生效时间',
            '失效时间' => '失效时间',
            '投保时间' => '投保时间',
            '付款时间' => '付款时间',
            '付款账号' => '付款账号',
            '交易记录号' => '交易记录号',
            '投保订单ID' => '投保订单ID',
            '投保订单号'  => '投保订单号',
            '出险订单ID' => '出险订单ID',
            '出险订单号' => '出险订单号',
            '审核人'    => '审核人',
            '审核时间'  => '审核时间',
            '审核状态'  => '审核状态',
            '备注'  => '备注',
        );
        
        foreach ($list as $item) {
            $exorders[] = array($item['city_name'], $item['id'], $item['pi_title'], $item['number'], $item['price'], $item['phone'], ' '.$item['phone_imei'],
                $item['customer'], $item['cellphone'], $item['caddress'], $item['engineer'], $status[$item['status']],
                date('Y-m-d H:i:s', $item['effect_time']), date('Y-m-d H:i:s', $item['failure_time']), 
                date('Y-m-d H:i:s', $item['create_time']), ($item['pay_time'] ? date('Y-m-d H:i:s', $item['pay_time']) : ''),
                $item['pay_account'], ' '. $item['pay_number'], $item['old_order_id'], $item['old_order_number'],
                $item['order_id'], $item['order_number'], $item['username'], ($item['broken_time'] ? date('Y-m-d H:i:s', $item['broken_time']) : ''),
                ($item['broken_time'] ? $flag[$item['broken_flag']] : ''), $item['remark']
            );
        }
        
        $this->exportData('保险单_' . date('Y_m_d'), $exorders);
    }
}