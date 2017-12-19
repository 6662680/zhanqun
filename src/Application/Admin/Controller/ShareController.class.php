<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 用户 Dates: 2016-08-05
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class ShareController extends BaseController
{
    /**
     * 载入分享列表页
     * @author liyang
     * @return void
     */
    public function username()
    {
        $this->display();
    }

    /**
     * 分享列表
     * @author liyang
     * @return void
     */
    public function userRow()
    {
        $map = array();
        $model = M('share_user');

        if (I('post.user')) {
            $map['user'] = array('like','%'.I('post.user').'%');
        }
        $rst['total'] = $model->where($map)->count();

        $rst['rows'] = $model->where($map)->order('id desc')->limit($this->page())->select();

        foreach ($rst['rows'] as &$value) {
            $value['qrcode'] = 'http://'.$_SERVER["SERVER_NAME"].'/admin/share/qrcode/?url=http://m.weadoc.com?friendshare='.$value['user'];
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 生成二维码
     *
     * @return void
     */
    public function qrcode()
    {
        $url = I('get.url');
        require_once(VENDOR_PATH . "phpqrcode/phpqrcode.php");
        \QRcode::png($url);
    }

    /**
     * 添加
     * @author liyang
     * @return void
     */
    public function add()
    {
        $rst = array();
        $id = I('get.id', 0);
        $post = I('post.');
        $model = M('share_user');

        if (!$post['pwd']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '密码必须输入！';
            $this->ajaxReturn($rst);
        }

        if (!$model->where(array('user'=>array('eq',I('post.user'))))->find()) {
            $model->name =  $post['name'];
            $model->user =  $post['user'];
            $model->password = MD5($post['pwd']);
            $model->type = $post['type'];
            $model->enterprise_name = $post['enterprise_name'];
            $model->organization_id = $post['organization'];
            $model->name2 = $post['name2'] ? $post['name2'] : '';
            $model->mobile2 = $post['mobile2'];
            $model->remark = $post['remark'];
            $model->time = time();

            if (!empty($post['pmobile'])) {
                $model->pid = $model->where(array('user' => $post['pmobile']))->getField('id');

                if (!$model->pid) {
                    $rst['success'] = false;
                    $rst['errorMsg'] = '上级手机号不存在！';
                    $this->ajaxReturn($rst);
                }
            }

            if (!$model->add()) {
                $rst['success'] = false;
                $rst['errorMsg'] = '添加失败！';
            } else {
                $rst['success'] = true;
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '已有的手机号！';
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 编辑
     * @author liyang
     * @return void
     */
    public function edit()
    {
        $rst = array();
        $id = I('get.id', 0);
        $post = I('post.');

        $model = M('share_user');
        $model->find($id);
        $model->name = $post['name'] ? $post['name'] : '';
        $model->password = $post['pwd']? MD5($post['pwd']) : '';
        $model->type = $post['type'];
        $model->remark = $post['remark'];
        if (!$model->save()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '编辑失败！';
        } else {
            $rst['success'] = true;
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 删除
     * @author liyang
     * @return void
     */
    public function delete()
    {
        $rst = array();
        $model = M('share_user');
        $model->find(I('post.id'));
        if (!$model->delete()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
        } else {
            $rst['success'] = true;
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 重置密码
     * @author liyang
     * @return void
     */
    public function reset()
    {
        $rst = array();
        $id = I('post.id');

        $model = M('share_user');
        $model->find($id);
        $model->password =  MD5(12345678);

        if (!$model->save()) {
            pr($model->getError());
            $rst['success'] = false;
            $rst['errorMsg'] = '重置失败！';
        } else {
            $rst['success'] = true;
        }
        $this->ajaxReturn($rst);
    }


    /**
     * 载入分享统计页面
     * @author liyang
     * @return void
     */
    public function statistical()
    {
        $this->display();
    }

    /**
     * 载入分享统计条数
     * @author liyang
     * @return void
     */
    public function statisticalRow()
    {
        $rst = array();
        $map = array();
        $where = array();
        $model = M('share');

        /*下单时间*/
        if (!empty(I('post.create_time_start')) && empty(I('post.create_time_end'))) {
            $map['create_time'] = array(array('EGT',strtotime(I('create_time_start'))));
        } else if(empty(I('post.create_time_start')) && !empty(I('post.create_time_end'))) {
            $map['create_time'] = array(array('ElT',strtotime(I('post.create_time_end'))));
        } else if(!empty(I('post.create_time_start')) && !empty(I('post.create_time_end'))) {
            $map['create_time'] = array(array('ElT',(strtotime(I('post.create_time_end')))),array('EGT',strtotime(I('create_time_start'))),'and');
        }

        /*结单时间*/
        if (!empty(I('post.clearing_time_start')) && empty(I('post.clearing_time_end'))) {
            $map['clearing_time'] = array(array('EGT',strtotime(I('clearing_time_start'))));
        } else if(empty(I('post.clearing_time_start')) && !empty(I('post.clearing_time_end'))) {
            $map['clearing_time'] = array(array('ElT',strtotime(I('post.clearing_time_end'))) + 24 * 60 * 60 - 1);
        } else if(!empty(I('post.clearing_time_start')) && !empty(I('post.clearing_time_end'))) {
            $map['clearing_time'] = array(array('ElT',(strtotime(I('post.clearing_time_end'))) + 24 * 60 * 60 - 1),array('EGT',strtotime(I('clearing_time_start'))),'and');
        }

        /*状态*/

        if (I('post.status') == 6) {
            $map['o.status'] = I('post.status');
        }

        if (I('post.status') == 1) {
            $map['o.status'] = array('LT',6);
        }

        /*关键字*/

        if (!empty(I('post.keyword'))){
            $map['number|user'] = array('like','%'.I('post.keyword').'%');
        }

        $rst['rows'] = $model
                    ->join('left join `share_user` u on u.id = share.user_id')
                    ->join('left join `order` o on o.id = share.order_id')
                    ->field('share.id,o.status,share.start_time,o.clearing_time,share.remark,share.ratio,
                            u.user,o.number,o.actual_price,u.name,u.pid,share.user_id')
                    ->order('share.id desc')
                    ->limit($this->page())
                    ->where($map)
                    ->select();

        $rst['total'] = $model
                    ->join('left join `share_user` u on u.id = share.user_id')
                    ->join('left join `order` o on o.id = share.order_id')
                    ->field('share.id,o.status,share.start_time,o.clearing_time,share.remark,share.ratio,
                                    u.user,o.number,o.actual_price')
                    ->order('share.id desc')
                    ->where($map)
                    ->count();

        foreach($rst['rows'] as &$value) {

            if (!empty($value['pid'])) {
                $value['user'] = substr( $this->findSuperior($value['user_id']), 0 ,-1);
            }

            if ($value['status'] >= 6) {
                $value['deduct'] = $value['actual_price'] * $value['ratio'] / 100;
            } else {
                $value['deduct'] = 0;
            }
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 公司地区
     *
     * @return void
     */
    public function organization()
    {
        $list = M('organization')->where(array('type' => 1))->select();
        $this->ajaxReturn($list);
    }

    /**查询上级分享人员
     * @author liyang
     * @return void
     */
    public function findSuperior($username, $rst =NULL)
    {
        $share = M('share_user')->where(array('id' => $username))->find();
        $rst .= $share['name'] . '('.$share['user'] .')' . '>';

        if (!empty($share['pid'])) {
            return $this->findSuperior($share['pid'], $rst);
        } else {
            return $rst;
        }
    }

    /**
     * 添加备注
     * @author liyang
     * @return void
     */
    public function statisticalEdit()
    {
        $rst = array();
        $id = I('get.id', 0);
        $model = M('share');
        $model->find($id);
        $model->remark = I('post.remark');
        if (!$model->save()) {
            $rst['success'] = false;
            $rst['errorMsg'] = '编辑失败！';
        } else {
            $rst['success'] = true;
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
        $rst = array();
        $map = array();
        $where = array();
        $model = M('share');
        
        /*下单时间*/
        if (!empty(I('post.create_time_start')) && empty(I('post.create_time_end'))) {
            $map['create_time'] = array(array('EGT',strtotime(I('create_time_start'))));
        } else if(empty(I('post.create_time_start')) && !empty(I('post.create_time_end'))) {
            $map['create_time'] = array(array('ElT',strtotime(I('post.create_time_end'))) + 24 * 60 * 60 - 1);
        } else if(!empty(I('post.create_time_start')) && !empty(I('post.create_time_end'))) {
            $map['create_time'] = array(array('ElT',(strtotime(I('post.create_time_end'))) + 24 * 60 * 60 - 1),array('EGT',strtotime(I('post.create_time_start'))),'and');
        }
        
        /*结单时间*/
        if (!empty(I('post.clearing_time_start')) && empty(I('post.clearing_time_end'))) {
            $map['clearing_time'] = array(array('EGT',strtotime(I('clearing_time_start'))));
        } else if(empty(I('post.clearing_time_start')) && !empty(I('post.clearing_time_end'))) {
            $map['clearing_time'] = array(array('ElT',strtotime(I('post.clearing_time_end'))) + 24 * 60 * 60 - 1);
        } else if(!empty(I('post.clearing_time_start')) && !empty(I('post.clearing_time_end'))) {
            $map['clearing_time'] = array(array('ElT',(strtotime(I('post.clearing_time_end'))) + 24 * 60 * 60 - 1),array('EGT',strtotime(I('post.clearing_time_start'))),'and');
        }
        
        /*状态*/
        if (I('post.status') == 6) {
            $map['o.status'] = I('post.status');
        }

        if (I('post.status') == 1) {
            $map['o.status'] = array('LT',6);
        }
        
        /*关键字*/
        if (!empty(I('post.keyword'))){
            $map['number|user'] = array('like','%'.I('post.keyword').'%');
        }
        
        $rst['rows'] = $model->join('left join `share_user` u on u.id = share.user_id')
                        ->join('left join `order` o on o.id = share.order_id')
                        ->field('share.id,u.name,o.status,share.start_time,o.clearing_time,share.remark,share.ratio,
                            u.user,o.number,o.actual_price')
                        ->order('share.id desc')
                        ->where($map)
                        ->select();
        
        $params = array();
        $params[] = array('ID', '名称', '分享账号', '订单号', '订单金额', '订单状态', '下单时间', '付款时间', '分成比例', '提成', '备注');
        
        foreach($rst['rows'] as &$value) {
            
            if ($value['status'] >= 6) {
                $value['deduct'] = $value['actual_price'] * $value['ratio'] / 100;
            } else {
                $value['deduct'] = 0;
            }
            
            $params[] = array(
                $value['id'], $value['name'],$value['user'], $value['number'], $value['actual_price'], $value['status'] >= 6 ? '已付款' : '未付款',
                date('Y-m-d H:i:s', $value['start_time']), $value['end_time'] ? date('Y-m-d H:i:s', $value['end_time']) : '',
                $value['ratio'], $value['deduct'], $value['remark']
            );
        }
        
        $this->exportData('分享统计-'.date('Y-m-h-H-i-s'), $params);
    }
    
    /**
     * 保险分成
     */
    public function insurance()
    {
        $this->display();
    }
    
    /**
     * 保险列表
     */
    public function insuranceRows()
    {
        $rst = array();
        $map = array();
        $model = M('phomal_insurance_order');
        $data = I('post.');
        $map['pio.engineer_id'] = array('gt', 0);
        
        if (!empty($data['create_stime']) && empty($data['create_etime'])) {
            $map['pio.create_time'] = array(array('EGT', strtotime($data['create_stime'])));
        } else if(empty($data['create_stime']) && !empty($data['create_etime'])) {
            $map['pio.create_time'] = array(array('ElT', strtotime($data['create_etime'])));
        } else if(!empty($data['create_stime']) && !empty($data['create_etime'])) {
            $map['pio.create_time'] = array(array('ElT', (strtotime($data['create_stime']))), array('EGT', strtotime($data['create_etime'])), 'and');
        }
        
        if ($data['status'] != 'all' && is_numeric($data['status'])) {
            $map['pio.status'] = $data['status'];
        }
        
        if (!empty($data['engineer_id']) && $data['engineer_id'] != 'all') {
            $map['pio.engineer_id'] = $data['engineer_id'];
        }
        
        /*关键字*/
        if (!empty(I('post.keyword'))){
            $like['pio.id'] = array('like', '%' . trim($data['keyword']). '%');
            $like['pio.number'] = array('like', '%' . trim($data['keyword']). '%');
            $like['pio.customer'] = array('like', '%' . trim($data['keyword']). '%');
            $like['pio.cellphone'] = array('like', '%' . trim($data['keyword']) . '%');
            $like['pio.engineer_id'] = array('like', '%' . trim($data['keyword']) . '%');
            $like['e.name'] = array('like', '%' . trim($data['keyword']) . '%');
            $like['e.cellphone'] = array('like', '%' . trim($data['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }
        
        $count = $model->join('pio left join engineer e on e.id = pio.engineer_id')
                ->where($map)->count();
        $rst['total'] = $count;
        $rst['rows'] = array();
        
        if ($count) {
            $rst['rows'] = $model->join('pio left join engineer e on e.id = pio.engineer_id')
                        ->join('left join engineer_insurance_divide eid on eid.insurance_order_id = pio.id')
                        ->field('pio.id, pio.number, pio.customer, pio.cellphone as ccellphone, e.name, e.cellphone, 
                            pio.price, pio.status, pio.create_time, pio.pay_time, eid.earning')
                        ->where($map)->order('pio.id desc')->limit($this->page())->select();
        }
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 导出
     */
    public function insuranceExport()
    {
        $rst = array();
        $map = array();
        $model = M('phomal_insurance_order');
        $data = I('post.');
        $map['pio.engineer_id'] = array('gt', 0);
        
        if (!empty($data['create_stime']) && empty($data['create_etime'])) {
            $map['pio.create_time'] = array(array('EGT', strtotime($data['create_stime'])));
        } else if(empty($data['create_stime']) && !empty($data['create_etime'])) {
            $map['pio.create_time'] = array(array('ElT', strtotime($data['create_etime'])));
        } else if(!empty($data['create_stime']) && !empty($data['create_etime'])) {
            $map['pio.create_time'] = array(array('ElT', (strtotime($data['create_stime']))), array('EGT', strtotime($data['create_etime'])), 'and');
        }
        
        if ($data['status'] != 'all' && is_numeric($data['status'])) {
            $map['pio.status'] = $data['status'];
        }
        
        if (!empty($data['engineer_id']) && $data['engineer_id'] != 'all') {
            $map['pio.engineer_id'] = $data['engineer_id'];
        }
        
        /*关键字*/
        if (!empty(I('post.keyword'))){
            $like['pio.id'] = array('like', '%' . trim($data['keyword']). '%');
            $like['pio.number'] = array('like', '%' . trim($data['keyword']). '%');
            $like['pio.customer'] = array('like', '%' . trim($data['keyword']). '%');
            $like['pio.cellphone'] = array('like', '%' . trim($data['keyword']) . '%');
            $like['pio.engineer_id'] = array('like', '%' . trim($data['keyword']) . '%');
            $like['e.name'] = array('like', '%' . trim($data['keyword']) . '%');
            $like['e.cellphone'] = array('like', '%' . trim($data['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }
        
        $list = $model->join('pio left join engineer e on e.id = pio.engineer_id')
                ->join('left join engineer_insurance_divide eid on eid.insurance_order_id = pio.id')
                ->field('pio.id, pio.number, pio.customer, pio.cellphone as ccellphone, e.name, e.cellphone, 
                    pio.price, pio.status, pio.create_time, pio.pay_time, eid.earning')
                ->where($map)->order('pio.id desc')->select();
        
        $exorders = array();
        $exorders[] = array(
            '订单ID',
            '订单编号',
            '客户',
            '手机号码',
            '金额',
            '订单状态',
            '下单时间',
            '付款时间',
            '工程师',
            '手机号码',
            '提成'
        );
        
        $status = C('INSURANCE_STATUS');

        foreach ($list as $val) {
            $exorders[] = array(
                $val['id'],
                $val['number'],
                $val['customer'],
                $val['ccellphone'],
                $val['price'],
                $status[$val['status']],
                date('Y-m-d H:i:s', $val['create_time']),
                $val['pay_time'] > 0 ? date('Y-m-d H:i:s', $val['pay_time']) : '',
                $val['name'],
                $val['cellphone'],
                $val['earning'],
            );
        }
        
        $this->exportData('工程师推荐保险列表'.date('YmdHis'), $exorders);
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
     * 查询订单
     */
    public function findOrder()
    {
        $map['share.user_id'] = I('post.id');

        $join = 'customer on customer.id = order.customer_id';
        $joinOrder = '`order` on order.id = share.order_id';
        $count = D('share')->join($joinOrder, 'LEFT')->join($join, 'LEFT')->where($map)->count();

        if ($count == 0) {
            $rst['total'] = 0;
            $rst['rows'] = '';
        } else {
            $Page = new \Think\Page($count, 50);
            $page = $Page->show();
            $list = D('share')->join($joinOrder, 'LEFT')
                ->join($join, 'LEFT')
                ->join('left join phone p on order.phone_id = p.id')
                ->join('left join order_phomal opm on opm.order_id = order.id')
                ->join('left join phone_malfunction pm on opm.phomal_id = pm.id')
                ->join('left join malfunction m on pm.malfunction_id = m.id')
                ->where($map)->field('`order`.*, customer.name as cname, customer.cellphone as ccellphone, p.alias, group_concat(m.name) as malfunctions')
                ->limit($Page->firstRow.','.$Page->listRows)
                ->order('id desc')
                ->select();
            $rst['total'] = $count;
            $rst['rows'] = $list;
        }

        $this->ajaxReturn($rst);
    }
}