<?php
 
// +------------------------------------------------------------------------------------------ 
// | Author: zhujianping <zhujianping@wedoc.com>
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2017-06-07
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;
use Api\Controller\AddressController;

class ThirdinsuranceController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 来源
     *
     * @return void
     */
    public function source()
    {
        $map = array();
        $list = M('third_party_token')->where($map)->select();
        array_unshift($list,array('name'=>'全部','id'=>''));
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
        $model = M('third_insurance_order');
        $post = I('post.');
        $map = array();
        
        if (!empty($post['create_stime'])) {
            $map['tio.create_time'] = array('egt', strtotime($post['create_stime']));
        }
        
        if (!empty($post['create_etime'])) {
            $map['tio.create_time '] = array('elt', strtotime($post['create_etime'] . ' 23:59:59.999'));
        }
        
        if (!empty($post['pay_stime'])) {
            $map['tio.pay_time'] = array('egt', strtotime($post['pay_stime']));
        }
        
        if (!empty($post['pay_etime'])) {
            $map['tio.pay_time '] = array('elt', strtotime($post['pay_etime'] . ' 23:59:59.999'));
        }

        if (is_numeric($post['audit_status'])) {
            $map['tio.audit_status'] = $post['audit_status'];
        }
        
        if (!empty($post['source']) && $post['source'] != 'all') {
            $map['third_party_order.source'] = $post['source'];
        }
        
        if (!empty($post['engineer_id']) && $post['engineer_id'] != 'all') {
            $map['tio.engineer_id'] = $post['engineer_id'];
        }
        
        if ($post['keyword']) {
            //$like['pi.title'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['tio.id'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['tio.number'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['tio.customer'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['tio.cellphone'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }
        
        $join = "tio left join `third_party_order` on third_party_order.order_number = tio.old_order_number
                left join `third_party_order` as o2 on o2.order_number = tio.order_number
                left join `user` u on u.id = tio.auditor
                ";
        
        $rst['total'] = $model->join($join)->where($map)->count();
        $rst['rows'] = $model->join($join)
                        ->field("tio.*, third_party_order.phone_name,
                            third_party_order.order_number as old_order_number, third_party_order.phone_imei,  o2.order_number, 
                            u.username")
                        ->where($map)->limit($this->page())->order('tio.create_time desc')->select();
        
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
        
        $item = M('third_insurance_order')->where($map)->field('status, effect_time')->find();
        
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
        
        if (M('third_insurance_order')->where($map)->setField('status', '-1') === false) {
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
        
        $list = M('third_insurance_order_log')->where(array('third_insurance_order_id' => $id))->select();
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
        $model = M('third_insurance_order');
        $post = I('post.');
        $map = array('tio.status' => array('in', '3,5'));
    
        if (!empty($post['broken_stime'])) {
            $map['tio.broken_time'] = array('egt', strtotime($post['broken_stime']));
        }
    
        if (!empty($post['broken_etime'])) {
            $map['tio.broken_time '] = array('elt', strtotime($post['broken_etime'] . ' 23:59:59.999'));
        }
    
        if (!empty($post['status']) && $post['status'] != 'all') {
            $map['tio.status'] = $post['status'];
        }
    
        if (!empty($post['source']) && $post['source'] != 'all') {
            $map['third_party_order.source'] = $post['source'];
        }

        if ($post['keyword']) {
            //$like['pi.title'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['tio.id'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['tio.number'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['tio.customer'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['tio.cellphone'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        $join = "tio left join third_insurance_workorder tiw on tiw.third_insurance_id=tio.id
                left join engineer e on e.id = tiw.engineer_id
                left join `third_party_order` on third_party_order.order_number = tio.old_order_number
                left join `third_party_order` as o2 on o2.order_number = tio.order_number
                left join `user` u on u.id = tio.auditor
                ";
    
        $rst['total'] = $model->join($join)->where($map)->count();
        $rst['rows'] = $model->join($join)
            ->field("tio.*, third_party_order.phone_name,e.name as engineer,
                            third_party_order.order_number as old_order_number, third_party_order.phone_imei,  o2.order_number, 
                            u.username")
                        ->where($map)->limit($this->page())->order('tio.broken_time desc, tio.create_time desc')->select();
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
        
        $item = M('third_insurance_order')->where($map)->field('broken_flag, failure_time, order_id')->find();
        
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
        
        if (M('third_insurance_order')->where($map)->save($data) === false) {
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
        $model = M('third_insurance_order');

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
     * 审核出险资料
     */
    public function audit()
    {
        $id = I('get.id');
        $data = I('post.');
        $rst = array();
        $map = array('id' => $id);
        
        $item = M('third_insurance_order')->where($map)->field('status, broken_flag, failure_time, cellphone')->find();
        
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
            /*$order_id = D('thirdInsurance')->insuranceToMaintainOrder($id); //根据保险单产生维修单
            
            if ($order_id === false) {
                $flag = false;
            }*/
            
            $data['broken_flag'] = 1;
            $data['auditor'] = session('userId');
            //$data['order_id'] = (int) $order_id;
            
            $action = "操作人：".session('userInfo.username').'--客户报险审核通过--备注：' . $data['remark'] . '--状态:' . C('INSURANCE_STATUS')[$item['status']];
        } else {
            $data['broken_flag'] = -1;
            $data['auditor'] = session('userId');
            
            $action = "操作人：".session('userInfo.username').'--客户报险审核不通过--备注：' . $data['remark'] . '--状态:' . C('INSURANCE_STATUS')[$item['status']];
        }
        
        if (M('third_insurance_order')->where($map)->save($data) === false) {
            $flag = false;
        }
        
        if (D('thirdInsurance')->writeLog($id, $action) === false) {
            $flag = false;
        }
        
        if ($flag) {
            M()->commit();
            $rst['success'] = true;

        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '审核失败，请重试！';
        }
        
        $this->ajaxReturn($rst);
    }

    /**
     * 审核保险资料
     */
    public function auditStatus()
    {
        $id = I('get.id');
        $data = I('post.');
        $rst = array();
        $map = array('id' => $id);

        $item = M('third_insurance_order')->where($map)->field('number,imei_img,status,audit_status,failure_time, cellphone')->find();


        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此保险记录不存在！';
            $this->ajaxReturn($rst);
        }
        /*
        if ($item['audit_status'] != 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此保险资料已审核，请刷新页面！';
            $this->ajaxReturn($rst);
        }*/

        if ($data['flag'] != 1 && empty($data['reason'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '审核不通过，请输入备注信息！';
            $this->ajaxReturn($rst);
        }

        $flag = true;
        M()->startTrans();

        if ($data['flag'] == 1) {//审核通过

            $data['audit_status'] = 1;
            $data['status'] = 2;
            $data['auditor'] = session('userId');
            $action = "操作人：".session('userInfo.username').'--客户保险资料审核通过--备注：' . $data['reason'] . '--状态:' . C('INSURANCE_STATUS')[$item['status']];
        } else {
            $data['audit_status'] = -1;
            $data['auditor'] = session('userId');
            $action = "操作人：".session('userInfo.username').'--客户保险资料审核不通过--备注：' . $data['reason'] . '--状态:' . C('INSURANCE_STATUS')[$item['status']];
        }

        if (M('third_insurance_order')->where($map)->save($data) === false) {
            $flag = false;
        }

        if (D('ThirdInsurance')->writeLog($id, $action) === false) {
            $flag = false;
        }

        if ($flag) {
            M()->commit();
            $rst['success'] = true;
            if ($data['flag'] == 1){
                $sms = new \Vendor\aliNote\aliNote();
                $sms->send($item['cellphone'], array('number' => $item['number']),'SMS_71285298');
            }
        } else {
            M()->rollback();
            $rst['success'] = false;
            $rst['errorMsg'] = '审核失败，请重试！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 预约 status 4
     *
     * @return void
     */
    public function appointmentOrder()
    {
        $engineer = M('engineer')->where(array('id' => I('post.contact_engineer')))->find();
        $map = array();
        $map['third_insurance_id'] = I('get.id');

        $data = array();
        $data['engineer_id'] = I('post.contact_engineer');
        $data['address'] = I('post.address');
        $data['before_remark'] = I('post.before_remark');
        $data['service_time'] = strtotime(I('post.service_time'));
        $data['third_insurance_id'] =  $map['third_insurance_id'];

        if (M('third_insurance_workorder')->add($data) === false) {
            $this->ajaxReturn(['success'=>false,'errorMsg'=>'工程师预约失败']);
        }
        $this->ajaxReturn(['success'=>true,'msg'=>'工程师预约成功']);
    }

    /**
     * 完成订单 status 5
     *
     * @return void
     */
    public function finishOrder()
    {
        $post = I('post.');

        if (empty($post['is_visit'])) {
            $this->ajaxReturn(array('success' => false, 'errorMsg' => '是否上门不能为空'));
        }

        $map = array();
        $map['third_insurance_id'] = I('get.id');

        $data = array();
        $data['finish_time'] = time();
        $data['is_visit'] = $post['is_visit'];
        $data['after_remark'] = $post['after_remark'];

        M()->startTrans();
        $flag = M('third_insurance_workorder')->where($map)->save($data);
        if (!$flag || M('third_insurance_order')->where(array('id'=>I('get.id')))->save(array('status'=>5)) === false) {
            M()->rollback();
            $this->ajaxReturn(['success'=>false,'errorMsg'=>'结单失败']);
        }
        M()->commit();
        $this->ajaxReturn(['success'=>true,'msg'=>'结单成功']);
    }

    /**
     * 导出
     */
    public function export()
    {
        set_time_limit(0);
        $model = M('third_insurance_order');
        $post = I('post.');
        $map = array();
        
        if (!empty($post['create_stime'])) {
            $map['tio.create_time'] = array('egt', strtotime($post['create_stime']));
        }
        
        if (!empty($post['create_etime'])) {
            $map['tio.create_time '] = array('elt', strtotime($post['create_etime'] . ' 23:59:59.999'));
        }
        
        if (!empty($post['pay_stime'])) {
            $map['tio.pay_time'] = array('egt', strtotime($post['pay_stime']));
        }
        
        if (!empty($post['pay_etime'])) {
            $map['tio.pay_time '] = array('elt', strtotime($post['pay_etime'] . ' 23:59:59.999'));
        }
        
        if (!empty($post['city']) && $post['city'] != 'all') {
            $map['order.city'] = $post['city'];
        }
        
        if (!empty($post['status']) && $post['status'] != 'all') {
            $map['tio.status'] = $post['status'];
        }
        
        if (!empty($post['phone_id']) && $post['phone_id'] != 'all') {
            $map['pi.phone_id'] = $post['phone_id'];
        }
        
        if (!empty($post['engineer_id']) && $post['engineer_id'] != 'all') {
            $map['tio.engineer_id'] = $post['engineer_id'];
        }
        
        if ($post['keyword']) {
            $like['pi.title'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['p.name'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['p.alias'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['tio.number'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['tio.customer'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['tio.cellphone'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['tio.old_order_id'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['third_party_order.number'] = array('LIKE', '%' . $post['keyword'] . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }
        
        $join = "tio left join phomal_insurance pi on pi.id = tio.phomal_insurance_id
                left join phone p on p.id = pi.phone_id
                left join engineer e on e.id = tio.engineer_id
                left join `order` on order.id = tio.old_order_id
                left join address adr on adr.id= order.city
                left join `order` as o2 on o2.id = tio.order_id
                left join `user` u on u.id = tio.auditor
                ";
        
        $list = $model->join($join)
                ->field("tio.*, pi.title as pi_title, p.alias as phone, e.name as engineer,
                    order.number as old_order_number, order.phone_imei, order.address as caddress, o2.id as order_id, 
                    o2.number as order_number, u.username, adr.name as city_name")
                ->where($map)->order('tio.create_time desc')->select();
        
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