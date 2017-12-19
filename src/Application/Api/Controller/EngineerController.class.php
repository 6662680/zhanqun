<?php
// +------------------------------------------------------------------------------------------
// | Author: TCG <tianchunguang@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: app Dates: 2016-09-29
// +------------------------------------------------------------------------------------------
namespace Api\Controller;

use Api\Controller;

class EngineerController extends BaseController
{
    /**
     * 登录接口
     *
     * @return void
     */
    public function login()
    {
        $cellphone = trim(I('param.cellphone'));
        $password = trim(I('param.password'));
        $registrationId = trim(I('param.registrationId'));

        if (empty($cellphone)) {
            $result['status'] = 0;
            $result['info'] = '请输入账号(手机号)';
            $this->ajaxReturn($result);
        }

        if (empty($password)) {
            $result['status'] = 0;
            $result['info'] = '请输入密码';
            $this->ajaxReturn($result);
        }

        if (empty($registrationId)) {
            $result['status'] = 0;
            $result['info'] = '请输入工程师设备号';
            $this->ajaxReturn($result);
        }

        $engineer = M('engineer');
        $map = array();
        $map['e.cellphone'] = $cellphone;
        $map['e.status'] = array('neq', -1);
        $password = createPassword($password);
        $info = $engineer->join('e left join engineer_level el on e.level = el.id')
                ->join('left join engineer_info ei on e.id = ei.engineer_id')
                ->field('e.name, e.password, e.cellphone, e.work_number, e.registration_id, ei.engineer_id, el.title as level, ei.avatar')
                ->where($map)->find();
        $result = array();

        //先判断账号是否存在
        if (empty($info)) {
            $result['status'] = 0;
            $result['info'] = '该账号不存在';
            $this->ajaxReturn($result);
        }

        if ($password !== $info['password']) {
            $result['status'] = 0;
            $result['info'] = '密码错误';
            $this->ajaxReturn($result);
        }
        
        //判断设备号是否存在
        if ($info['registration_id'] && $info['registration_id'] != $registrationId) {
            $result['status'] = 0;
            $result['info'] = '账号已在其他设备上登录，请先退出原设备账号！';
            $this->ajaxReturn($result);
        }
        
        $map = array();
        $map['id'] = $info['engineer_id'];

        $data = array();
        $data['registration_id'] = $registrationId;

        if ($engineer->where($map)->save($data) === false) {
            $result['status'] = 0;
            $result['info'] = '工程师设备号保存失败';
            $this->ajaxReturn($result);
        }

        unset($info['password']);
        $url = 'http://www.shoujihuaile.com';
        $info['avatar'] = $info['avatar'] ? $url . $info['avatar'] : '';
        
        $result['status'] = 1;
        $result['userInfo'] = $info;
        
        $data = array();
        $data['engineer_id'] = $info['engineer_id'];
        $data['time'] = time();
        $data['action'] = '工程师登录app';
        M('engineer_action_log')->add($data);
        
        $this->ajaxReturn($result);
    }

    /**
     * 退出登录
     *
     * @return void
     */
    public function logout()
    {
        $engineerId = trim(I('param.engineerId'));
        $map = array();
        $map['id'] = $engineerId;
        $is_work = (int)M('engineer')->where($map)->getField('is_work');
        
        $data = array();
        $data['engineer_id'] = $engineerId;
        $data['time'] = time();
        $data['action'] = '工程师退出app';

        if (M('engineer_action_log')->add($data) === false) {
            $result['status'] = 0;
            $this->ajaxReturn($result);
        } else {
            $data = array();
            $data['registration_id'] = '';
            
            if (M('engineer')->where($map)->save($data) === false) {
                $result['status'] = 0;
                $this->ajaxReturn($result);
            } else {
                $result['status'] = 1;
                $result['is_work'] = $is_work;
                $this->ajaxReturn($result);
            }
        }
    }

    /**
     * 重置工程师密码
     *
     * @return void
     */
    public function resetPassword()
    {
        $engineerId = I('param.engineerId');
        $oldPassword = I('param.oldPassword');
        $newPassword = I('param.newPassword');
        
        if (!$newPassword) {
            $result['status'] = 0;
            $result['info'] = '新密码不能为空！';
            $this->ajaxReturn($result);
        }
        
        $result = array();
        $map = array();
        $map['id'] = $engineerId;
        $map['password'] = createPassword($oldPassword);
        
        $engineer = M('engineer');
        $info = $engineer->where($map)->field('id, password')->find();

        if (!$info) { //旧密码不正确
            $result['status'] = 0;
            $result['info'] = '旧密码输入错误，密码重置失败！';
            $this->ajaxReturn($result);
        }

        $data['password'] = createPassword($newPassword);

        if (M('engineer')->where($map)->save($data) !== false) {
            $result['status'] = 1;
            $result['info'] = '密码重置成功';

            $data = array();
            $data['engineer_id'] = $info['id'];
            $data['time'] = time();
            $data['action'] = '工程师重置密码';
            M('engineer_action_log')->add($data);
        } else {
            $result['status'] = 0;
            $result['info'] = '密码重置失败';
        }
        $this->ajaxReturn($result);
    }

    /**
     * 修改工程师工作状态
     *
     * @return void
     */
    public function isWork()
    {
        $engineerId = intval(I('param.engineerId'));
        $isWork = intval(I('param.isWork'));
        $YunTuId = intval(I('param.YunTuId'));
        $engineer = M('engineer');
        
        $map = array();
        $map['id'] = $engineerId;

        $engineerId = $engineer->where($map)->getField('id');
        $result = array();

        if (!$engineerId) {
            $result['status'] = 0;
            $result['info'] = '该账号不存在';
            $this->ajaxReturn($result);
        }
        
        $data = array();
        $data['is_work'] = $isWork;
        $data['yuntuid'] = $YunTuId;

        if ($engineer->where($map)->save($data) !== false) {
            $result['status'] = 1;

            if ($isWork == 1) {
                $result['info'] = '您已经开始接单了';

                $data = array();
                $data['engineer_id'] = $engineerId;
                $data['time'] = time();
                $data['action'] = '工程师开始接单';
                M('engineer_action_log')->add($data);
            } else {
                $result['info'] = '您已经收工了';

                $data = array();
                $data['engineer_id'] = $engineerId;
                $data['time'] = time();
                $data['action'] = '工程师收工';
                M('engineer_action_log')->add($data);
            }
        } else {
            $result['status'] = 0;
            $result['info'] = '联网后才能结单哦';
        }

        $this->ajaxReturn($result);
    }
    
    /**
     * 返回工程师工作状态
     *
     * @return void
     */
    public function getEngineerIsWork()
    {
        $engineerId = intval(I('param.engineerId'));
        
        $map = array();
        $map['id'] = $engineerId;
    
        $engineer = M('engineer')->where($map)->field('id, is_work, yuntuid')->find();
        
        if (!$engineer) {
            $result['status'] = 0;
            $this->ajaxReturn($result);
        }
        
        $result['status'] = 1;
        $result['YunTuId'] = $engineer['yuntuid'];
        $result['engineer_is_work'] = $engineer['is_work'];
        
        $this->ajaxReturn($result);
    }

    /**
     * 根据订单ID返回订单详细信息
     *
     * @return void
     */
    public function order()
    {
        $orderId = intval(I('param.orderId'));

        $result = array();

        if (empty($orderId)) {
            $result['status'] = 0;
            $result['data'] = '请输入订单ID';
            $this->ajaxReturn($result);
        }
        
        $map["o.id"] = $orderId;
        $order_info = M('order')->join('o left join customer c on o.customer_id = c.id')
                    ->field("o.id, o.number, o.status, o.type, o.reference_price, o.actual_price, o.color_id,
                            o.color, o.phone_name, o.customer_id, o.customer, o.cellphone, c.address,
                            o.create_time, o.maintain_start_time, o.engineer_remark, o.reference_price,
                            o.malfunction_description, o.remark, o.clearing_time, o.engineer_id, o.user_remark")
                    ->where($map)->find();

        if (empty($order_info)) {
            $result['status'] = 0;
            $result['data'] = '该订单不存在';
            $this->ajaxReturn($result);
        }
        
        $result_info = array();
        $con_result = array();
        
        $con_result['order_id'] = $order_info['id'];
        $con_result['order_number'] = $order_info['number'];
        $con_result['order_status'] = $order_info['status'];
        $con_result['order_type'] = $order_info['type'];
        $con_result['reference_price'] = $order_info['reference_price'];
        $con_result['actual_price'] = $order_info['actual_price'];
        $con_result['create_time'] = date('Y.m.d H:i:s', $order_info['create_time']);
        $con_result['phone_name'] = $order_info['phone_name'] . '  ' . $order_info['color'] . '  ';
        $con_result['is_maintain'] = $order_info['maintain_start_time'] ? 1 : 0;
        $con_result['user_remark'] = $order_info['user_remark'] ? $order_info['user_remark'] : '';
        $con_result['kefu_remark'] = $order_info['remark'] ? $order_info['remark'] : '';
        $con_result['engineer_remark'] = $order_info['engineer_remark'] ? $order_info['engineer_remark'] : '';
        $con_result['is_insurance'] = 0; //是否可以购买保险 0-不能 1-可以购买 2-已购买
        
        //订单是新单或保险单，已入库且在3天以内的订单
        if (in_array($order_info['type'], array(1, 2, 5)) && $order_info['status'] == 6 && $order_info['clearing_time'] + 259200 > time()) {
            
            if (M('phomal_insurance_order')->where(array('old_order_id' => $orderId, 'engineer_id' => $order_info['engineer_id'], 'status' => array('gt', 0)))->count()) {
                $con_result['is_insurance'] = 2;
            } else {
                
                if (!(M('order_partner')->where(array('order_id' => $orderId))->count())) {
                    
                    $insurance = M('order_phomal')->join('op left join phomal_insurance_phomals pip on op.phomal_id = pip.phomal_id')
                            ->join('left join phomal_insurance pi on pi.id = pip.phomal_insurance_id')
                            ->where(array('op.order_id' => $orderId, 'pi.id' => array('gt', 0), 'pi.status' => 1))->count();
                
                    if ($insurance) {
                        $con_result['is_insurance'] = 1;
                    }
                }
            }
        }
        
        if (strpos($order_info['phone_name'], 'iPhone') !== false) {
            $con_result['is_iphone'] = 1;
        } else {
            $con_result['is_iphone'] = 0;
        }

        //故障
        $malfunctions = M('order_phomal')->join('op left join phone_malfunction pm on op.phomal_id = pm.id')
                        ->field('pm.malfunction, pm.fitting, pm.is_color')
                        ->where(array('op.order_id' => $orderId))->select();
        
        $fittings = array();
        
        if (empty($malfunctions)) {
            $con_result['malfunctions'] = array($order_info['malfunction_description']);
        } else {
            
            //$price_list = M('fitting')->getField('id, price');
            $con_result['malfunctions'] = array();
            
            foreach ($malfunctions as $malfunction) {
                
                $con_result['malfunctions'][] = $malfunction['malfunction'];
                
                // 没有颜色，按原来的json解析
                if (!$malfunction['is_color']) {
                    $malfunction_fittings = json_decode($malfunction['fitting'], true);
            
                    foreach ($malfunction_fittings as $fitting) {
                        //$fitting['price'] = $price_list[$fitting['id']];
                        $fittings[] = $fitting;
                    }
                } else {
                    // 有颜色，读取订单的color_id去required_part里面去取相应的颜色值
                    $mal_list = json_decode($malfunction['fitting'], true);
            
                    foreach ($mal_list[$order_info['color_id']]['items'] as $fitting) {
                        //$fitting['price'] = $price_list[$fitting['id']];
                        $fittings[] = $fitting;
                    }
                }
            }
            
            //$con_result['malfunctions'] = implode(' + ', $con_result['malfunctions']);
        }
        
        $result_info['consume'] = $fittings;

        //客服和工程师备注
        $remarks = array();
        $remarks['kefu_remark'] = $order_info['remark'];
        $remarks['engineer_remark'] = $order_info['engineer_remark'];
        $remarks['user_remark'] = $order_info['user_remark'];
        $result_info['consume3'] = $remarks;

        //客户信息
        $con_result['customer_id'] = $order_info['customer_id'];
        $con_result['customer_name'] = $order_info['customer'];
        $con_result['customer_cellphone'] = $order_info['cellphone'];
        $con_result['customer_address'] = $order_info['address'];
        $result_info['consume2'] = $con_result;

        $result['status'] = 1;
        $result['data'] = $result_info;
        $this->ajaxReturn($result);
    }

    /**
     * 工程师添加订单备注
     *
     * @return void
     */
    public function engineerRemark()
    {
        $order_id = intval(I('param.orderId'));
        $remark = trim(I('param.remark'));

        $result = array();
        
        $data = array();
        $data['engineer_remark'] = $remark;
        
        $map = array();
        $map['id'] = $order_id;

        if (M('order')->where($map)->save($data) === false) {
            $result['status'] = 0;
            $result['info'] = '操作失败！';
            $this->ajaxReturn($result);
        } else {
            $result['status'] = 1;
            $result['info'] = '操作成功！';

            $engineer_id = M('order')->where($map)->getField('engineer_id');

            $data = array();
            $data['engineer_id'] = $engineer_id;
            $data['time'] = time();
            $data['action'] = '工程师添加备注, 订单ID--'.$order_id;
            M('engineer_action_log')->add($data);
            
            $action = '工程师添加备注--工程师ID:['.$engineer_id.']--备注:[' . $remark . ']';
            D('Admin/order')->writeLog($order_id, $action);
            
            $this->ajaxReturn($result);
        }
    }

    /**
     * 判断某个工程师是否有已结单但是尚未付款的订单
     *
     * @return void
     */
    public function getDueToPaid()
    {
        $engineerId = intval(I('param.engineerId'));

        if (empty($engineerId)) {
            $result['status'] = 0;
            $result['info'] = '请输入工程师ID';
            $this->ajaxReturn($result);
        }

        $count = M('order')->where(array('engineer_id' => $engineerId, 'status' => 5))->count();

        if ($count) {
            $result['status'] = 1;
            $this->ajaxReturn($result);
        } else {
            $result['status'] = 0;
            $this->ajaxReturn($result);
        }
    }

    /**
     * 获得某个工程师的等待中和处理中的订单列表
     *
     * @return void
     */
    public function getSpecificOrders()
    {
        $engineerId = intval(I('param.engineerId'));

        if (empty($engineerId)) {
            $result['status'] = 0;
            $result['data'] = '请输入工程师ID';
            $this->ajaxReturn($result);
        }

        $map = array();
        $map['o.engineer_id'] = $engineerId;
        $map['o.status'] = array('in', array(3, 4));
        
        $order_list = M('order')->join('o left join customer c on o.customer_id = c.id')
                    ->join('left join order_phomal opm on opm.order_id = o.id')
                    ->join('left join phone_malfunction pm ON opm.phomal_id = pm.id')
                    ->field('o.id, o.number, o.cellphone, o.type, o.create_time as time, o.actual_price as price, o.status, o.malfunction_description, 
                            o.phone_name, c.name as customer, c.address, group_concat(pm.malfunction) as malfunctions')
                    ->where($map)->group('o.id')->order('o.create_time desc')->select();

        if (empty($order_list)) {
            $result['status'] = 0;
            $result['info'] = '无订单';
            $this->ajaxReturn($result);
        }
        
        foreach ($order_list as &$order) {
            $order['time'] = date('H:i:s', $order['time']);
            
            if (!$order['malfunctions']) {
                $order['malfunctions'] = $order['malfunction_description'];
            }
        }

        $result['status'] = 1;
        $result['data'] = $order_list;
        $this->ajaxReturn($result);
    }


    /**
     * 工程师 立即上门
     *
     * @return void
     */
    public function orderHandleFour()
    {
        $orderId = intval(I('param.orderId'));
        $result = array();

        if (empty($orderId)) {
            $result['status'] = 0;
            $result['info'] = '请输入订单ID';
            $this->ajaxReturn($result);
        }
        
        $map['id'] = $orderId;
        $order_info = M('order')->where($map)->field('id, engineer_id, status')->find();

        if (empty($order_info)) {
            $result['status'] = 0;
            $result['info'] = '订单不存在';
            $this->ajaxReturn($result);
        }
        
        if (empty($order_info['engineer_id'])) {
            $result['status'] = 0;
            $result['info'] = '订单异常，请联系管理人员';
            $this->ajaxReturn($result);
        }
        
        if ($order_info['status'] >= 4) {
            $result['status'] = 0;
            $result['info'] = '订单已经被处理';
            $this->ajaxReturn($result);
        }
        
        $data['status'] = 4;
        $flag = true;
        
        M()->startTrans();
        
        if (M('order')->where($map)->save($data) === false) {
            $flag = false;
        }
        
        /** @todo 判断是不是葡萄生活的订单，如果是 */
        $partner = M('order_partner')->where(array('order_id' => $orderId))->find();
        D('Api/ThirdParty')->factory($partner['partner'], 'deal', array('orderId' => $orderId));
        
        $engineer_name = M('engineer')->where(array('id' => $order_info['engineer_id']))->getField('name');
        $log_data['order_id'] = $orderId;
        $log_data['time'] = time();
        $log_data['action'] = '操作人：'.$engineer_name.'--app--立即上门--状态：处理中';

        if (M('order_log')->add($log_data) === false) {
            $flag = false;
        }

        $data = array();
        $data['engineer_id'] = $order_info['engineer_id'];
        $data['time'] = time();
        $data['action'] = '工程师立即上门, 订单ID--'.$orderId;

        if (M('engineer_action_log')->add($data) === false) {
            $flag = false;
        }
        
        $result = array();
        
        if ($flag) {
            M()->commit();
            $result['status'] = 1;
            $result['info'] = '操作成功';
        } else {
            M()->rollback();
            $result['status'] = 0;
            $result['info'] = '操作失败';
        }
        $this->ajaxReturn($result);
    }
    
    /**
     * 工程师 维修 上传图片
     */
    public function orderHandleImg()
    {
        $orderId = intval(I('param.orderId'));
        
        $map['id'] = $orderId;
        $order_info = M('order')->where($map)->find();
        
        if (empty($order_info)) {
            $result['status'] = 0;
            $result['info'] = '订单不存在';
            $this->ajaxReturn($result);
        }
        
        if (empty($order_info['engineer_id'])) {
            $result['status'] = 0;
            $result['info'] = '订单异常，请联系管理人员';
            $this->ajaxReturn($result);
        }
        
        $info = $this->upload();
        
        if (!$info['success']) {
            $result['status'] = false;
            $result['info'] = $info['errorMsg'];
            $this->ajaxReturn($result);
        }
        
        $data = array();
        
        //开始维修图片
        if (isset($info['info']['startImg']['savepath'])) {
            $data['maintain_start_img'] = '/upload/' . $info['info']['startImg']['savepath'] . $info['info']['startImg']['savename'];
        }
        
        //结束维修图片
        if (isset($info['info']['endImg']['savepath'])) {
            $data['maintain_end_img'] = '/upload/' . $info['info']['endImg']['savepath'] . $info['info']['endImg']['savename'];
        }
        
        //维修单
        if (isset($info['info']['img']['savepath'])) {
            $data['maintain_img'] = '/upload/' . $info['info']['img']['savepath'] . $info['info']['img']['savename'];
        }
        
        if (!$data) {
            $result['status'] = 0;
            $result['info'] = '请上传图片';
            $this->ajaxReturn($result);
        }
        
        if (M('order')->where($map)->save($data) !== false) {
            $result['status'] = 1;
            $result['info'] = '上传成功';
        } else {
            $result['status'] = 0;
            $result['info'] = '上传失败（更新订单维修图片失败）';
        }
        
        $this->ajaxReturn($result);
    }

    /**
     * 工程师 开始维修
     *
     * @return void
     */
    public function orderHandleStart()
    {
        $orderId = intval(I('param.orderId'));
        $phoneImei = trim(I('param.phoneImei'));
        
        $map['id'] = $orderId;
        $order_info = M('order')->where($map)->find();
        
        if (empty($order_info)) {
            $result['status'] = 0;
            $result['info'] = '订单不存在';
            $this->ajaxReturn($result);
        }
        
        if (empty($order_info['engineer_id'])) {
            $result['status'] = 0;
            $result['info'] = '订单异常，请联系管理人员';
            $this->ajaxReturn($result);
        }
        
        $result = array();
        $data = array();
        $data['maintain_start_time'] = time();
        $data['phone_imei'] = $phoneImei;
        
        M()->startTrans();
        $flag = true;

        if (M('order')->where($map)->save($data) === false) {
            $flag = false;
        }

        $data = array();
        $data['engineer_id'] = $order_info['engineer_id'];
        $data['time'] = time();
        $data['action'] = '工程师开始维修, 订单ID--'.$orderId;

        if (M('engineer_action_log')->add($data) === false) {
            $flag = false;
        }
        
        if ($flag) {
            M()->commit();
            $result['status'] = 1;
            $result['info'] = '操作成功';
        } else {
            M()->rollback();
            $result['status'] = 0;
            $result['info'] = '操作失败';
        }

        if ($flag) {

            /** @todo 判断是不是雅生活的订单，雅订单需要通知雅生活APP推送消息 */
            $partner = M('order_partner')->where(array('order_id' => $orderId))->find();

            if (($partner['partner'] == 'yashenghuo')) {
                D('Api/ThirdParty')->factory($partner['partner'], 'orderProgress', array('orderNo' => $orderId));
            }

        }

        $this->ajaxReturn($result);
    }


    /**
     * 工程师点击结束维修弹出的确认按钮 判断物料扣除的是否准确提示
     *
     * @return void
     */
    public function confirmHandleStart()
    {
        $orderId = intval(I('param.orderId'));
        $result = array();

        if (empty($orderId)) {
            $result['status'] = 0;
            $result['info'] = '请输入订单ID';
            $this->ajaxReturn($result);
        }
        $map['id'] = $orderId;

        $order_info = M('order')->where($map)->field('color_id')->find();

        if (empty($order_info)) {
            $result['status'] = 0;
            $result['info'] = '订单不存在';
            $this->ajaxReturn($result);
        }

        $malfunction_list = M('order_phomal')->join('op left join phone_malfunction pm on op.phomal_id = pm.id')
                           ->field('pm.fitting, pm.is_color')
                           ->where(array('op.order_id' => $orderId))->select();

        $fittings = array();
        $fitting_names = array();
        
        foreach ($malfunction_list as $malfunction) {

            /** 没有颜色，按原来的json解析 */
            if (empty($malfunction['is_color'])) {
                $malfunction_fittings = json_decode($malfunction['fitting'], true);
                foreach ($malfunction_fittings as $fitting){
                    $fittings[] = $fitting;
                    $fitting_names[] = $fitting['name'];
                }
            } else {
                /** 有颜色，读取订单的color_id去fitting里面去取相应的颜色值 */
                $mal_list = json_decode($malfunction['fitting'], true);
                $malfunction_fittings = $mal_list[$order_info['color_id']]['items'];
                
                foreach ($malfunction_fittings as $fitting) {
                    $fittings[] = $fitting;
                    $fitting_names[] = $fitting['name'];
                }
            }
        }

        if (empty($fitting)) {
            $result['status'] = 1;
            $result['info'] = '请确认消耗的物料为空';
            $this->ajaxReturn($result);
        } else {
            $fitting_names = implode(',', $fitting_names);
            $result['status'] = 1;
            $result['info'] = '请确认消耗的物料为' . $fitting_names;
            $this->ajaxReturn($result);
        }
    }

    /**
     * 订单完成、订单日志、生成废料回收记录、扣除工程师物料库、扣除工程师预计消耗、生成工程师物料日志、生成工程师预计收益
     *
     * @return void
     */
    public function orderHandleFive()
    {
        $orderId = intval(I('param.orderId'));
        $result = array();

        if (empty($orderId)) {
            $result['status'] = 0;
            $result['info'] = '请输入订单ID';
            $this->ajaxReturn($result);
        }
        
        $map['id'] = $orderId;
        $order_info = M('order')->where($map)->field('id, status, color_id, engineer_id, actual_price, type, pay_type, paid_amount')->find();

        if (empty($order_info)) {
            $result['status'] = 0;
            $result['info'] = '订单不存在';
            $this->ajaxReturn($result);
        }
        
        if ($order_info['status'] >= 5) {
            $result['status'] = 0;
            $result['info'] = '订单已经完成';
            $this->ajaxReturn($result);
        }
        
        if ($order_info['type'] == 2 && $order_info['actual_price'] != 0) {
            $result['status'] = 0;
            $result['info'] = '返修单实际价格不为0，不能结单！';
            $this->ajaxReturn($result);
        }
        
        if ($order_info['type'] == 5 && $order_info['actual_price'] != 0) {
            $result['status'] = 0;
            $result['info'] = '保险单实际价格不为0，不能结单！';
            $this->ajaxReturn($result);
        }
        
        /** @todo 判断是不是葡萄生活的订单，葡萄订单是中间付费模式，需要先通知对方付款 */
        $partner = M('order_partner')->where(array('order_id' => $orderId))->find();
        
        if (($partner['partner'] == '葡萄生活') && $partner['is_paid'] != 1) {
        
            $newPrice = M('order')->where(array('id' => $orderId))->getField('actual_price');
    
            //判断是不是葡萄生活的订单，如果是
            $param = array('orderId' => $orderId, 'newPrice' => $newPrice);
            $output = D('Api/ThirdParty')->factory($partner['partner'], 'notifyOrderPrice', $param);
    
            if ($output && $output['status'] == 0) {
                $this->ajaxReturn($output);
            }
        }

        $order_data['status'] = 5;
        $order_data['end_time'] = time();
        $order_data['maintain_end_time'] = time();

        $flag = true;
        $error = array();
        
        M()->startTrans();

        if (M('order')->where($map)->save($order_data) === false) { //保存order表
            $flag = false;
            $error[] = '订单数据更新失败';
        }
        
        $engineer_info = M('engineer')->where(array('id' => $order_info['engineer_id']))->find();
        
        $order_log_data['order_id'] = $orderId;
        $order_log_data['time'] = time();
        $order_log_data['action'] = '操作人：'.$engineer_info['name'].'--app--结束维修--状态：结单';
        
        if (M('order_log')->add($order_log_data) === false) { //保存order_log表
            $flag = false;
            $error[] = '订单日志添加失败';
        }
        
        /** 第三方合作伙伴 */
        D('Api/ThirdParty')->factory($partner['partner'], 'deal', array('orderId' => $orderId));
        
        /** @todo判断是不是葡萄生活的订单，如果是 */
        if ($partner['partner'] == '葡萄生活' && $partner['is_paid'] == 1) {
            $param = array('orderId' => $orderId, 'orderStatus' => 12);
            $output = D('Api/Putao')->notifyOrderStatus($param);
    
            if ($output && $output['status'] == 0) {
                $flag = false;
                $error[] = $output['info'];
            }
    
            $order_pay['status'] = 6;
            $order_pay['clearing_time'] = time();
            $order_pay['payment_method'] = 1; //假设为支付宝吧
            $order_pay['is_clearing'] = 1;
            $order_pay['third_party_number'] = '葡萄生活';
    
            if (M('order')->where($map)->save($order_pay) === false) { //保存order表
                $flag = false;
                $error[] = '葡萄订单付款数据更新失败';
            }
            
            //计算工程师收益
            if (D('Admin/Engineer')->generateEngineerDivide($orderId) === false) {
                $flag = false;
                $error[] = '葡萄订单结单入库计算工程师收益失败';
            }
            
            $order_log_data = array();
            $order_log_data['order_id'] = $orderId;
            $order_log_data['time'] = time();
            $order_log_data['action'] = '工程师结束维修葡萄生活订单--自动入库--状态：入库';
            
            if (M('order_log')->add($order_log_data) === false) { //保存order_log表
                $flag = false;
                $error[] = '订单日志添加失败';
            }
        }

        $this->generateWasteApplyLog($orderId, $flag, $error);
        $this->deductEngineerFittings($order_info, $flag, $error);

        if (($order_info['type'] == 2) && ($order_info['actual_price'] == 0)) { //返修订单
            $order_pay['status'] = 6;
            $order_pay['clearing_time'] = time();
            $order_pay['payment_method'] = $order_info['payment_method'];
            $order_pay['is_clearing'] = 1;

            if (M('order')->where($map)->save($order_pay) === false) { //保存order表
                $flag = false;
                $error[] = '返修订单入库信息更新失败';
            }
            
            $order_log_data = array();
            $order_log_data['order_id'] = $orderId;
            $order_log_data['time'] = time();
            $order_log_data['action'] = '操作人：'.$engineer_info['name'].'--app--结束维修--返修单自动入库--状态：入库';
            
            if (M('order_log')->add($order_log_data) === false) { //保存order_log表
                $flag = false;
                $error[] = '订单日志添加失败';
            }
            
            //计算工程师收益
            if (D('Admin/Engineer')->generateEngineerDivide($orderId) === false) {
                $flag = false;
                $error[] = '订单入库计算工程师收益失败';
            }                
        } else if (($order_info['type'] == 5) && ($order_info['actual_price'] == 0)) { //保险订单
            
            $order_pay['id'] = $orderId;
            $order_pay['status'] = 6;
            $order_pay['clearing_time'] = time();
            $order_pay['payment_method'] = 1;
            $order_pay['is_clearing'] = 1;

            $rst = D('Admin/order')->stock($order_pay, false);
            
            if (!$rst['success']) { //保存order表
                $flag = false;
                $error[] = '保险订单入库信息更新失败';
            }
            
            $order_log_data = array();
            $order_log_data['order_id'] = $orderId;
            $order_log_data['time'] = time();
            $order_log_data['action'] = '操作人：'.$engineer_info['name'].'--app--结束维修--保险单自动入库--状态：入库';
            
            if (M('order_log')->add($order_log_data) === false) { //保存order_log表
                $flag = false;
                $error[] = '订单日志添加失败';
            }
        } else if ($order_info['pay_type'] == 2 && $order_info['actual_price'] == $order_info['paid_amount']) { /** 预付订单 */
            $param = array();
            $param['id'] = $orderId;

            $rst = D('Admin/order')->stock($param, false);
            
            if (!$rst['success']) {
                $flag = false;
                $error[] = '预付单入库信息更新失败';
            }
            
            $order_log_data = array();
            $order_log_data['order_id'] = $orderId;
            $order_log_data['time'] = time();
            $order_log_data['action'] = '操作人：'.$engineer_info['name'].'--app--结束维修--预付单自动入库--状态：入库';
            
            if (M('order_log')->add($order_log_data) === false) {
                $flag = false;
                $error[] = '订单日志添加失败';
            }
        }

        if ($flag) {
            M()->commit();
            
            if ($partner && $partner['partner'] == '葡萄生活') {
                $result['is_partner'] = 1;
            }
            
            $result['status'] = 1;
            $result['info'] = '操作成功';

            $data = array();
            $data['engineer_id'] = $order_info['engineer_id'];
            $data['time'] = time();
            $data['action'] = '工程师结束维修, 订单ID--'.$orderId;
            M('engineer_action_log')->add($data);

            $this->ajaxReturn($result);
        } else {
            M()->rollback();
            $result['status'] = 0;
            $result['info'] = empty($error[0]) ? '结束维修失败': $error[0];
            $this->ajaxReturn($result);
        }
    }

    /**
     * 结单时产生废料记录
     *
     * @param $order_id
     * @param $flag
     * @param $error
     * @return void
     */
    private function generateWasteApplyLog($order_id, &$flag, &$error)
    {
        // 查询该订单产生的废料
        $malfunction_list = M('phone_malfunction')->join('pm left join order_phomal op on pm.id = op.phomal_id')
                            ->join('left join `order` o on o.id = op.order_id')
                            ->where(array('op.order_id' => $order_id))->field('pm.waste, o.phone_id, o.phone_name')->select();
        $wastes = array();

        foreach ($malfunction_list as $malfunction) {
            $waste_array = json_decode($malfunction['waste'], true);

            foreach($waste_array as $waste_info) {
                
                $waste_info['phone_id'] = $malfunction['phone_id'];
                $waste_info['phone'] = $malfunction['phone_name'];
                $waste_info['waste_id'] = $waste_info['id'];
                unset($waste_info['id']);
                
                $wastes[] = $waste_info;
            }
        }
        
        //生成废料申请记录
        if ($wastes) {
            $map['id'] = $order_id;
            $engineer_id = M('order')->where($map)->getField('engineer_id');
            
            $engineer_map['id'] = $engineer_id;
            $organization_id = M('engineer')->where($engineer_map)->getField('organization_id');
            
            $waste_data = array();
            $waste_data['engineer_id'] = $engineer_id;
            $waste_data['user_id'] = 0;
            $waste_data['organization_id'] = $organization_id;
            $waste_data['order_id'] = $order_id;
            $waste_data['wastes'] = json_encode($wastes);
            $waste_data['status'] = 0;
            $waste_data['time'] = time();
            
            if (M('waste_refund')->add($waste_data) === false) {
                $flag = false;
                $error[] = '废料记录添加失败';
            }
        }
    }

    /**
     * 扣除工程师物料和预计消耗
     *
     * @param $order_id
     * @param $flag
     * @param $error
     * @return void
     */
    private function deductEngineerFittings($orderInfo, &$flag, &$error)
    {
        $orderId = $orderInfo['id'];
        $colorId = $orderInfo['color_id'];
        
        /** 查询该订单所需配件，在工程师物料里解除锁定 */
        $malfunction_list = M('phone_malfunction')->join('pm left join order_phomal op on pm.id = op.phomal_id')
                            ->where(array('op.order_id' => $orderId))->field('pm.fitting, pm.is_color')->select();
        
        $fittings = array();
        
        foreach ($malfunction_list as $malfunction) {
            /** 没有颜色，按原来的json解析 */
            if (empty($malfunction['is_color'])) {
                $malfunction_fittings = json_decode($malfunction['fitting'], true);
                
                foreach ($malfunction_fittings as $fitting){
                    $fittings[] = $fitting;
                }
            } else {
                /** 有颜色，读取订单的color_id去fitting里面去取相应的颜色值 */
                $mal_list = json_decode($malfunction['fitting'], true);
                $malfunction_fittings = $mal_list[$colorId]['items'];
                
                foreach ($malfunction_fittings as $fitting){
                    $fittings[] = $fitting;
                }
            }
        }
        
        if (count($fittings) >= 1) {
            $item = array();
            $item['engineer_id'] = $orderInfo['engineer_id'];
            $item['order_id'] = $orderId;
            $item['user_id'] = 0;
            $item['type'] = 1;
            $item['inout'] = 2;
            $item['time'] = time();
        
            foreach ($fittings as $key => $value) {
                $item['fittings_id'] = $value['id'];
                $item['name'] = $value['name'];
                $item['amount'] = $value['amount'];
        
                /** 减少工程师库存 */
                if ($this->reduceEngineerWarehouse($item, $flag, $error) === false) {
                    break;
                }
            }
        }
        
        return true;
    }
    
    /**
     * 减少工程师物料库存数量
     *
     * @return void
     */
    private function reduceEngineerWarehouse($data, &$flag, &$error)
    {
        $map = array();
        $map['fittings_id'] = $data['fittings_id'];
        $map['engineer_id'] = $data['engineer_id'];
        $current = M('engineer_warehouse')->where($map)->find();

        /** 判断是否存在该配件 */
        if (!$current) {
            $flag = false;
            $error[] = '减少工程师库存错误(配件不存在)';
            return false;
        }

        /** 判断配件数量是否充足 */
        if (($current['amount'] - $data['amount']) < 0) {
            $flag = false;
            $error[] = '减少工程师库存错误(配件数量不足)';
            return false;
        }

        /** 更新库存数量 */
        $item = array();
        $item['amount'] = $current['amount'] - $data['amount'];

        if (M('engineer_warehouse')->where($map)->save($item) === false) {
            $flag = false;
            $error[] = '减少工程师库存错误(更新数据)';
            return false;
        }

        /** 变更日志 查找最先进入的物料 */
        $map = array();
        $map['engineer_id'] = $data['engineer_id'];
        $map['fitting_id'] = $data['fittings_id'];
        $map['order_id'] = 0;
        $map['status'] = 3;
        $fittings = M('stock')->where($map)->order('id asc')->limit(0, $data['amount'])->getField('id, fitting_id');
        
        if (empty($fittings)) {
            $flag = false;
            $error[] = '减少工程师库存错误(配件不存在)';
            return false;
        }
        
        if (count($fittings) != $data['amount']) {
            $flag = false;
            $error[] = '减少工程师库存错误(配件数量不足)';
            return false;
        }
        
        /** 更新工程师物料库存 */
        $map = array('id' => array('in', array_keys($fittings)));
        
        if (M('stock')->where($map)->save(array('order_id' => $data['order_id'], 'status' => 4, 'consume_time' => time())) === false) {
            $flag = false;
            $error[] = '减少工程师库存错误(更新数据)';
            return false;
        }
        
        /** 出库日志 */
        $item = array();
        $item['engineer_id'] = $data['engineer_id'];
        $item['fittings_id'] = $data['fittings_id'];
        $item['admin_id'] = $data['user_id'];
        $item['type'] = $data['type'];
        $item['inout'] = $data['inout'];
        $item['time'] = $data['time'];
        $item['order_id'] = $data['order_id'];
        $item['amount'] = $data['amount'];
        
        if (M('engineer_inout')->add($item) === false) {
            $flag = false;
            $error[] = '减少工程师库存错误(插入日志)';
            return false;
        }
        
        return true;
    }

    /**
     * 计算工程师收益
     *
     * @param $order_id
     * @param $flag
     * @param $error
     * @return void
     */
    private function generateEngineerDivide($orderId, &$flag, &$error)
    {
        /** 订单 -> 工程师 -> 故障 -> 收益*/
        $map = array();
        $map['id'] = $orderId;
        $order = M('order')->join('o left join order_phomal opm on opm.order_id = o.id')
                ->join('left join phone_malfunction pm on opm.phomal_id = pm.id')
                ->field('o.id, o.number, o.phone_name, o.color, o.engineer_id, group_concat(pm.malfunction) as malfunctions')
                ->where($map)->group('o.id')->find();

        /** 工程师 */
        $map = array();
        $map['e.id'] = $order['engineer_id'];
        $engineer = M('engineer')->join('e left join engineer_level el on el.id = e.level')
                    ->field('e.type, el.divide')->where($map)->find();

        if ($engineer['type'] == 2) {
            $divide = 'divide_platform';
        } else {
            $divide = 'divide_local';
        }

        /** 故障 -> 收益*/
        $divide = M('phone_malfunction')->join('pm left join order_phomal op on op.phomal_id = pm.id')
                ->where(array('op.order_id' => $orderId))->sum($divide);

        /** 写入数据 */
        $data = array();
        $data['order_id'] = $order['id'];
        $data['order_number'] = $order['number'];
        $data['order_name'] = $order['phone_name'] . '-' . $order['color'] . '-' . $order['malfunctions'];
        $data['engineer_id'] = $order['engineer_id'];
        $data['divide'] = $engineer['divide'];
        $data['earning'] = $divide * $engineer['divide'] / 100;

        if (M('engineer_divide')->add($data) === false) {
            $flag = false;
            $error[] = '工程师收益添加失败';
        }
    }

    /**
     * 已完成的历史订单
     *
     * @return void
     */
    public function orderLog()
    {
        $engineerId = intval(I('param.engineerId'));
        $page = trim(I('param.page')) ? trim(I('param.page')) : 1;

        $result = array();

        if (empty($engineerId)) {
            $result['status'] = 0;
            $result['data'] = '请输入工程师ID';
            $this->ajaxReturn($result);
        }

        $start_id = ($page - 1) * 10;
        $map = array();
        $map['engineer_id'] = $engineerId;
        $map['status'] = array('egt', 5);
        
        $order_list = M('order')->join('o left join order_phomal opm on opm.order_id = o.id')
                    ->join('left join phone_malfunction pm on opm.phomal_id = pm.id')
                    ->field('o.id as order_id, o.status as order_status, o.actual_price, o.end_time, o.customer, o.number,
                            o.phone_name, o.type, IFNULL(group_concat(pm.malfunction), o.malfunction_description) as malfunctions')
                    ->where($map)->group('o.id')->order('o.status asc, o.end_time desc')->limit("{$start_id}, 10")->select();

        if (!$order_list) {
            $result['status'] = 0;
            $result['data'] = '订单数量为空';
        } else {
            $result['status'] = 1;
            $result['data'] = $order_list;
        }
        
        $this->ajaxReturn($result);
    }

    /**
     * 查询工程师现有物料
     *
     * @return void
     */
    public function warehouse()
    {
        $engineerId = intval(I('param.engineerId'));
        $page = trim(I('param.page')) ? trim(I('param.page')) : 1;
        
        if (empty($engineerId)) {
            $result['status'] = 0;
            $result['info'] = '请输入工程师ID';
            $this->ajaxReturn($result);
        }

        $start_id = ($page - 1) * 10;
        $map['ew.engineer_id'] = $engineerId;
        $map['ew.amount'] = array('gt', 0);
        $list = M('engineer_warehouse')->join('ew left join fitting f on ew.fittings_id = f.id')
                ->field('ew.fittings_id, ew.fittings_name, ew.amount, f.price as price_engineer')
                ->where($map)->limit($start_id, 10)->select();

        $final_list = array();
        
        foreach ($list as &$fitting) {
            $fitting['phone_name'] = $this->getFittingsPhone($fitting['fittings_id']);
            $final_list[] = $fitting;
        }
        $result = array();

        if ($list) {
            $result['status'] = 1;
            $result['data'] = $final_list;
        } else {
            $result['status'] = 0;
            $result['info'] = '您当前没有物料';
        }
        
        $this->ajaxReturn($result);
    }

    /**
     * 获得某种物料的机型 格式化
     *
     * @param $fitting_id
     * @return string
     */
    private function getFittingsPhone($fitting_id) {

        /** 配件对应机型 */
        $rst = M('phone_fitting')->join('pf left join phone p on pf.phone_id=p.id')
                ->field('pf.phone_id, p.alias, pf.fitting_id')
                ->where(array('pf.fitting_id' => $fitting_id))->select();
        $phones = array();

        foreach ($rst as $key => $value) {
            $phones[$value['fitting_id']][] = $value['alias'];
        }

        $phone_all = $phones[$fitting_id]; //机型别名集合
        $str = trim($phone_all[0]);
        
        if (strpos($str, '6 Plus') !== false) {
            $phone_all[0] = str_replace('6 Plus', '6P', $str);
        }
        
        if (strpos($str, '6s Plus') !== false) {
            $phone_all[0] = str_replace('6s Plus', '6s P', $str);
        }
        
        $brand = is_int(strpos($str, ' ')) ? substr($str, 0, (strpos($str, ' ') + 1)) : '';
        
        for ($i = 1; $i < count($phone_all); $i ++) {
        
            if (strpos($phone_all[$i], '6 Plus') !== false) {
                $phone_all[$i] = str_replace('6 Plus', '6P', $phone_all[$i]);
            }
        
            if (strpos($phone_all[$i], '6s Plus') !== false) {
                $phone_all[$i] = str_replace('6s Plus', '6s P', $phone_all[$i]);
            }
        
            $this_pos = strpos($phone_all[$i], ' ');
            $this_brand = substr($phone_all[$i], 0, $this_pos + 1);
        
            if ($this_brand == $brand) {
                $phone_all[$i] = str_replace($brand, '', $phone_all[$i]);
            }
        }
        
        $str = implode($phone_all, '/');
        return $str;
    }

    /**
     * 获得某种废料的机型 格式化
     *
     * @param $waste_id
     * @return string
     */
    private function getWastesPhone($waste_id) 
    {
        /** 配件对应机型 */
        $rst = M('phone_waste')->join('pw left join phone p on pw.phone_id=p.id')
                ->field('pw.phone_id, p.alias, pw.waste_id')
                ->where(array('pw.waste_id' => $waste_id))->select();
        
        $phones = array();

        foreach ($rst as $key => $value) {
            $phones[$value['waste_id']][] = $value['alias'];
        }

        $phone_all = $phones[$waste_id]; //机型别名集合
        $str = trim($phone_all[0]);
        
        if (strpos($str, '6 Plus') !== false) {
            $phone_all[0] = str_replace('6 Plus', '6P', $str);
        }

        if (strpos($str, '6s Plus') !== false) {
            $phone_all[0] = str_replace('6s Plus', '6s P', $str);
        }

         $brand = is_int(strpos($str, ' ')) ? substr($str, 0, (strpos($str, ' ') + 1)) : '';

        for ($i = 1; $i < count($phone_all); $i ++) {

            if (strpos($phone_all[$i], '6 Plus') !== false) {
                $phone_all[$i] = str_replace('6 Plus', '6P', $phone_all[$i]);
            }

            if (strpos($phone_all[$i], '6s Plus') !== false) {
                $phone_all[$i] = str_replace('6s Plus', '6s P', $phone_all[$i]);
            }

            $this_pos = strpos($phone_all[$i], ' ');
            $this_brand = substr($phone_all[$i], 0, $this_pos + 1);
            
            if ($this_brand == $brand) {
                $phone_all[$i] = str_replace($brand, '', $phone_all[$i]);
            }
        }
        
        $str = implode($phone_all, '/');
        return $str;
    }

    /**
     * 判断是不是被订单锁定 返回锁定的数量 没有锁定返回0
     *
     * @param $fitting_id
     * @param $engineer_id
     * @return int
     */
    private function orderLockNum($fitting_id, $engineer_id)
    {
        $num = 0;
        $map = array();
        $map['engineer_id'] = $engineer_id;
        $map['status'] = array('lt', 5);
        $map['status'] = array('neq', -1);
        $order_list = M('order')->where($map)->field('id, color_id')->select();
        
        foreach ($order_list as $order) {
            
            $malfunction_list = M('order_phomal')->join('op left join phone_malfunction pm on opm.phomal_id = pm.id')
                                ->field('pm.fitting, pm.is_color')->where(array('op.order_id'=>$order['id']))->select();

            foreach ($malfunction_list as $malfunction) {
                
                if (empty($malfunction['is_color'])) { //没有颜色，按原来的json解析
                    $malfunction_fittings = json_decode($malfunction['fitting'], true);
                    
                    foreach ($malfunction_fittings as $fitting) {
                        
                        if ($fitting['id'] == $fitting_id) {
                            $num += $fitting['amount'];
                        }
                    }
                } else { //有颜色，读取订单的color_id去required_part里面去取相应的颜色值
                    $mal_list = json_decode($malfunction['fitting'], true);
                    $malfunction_fittings = $mal_list[$order['color_id']]['items'];

                    foreach ($malfunction_fittings as $fitting) {
                        
                        if ($fitting['id'] == $fitting_id) {
                            $num += $fitting['amount'];
                        }
                    }
                }
            }
        }
        return $num;
    }

    /**
     * 判断是不是被退还中锁定 返回锁定的数量 没有锁定返回0
     *
     * @param $fitting_id
     * @param $engineer_id
     * @return int
     */
    private function refundLockNum($fitting_id, $engineer_id)
    {
        $num = 0;
        $map = array();
        $map['engineer_id'] = $engineer_id;
        $map['type'] = 2;
        $map['status'] = 1;
        $list = M('apply')->where($map)->select();
        
        foreach ($list as $log) {
            $fittings_array = json_decode($log['fittings'], true);
            
            foreach ($fittings_array as $fitting) {
                
                if ($fitting['id'] == $fitting_id) {
                    $num += $fitting['amount'];
                }
            }
        }
        return $num;
    }

    /**
     * 获得所有机型或者某个工程师现有物料所属的所有机型
     *
     * @return void
     */
    public function getEngineerPhones()
    {
        $engineerId = intval(I('param.engineerId'));
        $result['status'] = 1;
        
        if (empty($engineerId)) {
            $sql_phone = "select p.id as phone_id, p.alias as phone_alias from phone p 
                          order by p.alias asc";
            $return_list = M()->query($sql_phone);
        } else {
            $sql = "select distinct p.id as phone_id, p.alias as phone_alias from phone p
                    left join phone_fitting pf on p.id = pf.phone_id
                    left join fitting f on pf.fitting_id = f.id
                    left join engineer_warehouse ew on f.id = ew.fittings_id
                    where ew.engineer_id = {$engineerId} and ew.amount > 0";
            $return_list = M()->query($sql);
        }
        $result['data'] = $return_list;
        $this->ajaxReturn($result);
    }

    /**
     * 获得某种机型的配件列表
     *
     * @return void
     */
    public function getPhoneFittings()
    {
        $phoneId = intval(I('param.phoneId'));
        $engineerId = intval(I('param.engineerId'));
        if (empty($phoneId)) {
            $result['status'] = 0;
            $result['info'] = '请输入机型ID';
            $this->ajaxReturn($result);
        }

        if (!empty($engineerId)) {
            $where = array();
            $where['pf.phone_id'] = $phoneId;
            $where['ew.engineer_id'] = $engineerId;
            $where['ew.amount'] = array('gt', 0);
            $fitting_list = M('fitting')->join('f left join phone_fitting pf on f.id = pf.fitting_id')
                            ->join('left join engineer_warehouse ew on ew.fittings_id = f.id')
                            ->field('distinct f.id, f.title, f.price as price_engineer, ew.amount')
                            ->where($where)->select();
        } else {
            $where = array();
            $where['pf.phone_id'] = $phoneId;
            $fitting_list = M('fitting')->join('f left join phone_fitting pf on f.id = pf.fitting_id')
                            ->field('distinct f.id, f.title, f.price as price_engineer')
                            ->where($where)->select();
        }
        
        if (empty($fitting_list)) {
            $result['status'] = 0;
            $result['info'] = '该机型没有配件';
            $this->ajaxReturn($result);
        }
        
        $result['status'] = 1;
        $result['data'] = $fitting_list;
        $this->ajaxReturn($result);
    }

    /**
     * 返回申请者还可以申请的物料价值
     *
     * @return void
     */
    public function remainApplyWorth()
    {
        $engineerId = intval(I('param.engineerId'));

        if (empty($engineerId)) {
            $result['status'] = 0;
            $result['info'] = '请输入工程师ID';
            $this->ajaxReturn($result);
        }

        if (M('engineer')->where(array('id' => $engineerId))->getField('status') != 1) {
            $result['status'] = 1;
            $result['worth'] = 0;
            $this->ajaxReturn($result);
        }

        $map = array('e.id' => $engineerId);


        $max_worth = M('engineer_level')->join('el left join engineer e on e.level = el.id')->where($map)->getField('quota');

        //身上持有物料的价值
        $owned_worth = 0;
        $map = array('engineer_id' => $engineerId);
        $owned_worth = M('engineer_warehouse')->join('ew left join fitting f on ew.fittings_id = f.id')
                                ->where($map)->sum("ew.amount * f.price");

        //申请，并且审核通过的物料的价值
        $map['status'] = array('in', array(0, 1));
        $map['type'] = 1;
        $apply_worth = M('apply')->where($map)->sum('worth');

        /** 未退回废料订单物料价值 */
        $wasteLockWorth = 0;
        $map = array();
        $map['wr.status'] = 0;
        $map['wr.engineer_id'] = $engineerId;
        $wasteLockWorth = M('waste_refund')->join('wr left join `engineer_inout` ei on wr.order_id=ei.order_id left join `fitting` f on ei.fittings_id=f.id')
            ->where($map)->sum("ei.amount * f.price");
        $wasteLockWorth = round($wasteLockWorth);
        
        /** 剩余额度 */
        $remainWorth = round($max_worth - $owned_worth - $apply_worth - $wasteLockWorth);
        
        if ($remainWorth < 0) {
            $remainWorth = 0;
        }
        
        $result['status'] = 1;
        $result['worth'] = $remainWorth;
        $this->ajaxReturn($result);
    }

    /**
     * 申请物料、物料退还公用 一个接口，根据type参数区分
     *
     * @return void
     */
    public function apply()
    {
        $engineerId = intval(I('param.engineerId'));
        $type = intval(I('param.type', 1));
        $fittings = trim($_REQUEST['data']); //如果用I方法json格式会被修改

        if (empty($engineerId)) {
            $result['status'] = 0;
            $result['info'] = '请输入工程师ID';
            $this->ajaxReturn($result);
        }

        if (empty($fittings)) {
            $result['status'] = 0;
            $result['info'] = '请输入申请的物料ID、名称和数量';
            $this->ajaxReturn($result);
        }
        
        if ($type == 1) { //申请物料
            $map = array('e.id' => $engineerId);
            $max_worth = M('engineer_level')->join('el left join engineer e on e.level = el.id')->where($map)->getField('quota');
            
            //身上持有物料的价值
            $owned_worth = 0;
            $map = array('engineer_id' => $engineerId);
            $owned_worth = M('engineer_warehouse')->join('ew left join fitting f on ew.fittings_id = f.id')
                            ->where($map)->sum("ew.amount * f.price");
            
            //申请，并且审核通过的物料的价值
            $map['status'] = 1;
            $map['type'] = 1;
            $apply_worth = M('apply')->where($map)->sum('worth');
            
            $remainWorth = round($max_worth - $owned_worth - $apply_worth);
            
            if ($remainWorth <= 0) {
                $result['status'] = 0;
                $result['info'] = '当前可申请物料额度不足';
                $this->ajaxReturn($result);
            }
            
            $this->applyFittings($engineerId, $fittings);
        } else if ($type == 2) { //退还物料
            $this->refundFittings($engineerId, $fittings);
        }
    }

    /**
     * 申请物料，写入apply
     *
     * @param $engineerId
     * @param $fittings
     * @return bool
     */
    private function applyFittings($engineerId, $fittings)
    {
        $engineer_map = array('id' => $engineerId);
        $organization_id = M('engineer')->where($engineer_map)->getField('organization_id');

        $fittings = json_decode($fittings, true);
        
        $apply_worth = 0;
        
        $fitting_ids = array();
        $phone_ids = array();

        foreach ($fittings as $key => $value) {
            $map = array('fitting_id' => $value['fitting_id'], 'phone_id' => $value['phone_id']);
            
            if (!(M('phone_fitting')->where($map)->count())) {
                $result['status'] = 0;
                $result['info'] = $value['phone'] . ' 不存在 ' . $value['fitting'] . ' 配件, 请联系管理员！';
                $this->ajaxReturn($result);
            }
            
            $map = array('fitting_id' => $value['fitting_id'], 'organization_id' => $organization_id);
            $amount = M('warehouse')->where($map)->getField('amount');
            
            if (empty($amount) || $amount < $value['amount']) {
                $result['status'] = 0;
                $result['info'] = $value['phone'] . ' ' . $value['fitting'] . '缺货中, 请稍后重试！';
                $this->ajaxReturn($result);
            }
            
            $fitting_ids[] = $value['fitting_id'];
            $phone_ids[] = $value['phone_id'];
            
            $apply_worth += ($value['price'] * $value['amount']);
        }
        
        //从数据库中取出机型与配件的名称，避免数据传输错误问题
        if ($phone_ids && $fitting_ids) {
            $phone_names = M('phone')->where(array('id' => array('in', $phone_ids)))->getField('id, alias');
            $fitting_names = M('fitting')->where(array('id' => array('in', $fitting_ids)))->getField('id, title');
            
            foreach ($fittings as &$val) {
                isset($phone_names[$val['phone_id']]) && $val['phone'] = $phone_names[$val['phone_id']];
                isset($fitting_names[$val['fitting_id']]) && $val['fitting'] = $fitting_names[$val['fitting_id']];
            }
        }

        $data = array();
        $data['fittings'] = json_encode($fittings);
        $data['engineer_id'] = $engineerId;
        $data['time'] = time();
        $data['organization_id'] = $organization_id;
        $data['type'] = 1;
        $data['worth'] = $apply_worth;
        $data['status'] = 0;
  
        if (M('apply')->add($data) === false) {
            $result['status'] = 0;
            $result['info'] = '申请记录写入失败，申请失败';
            $this->ajaxReturn($result);
        }

        $result['status'] = 1;
        $result['info'] = '申请成功，请等待仓库处理';
        $this->ajaxReturn($result);
    }

    /**
     * 退还物料，写入apply_
     *
     * @param $engineerId
     * @param $fittings
     * @return bool
     */
    private function refundFittings($engineerId, $fittings)
    {
        $map = array('id' => $engineerId);
        $organization_id = M('engineer')->where($map)->getField('organization_id');
        
        $fittings = json_decode($fittings, true);

        $apply_worth = 0;

        foreach ($fittings as $key => $value) {
            
            $map = array('fitting_id' => $value['fitting_id'], 'organization_id' => $organization_id);
            $item = M('warehouse')->where($map)->find();
            
            /** 这种情况很少发生 */
            if (empty($item)) {
                $result['status'] = 0;
                $result['info'] = '仓库不存在' . $value['phone_name'] . ' '. $value['fitting_name'] . '，退还失败';
                $this->ajaxReturn($result);
            }
            
            $map = array('fittings_id' => $value['fitting_id'], 'engineer_id' => $engineerId);
            $amount = M('engineer_warehouse')->where($map)->getField('amount');

            if ($amount < $value['amount']) {
                $result['status'] = 0;
                $result['info'] = '您当前的 ' . $value['phone_name'] . ' '. $value['fitting_name'] . ' 数量小于' . $value['amount'] . '，退还失败';
                $this->ajaxReturn($result);
            }

            $apply_worth += ($value['price'] * $value['amount']);
        }

        $data = array();
        $data['organization_id'] = $organization_id;
        $data['time'] = time();
        $data['fittings'] = json_encode($fittings);
        $data['engineer_id'] = $engineerId;
        $data['type'] = 2;
        $data['worth'] = $apply_worth;
        $data['status'] = 0;

        if (M('apply')->add($data) === false) {
            $result['status'] = 0;
            $result['info'] = '退还记录写入失败，退还失败';
            $this->ajaxReturn($result);
        }

        $result['status'] = 1;
        $result['info'] = '退还成功，请等待仓库处理';
        $this->ajaxReturn($result);
    }

    /**
     * 工程师申请物料历史记录
     *
     * @return void
     */
    public function applyLog()
    {
        $engineerId = intval(I('param.engineerId'));
        $page = trim(I('param.page')) ? trim(I('param.page')) : 1;

        $month_now = date('Y-m');
        $month = trim(I('param.month')) ? trim(I('param.month')) : $month_now;
        $result = array();

        if (empty($engineerId)) {
            $result['status'] = 0;
            $result['info'] = '请输入工程师ID';
            $this->ajaxReturn($result);
        }

        $first_day = date('Y-m-01', strtotime($month));
        $first_day_time = strtotime($first_day);
        $last_day = date('Y-m-d', strtotime("$first_day +1 month -1 day"));
        $last_day = date('Y-m-d', strtotime("$last_day +1 day"));
        $last_day_time = strtotime($last_day);
        $last_day_time -= 1;

        $start_id = ($page - 1) * 10;
        $sql = "select * from apply where engineer_id = {$engineerId} 
                and time >= $first_day_time and time <= $last_day_time 
                and `status` > -2
                order by time desc limit {$start_id}, 10";
        $list = M()->query($sql);

        if (empty($list)) {
            $result['status'] = 0;
            $result['info'] = '申请记录为空';
            $this->ajaxReturn($result);
        }

        $result_list = array();
        
        foreach ($list as $log) {
            $result_info = array();
            $fittings = json_decode($log['fittings'], true);

            if (is_null($fittings)) {
                continue;
            }

            foreach ($fittings as &$fitting) {
                $fitting['phone_name'] = $this->getFittingsPhone($fitting['fitting_id']);
            }
            
            $result_info['applyId'] = $log['id'];
            $result_info['fittings'] = $fittings;
            $result_info['worth'] = $log['worth'];
            $result_info['status'] = $log['status'];
            $result_info['time'] = date('Y-m-d', $log['time']);
            $result_info['type'] = $log['type'];
            $result_list[] = $result_info;
        }
        $result['status'] = 1;
        $result['data'] = $result_list;

        $this->ajaxReturn($result);
    }

    /**
     * 工程师退还废料记录
     *
     * @return void
     */
    public function waste()
    {
        $engineerId = intval(I('param.engineerId'));
        $page = trim(I('param.page')) ? trim(I('param.page')) : 1;
        $month_now = date('Y-m');
        $month = trim(I('param.month')) ? trim(I('param.month')) : $month_now;

        $first_day = date('Y-m-01', strtotime($month));
        $first_day_time = strtotime($first_day);
        $last_day = date('Y-m-d', strtotime("$first_day +1 month -1 day"));
        $last_day = date('Y-m-d', strtotime("$last_day +1 day"));
        $last_day_time = strtotime($last_day);
        $last_day_time -= 1;

        $result = array();

        if (empty($engineerId)) {
            $result['status'] = 0;
            $result['info'] = '请输入工程师ID';
            $this->ajaxReturn($result);
        }
        
        $start_id = ($page - 1) * 10;
        $sql = "select * from waste_refund where engineer_id = {$engineerId} 
                and time >= $first_day_time and time <= $last_day_time 
                order by time desc limit {$start_id}, 10";
        $list = M()->query($sql);

        if (empty($list)) {
            $result['status'] = 0;
            $result['info'] = '没有退还记录';
            $this->ajaxReturn($result);
        }

        $data = array();
        
        foreach ($list as $waste) {
            $result_info = array();
            $wastes = json_decode($waste['wastes'], true);

            $worth = 0;
            
            foreach ($wastes as &$pwaste) {
                $pwaste['phone_name'] = $pwaste['phone_name'] ? $pwaste['phone_name'] : $this->getWastesPhone($pwaste['waste_id']);
                
                $price_engineer = M('waste')->where(array('id' => $pwaste['waste_id']))->getField('price');
                $worth += $price_engineer;
            }
            
            $result_info['wastes'] = $wastes;
            $result_info['worth'] = $worth;
            $result_info['status'] = $waste['status'];
            $result_info['time'] = date('Y-m-d', $waste['time']);
            $data[] = $result_info;
        }

        $result['status'] = 1;
        $result['data'] = $data;
        $this->ajaxReturn($result);
    }

    /**
     * 获得某个工程师当天、本月和累计  预计收益总和
     *
     * @return void
     */
    public function getMyDivide()
    {
        $engineerId = intval(I('param.engineerId'));

        if (empty($engineerId)) {
            $result['status'] = 0;
            $result['info'] = '请输入工程师ID';
            $this->ajaxReturn($result);
        }
        
        //当日收益
        $map = array();
        $map['ed.engineer_id'] = $engineerId;
        $map['o.status'] = 6;
        $map['o.end_time'] = array('egt', strtotime(date("Y-m-d")));
        $today_earnings = M('engineer_divide')->join('ed left join `order` o on ed.order_id = o.id')
                           ->where($map)->sum('ed.earning');
        
        //本月收益
        $map = array();
        $map['ed.engineer_id'] = $engineerId;
        $map['o.status'] = 6;
        $map['o.end_time'] = array('egt', strtotime(date('Y-m-01')));
        $month_earnings = M('engineer_divide')->join('ed left join `order` o on ed.order_id = o.id')
                            ->where($map)->sum('ed.earning');
        
        //累计收益
        $map = array();
        $map['ed.engineer_id'] = $engineerId;
        $map['o.status'] = 6;
        $all_earnings = M('engineer_divide')->join('ed left join `order` o on ed.order_id = o.id')
                        ->where($map)->sum('ed.earning');
        
        $result = array();
        $result['today_earnings'] = $today_earnings > 0 ? $today_earnings : 0;
        $result['month_earnings'] = $month_earnings > 0 ? $month_earnings : 0;
        $result['all_earnings'] = $all_earnings > 0 ? $all_earnings : 0;

        $this->ajaxReturn($result);
    }

    /**
     * 获得某个工程师当天、本月和累计 预计收益详情
     *
     * @return void
     */
    public function getMyDivideDetail()
    {
        $engineerId = intval(I('param.engineerId'));

        if (empty($engineerId)) {
            $result['status'] = 0;
            $result['info'] = '请输入工程师ID';
            $this->ajaxReturn($result);
        }

        //当日收益
        $map = array();
        $map['ed.engineer_id'] = $engineerId;
        $map['o.status'] = 6;
        $map['o.clearing_time'] = array('egt', strtotime(date("Y-m-d")));
        
        $today_array = M('engineer_divide')->join('ed left join `order` o on ed.order_id = o.id')
                    ->join('left join order_phomal op on op.order_id = o.id')
                    ->join('left join phone_malfunction pm on pm.id = op.phomal_id')
                    ->field('ed.earning as earnings, o.type, o.end_time, o.id as order_id, 
                            o.number as order_number, o.status, o.phone_name, 
                            IFNULL(group_concat(malfunction), o.malfunction_description) as malfunctions')
                    ->where($map)->group('o.id')->order('o.end_time desc')->select();
        
        //本月收益
        $map = array();
        $map['ed.engineer_id'] = $engineerId;
        $map['o.status'] = 6;
        $map['o.clearing_time'] = array('egt', strtotime(date("Y-m-01")));
        
        $month_list = M('engineer_divide')->join('ed left join `order` o on ed.order_id = o.id')
                    ->field('ed.*, o.end_time')
                    ->where($map)->order('o.end_time desc')->select();
        
        $month_divide = array();
        
        foreach ($month_list as $day) {
            $end_time = date('Y-m-d', $day['end_time']);
            
            if (isset($month_divide[$end_time])) {
                $month_divide[$end_time]['money'] += $day['earning'];
            } else {
                $month_divide[$end_time] = array(
                    'date'  => $end_time,
                    'money' => $day['earning']
                );
            }
        }
        $month_divide = array_values($month_divide);
        
        //累计收益
        $map = array();
        $map['ed.engineer_id'] = $engineerId;
        $map['o.status'] = 6;
        
        $all_list = M('engineer_divide')->join('ed left join `order` o on ed.order_id = o.id')
                    ->field('ed.*, o.end_time')
                    ->where($map)->order('o.end_time desc')->select();

        $all_divide = array();
        
        foreach ($all_list as $day) {
            $end_time = date('Y-m-d', $day['end_time']);
        
            if (isset($all_divide[$end_time])) {
                $all_divide[$end_time]['money'] += $day['earning'];
            } else {
                $all_divide[$end_time] = array(
                    'date'  => $end_time,
                    'money' => $day['earning']
                );
            }
        }
        $all_divide = array_values($all_divide);
        
        $result = array();
        $result['today_earnings'] = $today_array;
        $result['month_earnings'] = $month_divide;
        $result['all_earnings'] = $all_divide;

        $this->ajaxReturn($result);
    }

    /**
     * 获得某个工程师本月及累计订单总量
     *
     * @return void
     */
    public function getOrderAmount()
    {
        $engineerId = intval(I('param.engineerId'));

        if (empty($engineerId)) {
            $result['status'] = 0;
            $result['info'] = '请输入工程师ID';
            $this->ajaxReturn($result);
        }

        $map = array();
        $map['engineer_id'] = $engineerId;
        $map['status'] = 6;
        $all_count = M('order')->where($map)->count();

        $map = array();
        $map['engineer_id'] = $engineerId;
        $map['status'] = 6;
        $map['clearing_time'] = array('egt', strtotime(date('Y-m-01')));
        $month_count = M('order')->where($map)->count();
        
        $result = array();
        $result['total_order_num'] = $all_count;
        $result['month_order_num'] = $month_count;

        $this->ajaxReturn($result);
    }

    /**
     * 判断订单是否有效
     *
     * @return void
     */
    public function isEffective()
    {
        $orderId = intval(I('param.orderId'));
        $engineerId = intval(I('param.engineerId'));
        $result = array();

        if (empty($orderId)) {
            $result['status'] = 0;
            $result['data'] = '请输入订单ID';
            $this->ajaxReturn($result);
        }

        $map = array();
        $map['engineer_id'] = $engineerId;
        $map['id'] = $orderId;
        $map['status'] = array('in', array(3, 4));

        if (M('order')->where($map)->count()) {
            $result['status'] = 1;
            $result['data'] = '该订单正常';
            $this->ajaxReturn($result);
        } else {
            $result['status'] = 0;
            $result['data'] = '该订单异常';
            $this->ajaxReturn($result);
        }
    }
}
