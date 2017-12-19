<?php

// +------------------------------------------------------------------------------------------
// | Author: TCG <tianchunguang@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: 订单控制器 Dates: 2016-09-22
// +------------------------------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Controller;

class OrderController extends BaseController
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

        if (!empty($post['create_time_start'] && empty($post['create_time_end']))) {
            $map['o.create_time'] = array('egt', strtotime($post['create_time_start']));
        }
        
        if (!empty($post['create_time_end']) && empty($post['create_time_start'])) {
            $map['o.create_time '] = array('elt', strtotime($post['create_time_end'])+24*60*60-1);
        }

        if ($post['create_time_start'] && $post['create_time_end']) {
            $map['o.create_time '] = array(array('gt',strtotime($post['create_time_start'])),array('lt',strtotime($post['create_time_end']) +24*60*60-1),'and');
        }

        if (!empty($post['clearing_time_start']) && empty($post['clearing_time_end'])) {
            $map['o.clearing_time'] = array('egt', strtotime($post['clearing_time_start']));
        }
        
        if (!empty($post['clearing_time_end']) && empty($post['clearing_time_start'])) {
            $map['o.clearing_time '] = array('elt', strtotime($post['clearing_time_end'])+24*60*60-1);
        }

        if ($post['clearing_time_start'] && $post['clearing_time_end']) {
            $map['o.clearing_time '] = array(array('gt',strtotime($post['clearing_time_start'])),array('lt',strtotime($post['clearing_time_end']) +24*60*60-1),'and');
        }

        if (!empty($post['close_time_start']) && empty($post['close_time_end'])) {
            $map['o.close_time'] = array('egt', strtotime($post['close_time_start']));
        }

        if (!empty($post['close_time_end']) && empty($post['close_time_start'])) {
            $map['o.close_time '] = array('elt', strtotime($post['close_time_end'])+24*60*60-1);
        }

        if ($post['close_time_start'] && $post['close_time_end']) {
            $map['o.close_time '] = array(array('gt',strtotime($post['close_time_start'])),array('lt',strtotime($post['close_time_end']) +24*60*60-1),'and');
        }


        //if (!empty($post['city']) && is_int($post['city'])) {
        /*$post['city'] 是string类型 ,无法使用is_int*/
        if (!empty($post['city']) && $post['city'] != 'all' && $post['city'] != 9999) {
            
            if ($post['city'] == 'other') {
                $org = M('organization')->getField('city,id');
                $map['o.city'] = array('not in', array_keys($org));
            } else {
                $map['o.city'] = trim($post['city']);
            }
        } else {

            if (!$post['keyword']) {
                $city = array();

                foreach (session('addresses') as $address) {
                    $city[] = $address['city'];
                }

                if ($city) {
                    $map['o.city'] = array('in', $city);
                }
            }
        }

        if (!empty($post['phone_id'])) {
            $map['o.phone_id'] = trim($post['phone_id']);
        }
        
        if (!empty($post['engineer_id'])) {
            $map['o.engineer_id'] = trim($post['engineer_id']);
        }

        if (is_numeric($post['malfunction'])) {
            $map['m.id'] = $post['malfunction'];
        }

        if (is_numeric($post['status'])) {
            $map['o.status'] = $post['status'];
        }
        
        if (is_numeric($post['payment_method'])) {
            $map['o.payment_method'] = $post['payment_method'];
        }
        
        if (is_numeric($post['type'])) {
            $map['o.type'] = $post['type'];
        }
        
        if (is_numeric($post['category'])) {
            $map['o.category'] = $post['category'];
        }

        if (is_numeric($post['pay_type'])) {
            $map['o.pay_type'] = $post['pay_type'];
        }
        
        if (!empty($post['keyword'])) {
            $like['o.id'] = array('eq', trim($post['keyword']));
            $like['o.number'] = array('like', '%' . trim($post['keyword']). '%');
            $like['c.name'] = array('like', '%' . trim($post['keyword']). '%');
            $like['o.customer'] = array('like', '%' . trim($post['keyword']). '%');
            $like['o.cellphone'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['o.address'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['c.address'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['o.third_party_number'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['o.phone_imei'] = array('like', '%' . trim($post['keyword']) . '%');
            //$like['opt.partner'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        if (count($map) == 1 && isset($map['o.city'])) {
            $tmpMap = array();
            $tmpMap['city'] = $map['o.city'];
            $count = M('order')->where($tmpMap)->count();
        } else {
            $count = M('order')
                ->join('o left join customer c on c.id = o.customer_id')
                ->join('left join order_phomal opm on opm.order_id = o.id')
                ->join('left join phone_malfunction pm on opm.phomal_id = pm.id')
                ->join('left join malfunction m on pm.malfunction_id = m.id')
                ->where($map)->count();
        }
        $rst['total'] = $count;

        $list = M('order')->join('o left join engineer e on e.id = o.engineer_id')
                ->join('left join customer c on c.id = o.customer_id')
                ->join('left join order_phomal opm on opm.order_id = o.id')
                ->join('left join phone_malfunction pm on opm.phomal_id = pm.id')
                ->join('left join malfunction m on pm.malfunction_id = m.id')
                ->join('left join address on o.city = address.id')
                //->join('left join order_partner on o.id = order_partner.order_id')
                ->where($map)
                ->field('o.id, o.type, o.number, o.tax_number, o.customer, c.email, c.weixin, c.cellphone as cellphone,
                        o.phone_id, o.phone_name, o.phone_imei, o.category, o.create_time, o.status, o.reference_price, o.actual_price, o.pay_type, o.paid_amount, o.paid_time, 
                        o.payment_method, o.is_clearing, e.name as engineer_name, o.engineer_id, o.color, o.color_id, o.remark,
                        o.engineer_remark, e.name as engineer_name, o.malfunction_description, group_concat(m.name) as malfunctions,
                        group_concat(opm.phomal_id) as phomal_ids, o.province, o.city, o.county, 
                        c.address as address, c.address as caddress, o.is_invoice, o.invoice, o.logistics, o.postback,
                        o.end_time, o.buyer_email, o.maintain_start_time, o.maintain_end_time, o.close_reason, o.clearing_time, o.remark, o.engineer_remark,
                        o.user_remark, o.third_party_number, o.maintain_start_img, o.maintain_end_img, o.maintain_img, address.name as address_city, clearing_time, o.is_personal')
                ->group('o.id')
                ->limit($this->page())
                ->order('id desc')
                ->select();
        $rst['rows'] = $list;
    
        $this->ajaxReturn($rst);
    }
    
    /**
     * 下单
     *
     * @return void
     */
    public function add()
    {
        $rst = array();
        $data = array();
        $data = I('post.');
        $flag = true;

        if (!empty($data['old_order_number'])) {
            $where = array('number' => trim($data['old_order_number']));
            $order = M('order')->where($where)
                    ->field('id, customer_id, customer, cellphone, phone_id, phone_name, phone_imei, category, 
                        color_id, color, province, city, county, address, malfunction_description, create_time')
                    ->find();

            if (!$order) {
                $rst['success'] = false;
                $rst['errorMsg'] = '原订单编号不存在！';
                $this->ajaxReturn($rst);
            }
            
            $old_order_id = $order['id'];
            unset($order['id']);
            
            $order['number'] = D('order')->createNumber();
            $order['relation_id'] = $old_order_id;
            $order['status'] = 1;
            $order['type'] = 2;
            $order['create_time'] = time();
            
            $where = array('order_id' => $old_order_id);
            $phomals = M('order_phomal')->where($where)->select();
            
            M()->startTrans();
            $order_id = M('order')->add($order);

            if ($order_id === false) {
                $flag = false;
            }
            
            foreach ($phomals as $param) {
                $param['order_id'] = $order_id;
                
                if (M('order_phomal')->add($param) === false) {
                    $flag = false;
                }
            }
            
            $action = '操作人：' . session('userInfo.username') . '--后台手动下单--状态：' . C('ORDER_STATUS')[$order['status']];
            
            if (D('order')->writeLog($order_id, $action) === false) {
                $flag = false;
            }
            
            if ($flag) {
                M()->commit();
                $rst['success'] = true;
                $rst['errorMsg'] = '订单添加成功！订单编号：' . $order['number'];
            } else {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '添加失败！';
            }
        } else {
            $province_name = $data['province_name'];
            $city_name = $data['city_name'];
            $county_name = $data['county_name'];
            $type = $data['type'];

            if ($data['type'] == 4) {
                unset($data['Insurance']);
                $partner['partner'] = $data['Partner'];
            }

            if ($data['type'] == 5) {
                unset($data['Partner']);
                $partner['partner'] = $data['Insurance'];
            }

            if ($province_name !== $city_name) {
                $address = $province_name . $city_name . $county_name . $data['address'];
            } else {
                $address = $province_name . $county_name . $data['address'];
            }

            $phomal_ids = explode(',', $data['phomal_ids']);
            $map = array();
            $map['id'] = array('in', $phomal_ids);
            $reference_price = M('phone_malfunction')->where($map)->sum('price_reference');
            
            $data['address'] = $address;
            $data['reference_price'] = $reference_price;
            $data['actual_price'] = $reference_price;
            $data['create_time'] = time();
            $data['status'] = 1;
            $data['category'] = $data['category'];
            $data['name'] = $data['customer'];
            $data['number'] = D('order')->createNumber();
            
            M()->startTrans();
            $customer_id = M('customer')->add($data);
            
            if ($customer_id === false) {
                $flag = false;
            }
            
            $data['customer_id'] = $customer_id;
            $order_id = M('order')->add($data);

            if ($order_id === false) {
                $flag = false;
            }

            if ($type == 4 || $type == 5) {

                $partner['order_id'] = $order_id;
                $partner['order_number'] = $data['number'];

                if (M('order_partner')->add($partner) == false) {
                    $flag = false;
                }
            }

            foreach ($phomal_ids as $phomal_id) {
                $param = array(
                    'order_id'  => $order_id,
                    'phomal_id' => $phomal_id,
                );
                
                if (M('order_phomal')->add($param) === false) {
                    $flag = false;
                }
            }
            
            $action = '操作人：' . session('userInfo.username') . '--后台手动下单--状态：' . C('ORDER_STATUS')[$data['status']];
            
            if (D('order')->writeLog($order_id, $action) === false) {
                $flag = false;
            }
            
            if ($flag) {
                M()->commit();
                $rst['success'] = true;
                $rst['errorMsg'] = '订单添加成功！订单编号：' . $data['number'];
            } else {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '订单添加失败！';
            }
        }
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 编辑
     * 
     * @return void
     */
    public function edit()
    {
        $rst = array();
        $map = array();
        $map['o.id'] = I('get.id/d');
        
        $data = I('post.');
        $item = M('order')->join('o left join order_phomal op on op.order_id = o.id')
                ->join('left join phone_malfunction pm on pm.id = op.phomal_id')
                ->field('o.id, o.type, o.status, group_concat(malfunction) as malfunctions, o.city, o.engineer_id, o.phone_id')->group('order_id')
                ->where($map)->find();

        if ($item) {

            if (in_array($item['status'], array(5, 6))) {
                $rst['success'] = false;
                $rst['errorMsg'] = '结单或入库订单不能编辑！';
                $this->ajaxReturn($rst);
            }
            
            unset($map['o.id']);
            $map['id'] = I('get.id/d');
            $flag = true;
            
            if ($item['type'] == $data['type'] || $data['type'] == 5 || $data['type'] == 4) {
                
                M()->startTrans();
                
                if (isset($data['city']) && $item['city'] != $data['city'] && $item['engineer_id'] > 0) {
                    $data['engineer_id'] = 0;
                    $data['status'] = 1;
                    $item['status'] = 1;
                    
                    $action = '操作人：' . session('userInfo.username') . '--手动--编辑订单--状态：' . C('ORDER_STATUS')[$item['status']];
                    $action .= '--订单地址变更指派工程师自动取回';
                    D('order')->writeLog($item['id'], $action);
                }
                
                $phomal_ids = explode(',', $data['phomal_ids']);

                //故障价格
                if ($phomal_ids) {
                    $phone_ids = M('phone_malfunction')->where(array('id' => array('in', $phomal_ids)))->field('phone_id')->select();

                    foreach ($phone_ids as $value) {

                        if ($value['phone_id'] != $data['phone_id']) {
                            $rst['success'] = false;
                            $rst['errorMsg'] = '该机型下无此故障,请刷新页面后再修改！';
                            $this->ajaxReturn($rst);
                        }
                    }

                    $reference_price = M('phone_malfunction')->where(array('id' => array('in', $phomal_ids)))->sum('price_reference');
                    $data['reference_price'] = $reference_price;
                    $data['actual_price'] = $reference_price;
                }

                if ($data['color_id']) {
                    $color_ids = M('phone')->where(array('id' => $data['phone_id']))->field('color_id')->find();

                    if (strstr($color_ids['color_id'], $data['color_id']) == false) {
                        $rst['success'] = false;
                        $rst['errorMsg'] = '该机型下无此颜色,请刷新页面后再修改！';
                        $this->ajaxReturn($rst);
                    }

                }

                if (M('order')->where($map)->save($data) === false) {
                    $flag = false;
                }
                
                $map = array();
                $map['order_id'] = $item['id'];
                
                M('order_phomal')->where($map)->delete();
                
                foreach ($phomal_ids as $phomal_id) {
                    $param = array(
                        'order_id'  => $item['id'],
                        'phomal_id' => $phomal_id,
                    );
                
                    if (M('order_phomal')->add($param) === false) {
                        $flag = false;
                    }
                }
                
                if ($flag) {
                    M()->commit();
                } else {
                    M()->rollback();
                }
            } else if ($item['type'] == 1 && $data['type'] == 2) {

                if (empty($data['old_order_number'])) {
                    $rst['success'] = false;
                    $rst['errorMsg'] = '请输入原订单编号！';
                    $this->ajaxReturn($rst);
                }
            
                $where = array('number' => trim($data['old_order_number']));
                $order_id = M('order')->where($where)->getField('id');
                
                if (!$order_id) {
                    $rst['success'] = false;
                    $rst['errorMsg'] = '原订单编号不存在！';
                    $this->ajaxReturn($rst);
                }
            
                $param = array(
                    'relation_id' => $order_id,
                    'type' => $data['type'],
                    'reference_price' => 0,
                    'actual_price' => 0,
                );
            
                if (M('order')->where($map)->save($param) === false) {
                    $flag = false;
                }
            } else if ($item['type'] == 2 && $data['type'] == 1) {

                M()->startTrans();
                
                $data['relation_id'] = 0;
                
                if (isset($data['city']) && $item['city'] != $data['city'] && $item['engineer_id'] > 0) {
                    $data['engineer_id'] = 0;
                    $data['status'] = 1;
                    $item['status'] = 1;
                    
                    $action = '操作人：' . session('userInfo.username') . '--手动--编辑订单--状态：' . C('ORDER_STATUS')[$item['status']];
                    $action .= '--订单地址变更指派工程师自动取回';
                    D('order')->writeLog($item['id'], $action);
                }
                
                $phomal_ids = explode(',', $data['phomal_ids']);

                //故障价格
                if ($phomal_ids) {
                    $reference_price = M('phone_malfunction')->where(array('id' => array('in', $phomal_ids)))->sum('price_reference');
                    $data['reference_price'] = $reference_price;
                    $data['actual_price'] = $reference_price;
                }

                if (M('order')->where($map)->save($data) === false) {
                    $flag = false;
                }
                
                $map = array();
                $map['order_id'] = $item['id'];
                M('order_phomal')->where($map)->delete();
                
                foreach ($phomal_ids as $phomal_id) {
                    $param = array(
                        'order_id'  => $item['id'],
                        'phomal_id' => $phomal_id,
                    );
                    
                    if (M('order_phomal')->add($param) === false) {
                        $flag = false;
                    }
                }
                
                if ($flag) {
                    M()->commit();
                } else {
                    M()->rollback();
                }
            }
            
            if ($flag) {
                $rst['success'] = true;
                $rst['errorMsg'] = '编辑成功！';
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '编辑失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }
        
        if ($rst['success']) {
            $action = '操作人：' . session('userInfo.username') . '--手动--编辑订单--状态：' . C('ORDER_STATUS')[$item['status']];
            
            if (isset($data['type']) && $item['type'] != $data['type']) {
                $action .= ' 订单类型:[{' . C('ORDER_TYPE')[$item['type']] . '} => {' .  C('ORDER_TYPE')[$data['type']] . '}]';
            }
            
            $action .= ' 故障:[{' . $item['malfunctions'] . '} => {' .  $data['phomal_names'] . '}]';
            D('order')->writeLog($item['id'], $action);
        }
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 详情
     *
     * @return void
     */
    public function detail()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
    
        $item = M('order')->where($map)->find();
        
        if ($item) {
    
            if (in_array($item['status'], array(5, 6))) {
                $rst['success'] = false;
                $rst['errorMsg'] = '结单或入库订单不能编辑！';
                $this->ajaxReturn($rst);
            }

            $data = array();
            $data['actual_price'] = I('post.actual_price');
            
            if (M('order')->where($map)->save($data) !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '编辑失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }
    
        if ($rst['success']) {
            $action = '操作人：' . session('userInfo.username') . '--手动--更改实际价格[{' . $item['actual_price'] . '} => {' . $data['actual_price'] . '}]';
            D('order')->writeLog($item['id'], $action);
        }
    
        $this->ajaxReturn($rst);
    }
    
    /**
     * 指派
     * 
     * @return void
     */
    public function manual()
    {
        $order_partner = M("order_partner")->where(array('order_id' => intval(I('get.id/d'))))->find();
            
        if ($order_partner && $order_partner['partner'] == '葡萄生活' && $order_partner['is_paid'] != 1) {
            $rst['success'] = false;
            $rst['errorMsg'] = '手动派单失败！(葡萄生活订单未付款！)';
            $this->ajaxReturn($rst);
        }
        
        $rst = array();
        $rst = R("Api/Order/send", array(I('get.id/d'), I('post.engineer_id/d'), 2));
        $this->ajaxReturn($rst);
    }
    
    /**
     * 解除
     *
     * @return void
     */
    public function free()
    {
        $rst = array();
        $rst = D('order')->freeOrder(I('post.id/d'));
        $this->ajaxReturn($rst);
    }
    
    /**
     * 取消
     *
     * @return void
     */
    public function cancel()
    {
        $rst = array();
        $rst = D('order')->cancelOrder(I('get.id/d'), I('post.close_reason'));
        $this->ajaxReturn($rst);
    }
    
    /**
     * 备注
     *
     * @return void
     */
    public function remark()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');

        $data = array();
        $data['remark'] = I('post.remark');

        $item = M('order')->where($map)->find();
        
        if ($item) {
        
            if (M('order')->where($map)->save($data) !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '备注失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }
        
        if ($rst['success']) {
            $action = '操作人：' . session('userInfo.username') . '--手动----备注:[' . $data['remark'] . ']';
            D('order')->writeLog($item['id'], $action);
        }
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 改价
     *
     * @return void
     */
    public function setPrice()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入合理的订单价格！';
            $this->ajaxReturn($rst);
        }
        
        $item = M('order')->where($map)->find();
    
        if ($item) {
            
            if (!in_array($item['status'], array(5, 6)) && strpos(strtolower($_SERVER["HTTP_REFERER"]), 'admin/order/finance') === false) {
                $rst['success'] = false;
                $rst['errorMsg'] = '修改订单价格失败！';
                $this->ajaxReturn($rst);
            }
            
            M()->startTrans();
            $flag = true;
            
            $data['actual_price'] = $data['price'];
    
            if (M('order')->where($map)->save($data) === false) {
                $flag = false;
            }
            
            $action = '操作人：' . session('userInfo.username') . '--手动改价--{'.$item['actual_price'].'}=>{'.$data['price'].'}';
            
            if (D('order')->writeLog($item['id'], $action) === false) {
                $flag = false;
            }
            
            if ($flag) {
                M()->commit();
                $rst['success'] = true;
            } else {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '修改订单价格失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }
    
        $this->ajaxReturn($rst);
    }

    /**
     * 修改已付金额
     *
     * @return void
     */
    public function setPaidAmount()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        
        if (!is_numeric($data['paid_amount']) || $data['paid_amount'] < 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入合理的订单价格！';
            $this->ajaxReturn($rst);
        }
        
        $item = M('order')->where($map)->find();
    
        if ($item) {

            M()->startTrans();
            $flag = true;
            
            $row = array();
            $row['paid_amount'] = $data['paid_amount'];
    
            if (M('order')->where($map)->save($row) === false) {
                $flag = false;
            }
            
            $action = '操作人：' . session('userInfo.username') . '--手动修改已付金额--{'.$item['paid_amount'].'}=>{'.$data['paid_amount'].'}';
            
            if (D('order')->writeLog($item['id'], $action) === false) {
                $flag = false;
            }
            
            if ($flag) {
                M()->commit();
                $rst['success'] = true;
            } else {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '手动修改已付金额失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }
    
        $this->ajaxReturn($rst);
    }

    /**
     * 退款记录
     *
     * @return void
     */
    public function refundLog()
    {
        $map = array();
        $map['order_id'] = I('get.id/d');
        
        if (!$map['order_id']) {
            $this->ajaxReturn(array());
        }
        
        $list = M('order_refund')->where($map)->select();
        $this->ajaxReturn($list);
    }

    /**
     * 付款记录
     *
     * @return void
     */
    public function paidLog()
    {
        $map = array();
        $map['id'] = I('get.id/d');
        
        if (!$map['id']) {
            $this->ajaxReturn(array());
        }
        
        $item = M('order')->where($map)->getField('third_party_number');
        $list = explode(',', $item);

        $rst = array();
        foreach ($list as $key => $value) {
            $rst[] = array('number' => $value);
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 退款
     *
     * @return void
     */
    public function refund()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        
        if (!is_numeric($data['refund_amount']) || $data['refund_amount'] < 0) {
            $rst['success'] = false;
            $rst['errorMsg'] = '请输入合理的订单价格！';
            $this->ajaxReturn($rst);
        }
        
        $item = M('order')->where($map)->find();

        if (!$item) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }


        if (!$item['is_clearing']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '此订单尚未付款！';
            $this->ajaxReturn($rst);
        }

        $data['batch_no'] = $this->refundBatch();

        /** 退款请求 */
        if ($item['payment_method'] == 1) {
            $pay = new \Api\Controller\PayController();

            $refundRst = $pay->refund($data);

            /** 退款日志 */
            $row = array();
            $row['order_id'] = $item['id'];
            $row['order_number'] = $item['number'];
            $row['refund_time'] = time();
            $row['refund_amount'] = $data['refund_amount'];
            $row['batch_no'] = $data['batch_no'];
            $row['third_party_no'] = $data['third_party_number'];
            $row['refund_way'] = $item['payment_method'];
            $row['user_id'] = session('userId');

            if (M('order_refund')->add($row) === false) {
                $rst['success'] = false;
                $rst['errorMsg'] = '退款日志写入错误！';
                $this->ajaxReturn($rst);
            }
        } else if ($item['payment_method'] == 2) { /** 微信 */
            $pay = new \Api\Controller\WeixinpayController();
            $refundRst = $pay->refund($item, $data);

            if (!$refundRst) {
                $rst['success'] = false;
                $rst['errorMsg'] = '退款失败(微信)！';
                $this->ajaxReturn($rst);
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '此付款方式不支持退款！';
            $this->ajaxReturn($rst);
        }


        /** 订单日志 */
        $action = '操作人：' . session('userInfo.username') . '--退款单号{'.$item['third_party_number'].'}--退款金额{'.$data['refund_amount'].'}';

        if (D('order')->writeLog($item['id'], $action) === false) {
            $rst['success'] = false;
            $rst['errorMsg'] = '订单日志写入错误！';
            $this->ajaxReturn($rst);
        }

        $rst['success'] = true;
        $this->ajaxReturn($rst);
    }

    /**
     * 退款批次号 批次号，必填，格式：当天日期[8位]+序列号[3至24位]，如：20160308 1000001
     *
     * @return void
     */
    public function refundBatch()
    {
        /** 初始化redis */
        $redis = new \Redis();
        $redis->connect(C('REDIS_HOST'), C('REDIS_PORT'));
        $expireTime = strtotime('tomorrow') - 1;
        $batchSerialNumber = $redis->get('refundBatchNumber');

        /** 23:59:59 过期 获取不到流水设置为 0, 设置过期时间 获取流水号并且递增 */
        if (!$batchSerialNumber) {
            $batchSerialNumber = 0;
        }

        $redis->set('batchSerialNumber', $batchSerialNumber + 1);
        $redis->expireat('batchSerialNumber', $expireTime);

        /** 生成规则 年月日 + 流水号 */
        $number = date('ymd') . str_pad($batchSerialNumber, 4, "0", STR_PAD_LEFT);
        return $number;
    }
    
    /**
     * 改约
     *
     * @return void
     */
    public function hangup()
    {
        $rst = array();
        $rst = D('order')->hangupOrder(I('post.id/d'));
        $this->ajaxReturn($rst);
    }
    
    /**
     * 取回
     *
     * @return void
     */
    public function retrieve()
    {
        $rst = array();
        $rst = D('order')->retrieveOrder(I('post.id/d'));
        $this->ajaxReturn($rst);
    }
    
    /**
     * 入库
     *
     * @return void
     */
    public function stock()
    {
        $rst = array();
        $param = I('post.');
        $param['id'] = I('get.id/d');
        $rst = D('order')->stock($param);
        $this->ajaxReturn($rst);
    }
    
    /**
     * 导出
     *
     * @return void
     */
    public function export()
    {
        $columns = array(
            '订单ID' => 'id',
            '订单号'  => 'number',
            '用户姓名' => 'customer',
            '联系电话' => 'cellphone',
            '用户邮箱' => 'cemail',
            '联系地址' => 'caddress',
            '城市' => 'city',
            '订单类型' => 'type',
            '第三方合作商' => 'partner',
            '付款类型'    => 'pay_type',
            '返修单号' => 'relation_id',
            '机型'    => 'phone_name',
            'IMEI'  => 'phone_imei',
            '颜色'    => 'color',
            '故障'    => 'malfunction',
            '故障描述'  => 'malfunction_description',
            '消耗物料'  => 'fittings',
            '产生废料'  => 'wastes',
            '是否开票'  => 'is_invoice',
            '发票抬头'  => 'invoice',
            '维修方式'  => 'category',
            '维修时长'  => 'time',
            '预计价格'  => 'reference_price',
            '实际价格'  => 'actual_price',
            '支付方式'  => 'payment_method',
            '下单时间'  => 'create_time',
            '付款时间'  => 'paid_time',
            '结单时间'  => 'end_time',
            '结算时间'  => 'clearing_time',
            '取消时间' => 'close_time',
            '取消原因'  => 'close_reason',
            '工程师'   => 'engineer_name',
            '订单状态'  => 'status',
            '订单备注'  => 'remark',
            '工程师备注' => 'engineer_remark',
            '税号'    => 'tax_number',
        );
        
        $this->exportOrder($columns, I('post.'));
    }
    
    /**
     * 订单日志
     * 
     * @return void
     */
    public function orderLog()
    {
        $map = array();
        $map['order_id'] = I('get.id/d');
        
        if (!$map['order_id']) {
            $this->ajaxReturn(array());
        }
        
        $list = M('order_log')->where($map)->select();
        $this->ajaxReturn($list);
    }
    
    /**
     * 订单机型故障
     * 
     * @return void
     */
    public function orderPhomal()
    {
        $rst = array();
        $map = array();
        $map['order_id'] = I('get.id/d');
        $map['pm.id'] = array('gt', 0);
        
        if (!$map['order_id']) {
            $this->ajaxReturn(array());
        }
        
        $list = M('order_phomal')->join('op left join phone_malfunction pm on pm.id = op.phomal_id')
                ->join('left join `order` o on o.id = op.order_id')
                ->field('pm.malfunction, pm.fitting, o.color_id, pm.waste, pm.is_color')
                ->where($map)->select();
        
        $this->ajaxReturn($list);
    }

    /**
     * 订单第三方
     *
     * @return void
     */
    public function partner()
    {
        $rst = array();
        $map = array();
        $map['order_id'] = I('get.id/d');

        if (!$map['order_id']) {
            $this->ajaxReturn(array());
        }

        $list = M('order')
            ->join('o left join `order_partner` on o.id = order_partner.order_id')
            ->where($map)
            ->field('partner')
            ->find();

        if (!$list['partner']) {
            $list['partner'] = '无';
        }
        $this->ajaxReturn($list);
    }

    /**
     * 获取所有故障
     *
     * @return void
     */
    public function getPhomal()
    {

        $list = M('malfunction')->select();

        $this->ajaxReturn($list);
    }

    /**
     * 订单列表（财务）
     *
     * @return void
     */
    public function finance()
    {
        $this->display();
    }
    
    /**
     * 导出订单数据
     *
     * @return void
     */
    public function financeExport()
    {
        $columns = array(
            '订单编号'  => 'number',
            '客户名'   => 'customer',
            '手机号码'  => 'cellphone',
            '手机型号'  => 'phone_name',
            '状态'     => 'status',
            '工程师'    => 'engineer_name',
            '下单时间'    => 'create_time',
            '结单时间'    => 'end_time',
            '付款类型'    => 'pay_type',
            '预计价格'    => 'reference_price',
            '实际价格'    => 'actual_price',
            '付款方式'    => 'payment_method',
            '付款时间'    => 'paid_time',
            '结算时间'  => 'clearing_time',
            '是否结算'    => 'is_clearing',
            '已付金额'    => 'paid_amount',
            '第三方订单号' => 'third_party_number',
            '买家账号'    => 'buyer_email',
            '备注'      => 'remark',
            '工程师备注'  => 'engineer_remark',
            '用户备注'   => 'user_remark',
            '付款'  => 'fukuan',
            '取消时间' => 'close_time',
            '地区'    => 'city',
            '税号'    => 'tax_number',
            '发票抬头' => 'invoice'
        );

        $this->exportOrder($columns, I('post.'));
    }
    
    /**
     * 订单导出
     */
    private function exportOrder($columns, $param)
    {
        if (!$param || !is_array($param)) {
            return false;
        }
        
        set_time_limit(0);
        
        $exorders = array();
        $exorders[] = array_keys($columns);
        
        if (!empty($param['status']) && $param['status'] != 'all') {
            $map['o.status'] = $param['status'];
        }

        if (!empty($param['pay_type']) && $param['pay_type'] != 'all') {
            $map['o.pay_type'] = $param['pay_type'];
        }

        if (!empty($param['payment_method']) && $param['payment_method'] != 'all') {
            $map['o.payment_method'] = $param['payment_method'];
        }

        if (!empty($param['type']) && $param['type'] != 'all') {
            $map['o.type'] = $param['type'];
        }
        
        if (!empty($param['create_time_start']) && empty($param['create_time_end'])) {
            $map['o.create_time'] = array('EGT', strtotime($param['create_time_start']));
        }
        
        if(!empty($param['create_time_end']) && empty($param['create_time_start'])) {
            $map['o.create_time'] = array('ELT', strtotime($param['create_time_end']) +24*60*60-1);
        }

        if ($param['create_time_start'] && $param['create_time_end']) {
            $map['o.create_time '] = array(array('egt',strtotime($param['create_time_start'])), array('elt',strtotime($param['create_time_end']) +24*60*60-1),'and');
        }

        if (!empty($param['paid_time_start']) && empty($param['paid_time_end'])) {
            $map['o.paid_time'] = array('EGT', strtotime($param['paid_time_start']));
        }
        
        if(!empty($param['paid_time_end']) && empty($param['paid_time_start'])) {
            $map['o.paid_time'] = array('ELT', strtotime($param['paid_time_end']) +24*60*60-1);
        }

        if ($param['paid_time_start'] && $param['paid_time_end']) {
            $map['o.paid_time '] = array(array('egt',strtotime($param['paid_time_start'])), array('elt',strtotime($param['paid_time_end']) +24*60*60-1),'and');
        }
        
        if (!empty($param['clearing_time_start'] && empty($param['clearing_time_end']))) {
            $map['o.clearing_time'] = array('EGT', strtotime($param['clearing_time_start']));
        }
        
        if (!empty($param['clearing_time_end'] && empty($param['clearing_time_start']))) {
            $map['o.clearing_time'] = array('ELT', strtotime($param['clearing_time_end']) +24*60*60-1);
        }

        if ($param['clearing_time_start'] && $param['clearing_time_end']) {
            $map['o.clearing_time '] = array(array('egt',strtotime($param['clearing_time_start'])),array('elt',strtotime($param['clearing_time_end']) +24*60*60-1),'and');
        }

        if (!empty($param['close_time_start']) && empty($param['close_time_end'])) {
            $map['o.close_time'] = array('egt', strtotime($param['close_time_start']));
        }

        if (!empty($param['close_time_end']) && empty($param['close_time_start'])) {
            $map['o.close_time '] = array('elt', strtotime($param['close_time_end'])+24*60*60-1);
        }

        if ($param['close_time_start'] && $param['close_time_end']) {
            $map['o.close_time '] = array(array('gt',strtotime($param['close_time_start'])),array('lt',strtotime($param['close_time_end']) +24*60*60-1),'and');
        }

        if (!empty($param['city'])) {
            
            if ($param['city'] != 9999 && $param['city'] != 'all') {
                $map['o.city'] = trim($param['city']);
            } else {
                $city = array(0);
                
                foreach (session('addresses') as $address) {
                    $city[] = $address['city'];
                }
                
                if ($city && !in_array(9999, $city)) {
                    $map['o.city'] = array('in', $city);
                }
            }
        } else {
            $city = array(0);
        
            foreach (session('addresses') as $address) {
                $city[] = $address['city'];
            }
        
            if ($city && !in_array(9999, $city)) {
                $map['o.city'] = array('in', $city);
            }
        }

        if (!empty($param['keyword'])) {
            $like['o.id'] = array('eq', trim($param['keyword']));
            $like['o.number'] = array('like', '%' . trim($param['keyword']). '%');
            $like['c.name'] = array('like', '%' . trim($param['keyword']). '%');
            $like['o.customer'] = array('like', '%' . trim($param['keyword']). '%');
            $like['o.cellphone'] = array('like', '%' . trim($param['keyword']) . '%');
            $like['o.address'] = array('like', '%' . trim($param['keyword']) . '%');
            $like['c.address'] = array('like', '%' . trim($param['keyword']) . '%');
            $like['o.third_party_number'] = array('like', '%' . trim($param['keyword']) . '%');
            $like['o.phone_imei'] = array('like', '%' . trim($param['keyword']) . '%');
            //$like['opt.partner'] = array('like', '%' . trim($param['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        $list = M('order')->join('o left join customer c on c.id = o.customer_id')
                ->join('left join phone p on p.id = o.phone_id')
                ->join('left join engineer e on o.engineer_id = e.id')
                ->join('left join order_partner on o.id = order_partner.order_id')
                ->where($map)->field('o.*,p.alias as phone_name, c.email as cemail, c.address as caddress, e.name as engineer_name, order_partner.partner')
                ->order('id desc')->select();
        
        $category = C('ORDER_CATEGORY');
        $payment = C('ORDER_PAYMENT');
        $status  = C('ORDER_STATUS');
         
        foreach ($list as &$order) {

            $fittings = array();
            $wastes = array();
            $order['malfunction'] = array();
        
            $malfunction_list = M('order_phomal')->join('op left join phone_malfunction pm on op.phomal_id = pm.id')
                                ->field('pm.malfunction, pm.fitting, pm.waste, pm.is_color')
                                ->where(array('op.order_id' => $order['id']))->select();
        
            foreach ($malfunction_list as $malfunction) {
        
                $order['malfunction'][] = $malfunction['malfunction'];
        
                if (empty($malfunction['is_color'])) {
        
                    $malfunction_fittings = json_decode($malfunction['fitting'], true);
        
                    foreach ($malfunction_fittings as $fitting){
                        $fittings[] = $fitting['name'];
                    }
                } else { //有颜色，读取订单的color_id去fitting里面去取相应的颜色值
                    $mal_list = json_decode($malfunction['fitting'], true);
        
                    $malfunction_fittings = $mal_list[$order['color_id']]['items'];
        
                    foreach ($malfunction_fittings as $fitting){
                        $fittings[] = $fitting['name'];
                    }
                }
        
                $malfunction_wastes = json_decode($malfunction['waste'], true);
        
                foreach ($malfunction_wastes as $waste){
                    $wastes[] = $waste['name'];
                }
            }
        
            $order['malfunction'] = implode(',', $order['malfunction']);
            $order['status'] = $status[$order['status']];
            $order['category'] = $category[$order['category']];
            $order['isinvoice'] = $order['is_invoice'] ? '是' : '否';
            $type = array(1 => '新单', 2 => '返修', 5 => '保险', 3 => '活动', 4 => '第三方');
            $order['type'] = $type[$order['type']];
            $order['time'] = gmstrftime('%H:%M:%S', ($order['end_time'] - $order['maintain_start_time']));
            $order['payment_method'] = $payment[$order['payment_method']];
            $order['third_party_number'] = ' '. $order['third_party_number'];
            $order['create_time'] = date('Y-m-d H:i:s', $order['create_time']);
            $order['end_time'] = date('Y-m-d H:i:s', $order['end_time']);
            $order['paid_time'] = $order['paid_time'] ? date('Y-m-d H:i:s', $order['paid_time']) : '';
            $order['clearing_time'] = $order['clearing_time'] ? date('Y-m-d H:i:s', $order['clearing_time']) : '';
            $order['clone_time'] = $order['clone_time'] ? date('Y-m-d H:i:s', $order['clone_time']) : '';
            $order['is_clearing'] = $order['is_clearing'] ? '是' : '否';
            $order['malfunction_description'] = str_replace( array('='), '', $order['malfunction_description']);
            $order['fittings'] = implode(',', $fittings);
            $order['wastes'] = implode(',', $wastes);
            $order['fukuan'] = $order['clearing_time'] != 0 ? '是' : '否';
            $order['pay_type'] = $order['pay_type'] == 2 ? '预付' : '修付';
            $order['close_time'] = $order['close_time']? date('Y-m-d H:i:s', $order['close_time']) : '';
            $order['city'] = M('address')->find($order['city'])['name'];
            $order['tax_number'] = $order['tax_number'] ? $order['tax_number'] : '';
            $row = array();
        
            foreach ($columns as $v) {
                $row[] = $order[$v];
            }
        
            $exorders[] = $row;
        }

        unset($list);
        unset($order);
        unset($row);
        
        $this->exportData('weadoc_order_' . date('Y_m_d_H_i_s'), $exorders);
    }
    
    /**
     * 地区
     *
     * @return void
     */
    public function address()
    {
        $pid = I('get.pid', 0);
        $map = array();
        $map['pid'] = $pid;

        //加一层redis缓存
        if(C('REDIS_MODE')) {
            $redisCache = S(array('type'=>'redis','prefix'=>'backend_','expire'=>600));
            if($redisCache) {
                if(!$list = $redisCache->get('address_'.$pid)) {
                    $list = M('address')->where($map)->select();
                    if (!$pid) {
                        array_unshift($list, array('id' => '9999', 'name' => '全国'));
                    } else if($pid == '9999'){
                        $list[] = array('id' => '9999', 'name' => '全国');
                    }
                    $redisCache->set('address_'.$pid, $list);
                }

                $this->ajaxReturn($list);
            }
        }

        $list = M('address')->where($map)->select();

        if (!$pid) {
            array_unshift($list, array('id' => '9999', 'name' => '全国'));
        } else if($pid == '9999'){
            $list[] = array('id' => '9999', 'name' => '全国');
        }
    
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
                ->field('pm.id, m.name, pm.price_reference')
                ->where($map)->select();
        $this->ajaxReturn($list);
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
     * 工程师
     */
    public function engineers()
    {
        $list = M('engineer')->where(array('status' => array('gt', 0), 'organization_id' => array('in', array_keys(session('organizations')))))->field('id, name')->select();
        array_unshift($list,array('name'=>'全部','id'=>''));
        $this->ajaxReturn($list);
    }
    
    /**
     * 颜色
     *
     * @return void
     */
    public function colors()
    {
        $id = I('get.id');

        if (!empty($id)) {
            $phone = M('phone')->find($id);
            $color_id = explode(',',$phone['color_id']);

            foreach ($color_id as &$value) {
                $list[] = M('goods_color')->where(array('id' => $value))->field('id, name')->find();
            }
        } else {
            $list = M('goods_color')->field('id, name')->select();
        }

        $this->ajaxReturn($list);
    }
    
    /**
     * 订单关闭原因
     */
    public function orderCloseReasons()
    {
        $list = M('order_close_reason')->field('id, name')->select();
        $this->ajaxReturn($list);
    }
    
    /**
     * 订单列表（客服）
     */
    public function kefu()
    {
        $this->display();
    }
    
    /**
     * 客服处理中，状态码21
     *
     * @return void
     */
    public function kefuProcess()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('post.id/d');
        $data = array();
        $data['status'] = 21;
        
        $item = M('order')->where($map)->find();
        
        if ($item) {
            
            if ($item['status'] != 1) {
                $rst['success'] = false;
                $rst['errorMsg'] = '订单状态更新失败(订单不是下单状态)！';
                $this->ajaxReturn($rst);
            }
            
            $flag = true;
            M()->startTrans();
            
            if (M('order')->where($map)->save($data) === false) {
                $flag = false;
            }
            
            $action = '操作人：' . session('userInfo.username') . '--手动--客服处理中--状态：客服处理中';
            
            if (D('order')->writeLog($map['id'], $action) === false) {
                $flag = false;
            }
            
            if ($flag) {
                M()->commit();
                $rst['success'] = true;
            } else {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '订单状态更新失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '订单记录不存在！';
        }
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 客服处理完成，状态码22
     *
     * @return void
     */
    public function kefuClose()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('post.id/d');
        $data = array();
        $data['status'] = 22;
        
        $item = M('order')->where($map)->find();
        
        if ($item) {
        
            if ($item['status'] != 21) {
                $rst['success'] = false;
                $rst['errorMsg'] = '订单状态更新失败(订单不是客服处理中状态)！';
                $this->ajaxReturn($rst);
            }
            
            $flag = true;
            M()->startTrans();
            
            if (M('order')->where($map)->save($data) === false) {
                $flag = false;
            }
            
            $action = '操作人：' . session('userInfo.username') . '--手动--客服处理完成--状态：客服处理完成';
            
            if (D('order')->writeLog($map['id'], $action) === false) {
                $flag = false;
            }
            
            if ($flag) {
                M()->commit();
                $rst['success'] = true;
            } else {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '订单状态更新失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '订单记录不存在！';
        }
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 客服重置
     *
     * @return void
     */
    public function kefuReset()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('post.id/d');
        $data = array();
        $data['status'] = 1;
        
        $item = M('order')->where($map)->find();
        
        if ($item) {
        
            if (!in_array($item['status'], array(21, 22))) {
                $rst['success'] = false;
                $rst['errorMsg'] = '订单状态更新失败(订单不是客服处理中或处理完成状态)！';
                $this->ajaxReturn($rst);
            }
            
            $flag = true;
            M()->startTrans();
        
            if (M('order')->where($map)->save($data) === false) {
                $flag = false;
            }
            
            $action = '操作人：' . session('userInfo.username') . '--手动--客服重置--状态：下单';
            
            if (D('order')->writeLog($map['id'], $action) === false) {
                $flag = false;
            }
            
            if ($flag) {
                M()->commit();
                $rst['success'] = true;
            } else {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '订单状态更新失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '订单记录不存在！';
        }
        
        $this->ajaxReturn($rst);
    }

    /**
     * 唤起订单 (仅预付自动取消的订单可以唤起)
     *
     * @return void
     */
    public function arouse()
    {
        $rst = array();

        $map = array();
        $map['id'] = I('post.id/d');

        $item = M('order')->where($map)->find();
        
        if ($item) {
        
            if ($item['pay_type'] != 2) {
                $rst['success'] = false;
                $rst['errorMsg'] = '非预付单不能唤起！';
                $this->ajaxReturn($rst);
            }

            if ($item['status'] != -1) {
                $rst['success'] = false;
                $rst['errorMsg'] = '订单不在取消状态！';
                $this->ajaxReturn($rst);
            }

            if ($item['is_clearing'] == 1) {
                $rst['success'] = false;
                $rst['errorMsg'] = '已付款订单不能唤起！';
                $this->ajaxReturn($rst);
            }
            
            M()->startTrans();

            $data = array();
            $data['status'] = 1;
        
            if (M('order')->where($map)->save($data) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '订单状态更新失败！';
                $this->ajaxReturn($rst);
            }
            
            $action = '操作人：' . session('userInfo.username') . '--手动--唤起订单--状态：下单';
            
            if (D('order')->writeLog($map['id'], $action) === false) {
                M()->rollback();
                $rst['success'] = false;
                $rst['errorMsg'] = '订单日志更新失败！';
                $this->ajaxReturn($rst);
            }
            
            M()->commit();
            $rst['success'] = true;
            $this->ajaxReturn($rst);
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '订单记录不存在！';
            $this->ajaxReturn($rst);
        }
    }
}