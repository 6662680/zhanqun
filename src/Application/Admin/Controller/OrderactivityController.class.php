<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 活动订单 Dates: 2016-10-27
// +------------------------------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Controller;

class OrderactivityController extends BaseController
{
    /** 订单状态 */
    private $status = array(
        '-1' => '取消',
        '1' => '下单',
        '6' => '入库',
    );

    /** 地区 */
    private $address;

    /** 表 */
    private $modelName = 'order_activity';

    /**
     * 构造函数
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->address = C("ADDRESS");
    }

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
    public function row()
    {
        $map = array();
        $post = I('post.');

        if (!empty($post['start_time']) && empty($post['end_time'])) {
            $map['oa.create_time'] = array('EGT', strtotime($post['start_time']));
        } else if(!empty($post['end_time']) && empty($post['start_time'])) {
            $map['oa.create_time'] = array('ELT', strtotime($post['end_time']) + 24 * 60 * 60 - 1);
        } else if(!empty($post['start_time']) && !empty($post['end_time'])) {
            $map['oa.create_time'] = array(array('EGT', strtotime($post['start_time'])), array('ELT', strtotime($post['end_time']) + 24 * 60 * 60 - 1));
        }

        if (!empty($post['city'])) {
            $map['oa.city'] = $post['city'];
        }

        if (isset($post['status']) &&  $post['status'] != 2) {
            $map['oa.status'] = $post['status'];
        }

        if (!empty($post['keyword'])) {
            $like['c.address'] = array('LIKE', '%' . trim($post['keyword']) . '%');
            $like['c.cellphone'] = array('LIKE', '%' . trim($post['keyword']) . '%');
            $like['c.name'] = array('LIKE', '%' . trim($post['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        $model = D($this->modelName);
        $join = ' oa left join customer c on oa.customer_id = c.id left join address a on oa.city=a.id';
        $rst['total'] = $model->join($join)->where($map)->count();

        $rst['rows'] = $model->join($join)->where($map)->limit($this->page())
            ->order('id desc')->field('oa.*, a.name as city, c.name as username, c.cellphone, c.address')->select();

        $this->ajaxReturn($rst);
    }

    /**
     * 导出
     *
     * @return void
     */
    public function export()
    {
        $type = array(1 => '一元活动体验', 2 => '一元体验钢化膜', 3 => '一元体验数据线');
        $map = array();
        $post = I('post.');

        if (!empty($post['start_time']) && empty($post['end_time'])) {
            $map['oa.create_time'] = array('EGT', strtotime($post['start_time']));
        } else if(!empty($post['end_time']) && empty($post['start_time'])) {
            $map['oa.create_time'] = array('ELT', strtotime($post['end_time']) + 24 * 60 * 60 - 1);
        } else if(!empty($post['start_time']) && !empty($post['end_time'])) {
            $map['oa.create_time'] = array(array('EGT', strtotime($post['start_time'])), array('ELT', strtotime($post['end_time']) + 24 * 60 * 60 - 1));
        }

        if (!empty($post['city']) && $post['city'] != 'all') {
            $map['oa.city'] = $post['city'];
        }

        if (isset($post['status']) &&  $post['status'] != 2) {
            $map['oa.status'] = $post['status'];
        }

        if (!empty($post['keyword'])) {
            $like['c.address'] = array('LIKE', '%' . trim($post['keyword']) . '%');
            $like['c.cellphone'] = array('LIKE', '%' . trim($post['keyword']) . '%');
            $like['c.name'] = array('LIKE', '%' . trim($post['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        $model = D($this->modelName);
        $join = ' oa left join customer c on oa.customer_id = c.id';
        $list = $model->join($join)->where($map)->order('id desc')->field('oa.*, c.name as username, c.cellphone, c.address')->select();

        $exports = array();
        $title = array(
            'ID',
            '商品名称',
            '机型',
            '客户名',
            '手机号码',
            '地区',
            '详细地址',
            '下单时间',
            '结单时间',
            '状态',
            '备注',
        );

        $exports[] = $title;

        $address = D('Api/address');
        foreach ($list as $key => $value) {
            $item = array();
            $item[] = $value['id'];
            $item[] = $type[$value['type']];
            $item[] = $value['phone_name'];
            $item[] = $value['username'];
            $item[] = $value['cellphone'];
            $item[] = $address->idAddress($value['province']);
            $item[] = $address->idAddress($value['province']) . $address->idAddress($value['city']) . $address->idAddress($value['county']);
            $item[] = date('Y-m-d H:i:s', $value['create_time']);
            $item[] = date('Y-m-d H:i:s', $value['clearing_time']);
            $item[] = $this->status[$value['status']];
            $item[] = $value['remark'];
            $exports[] = $item;
        }

        $this->exportData('活动订单-'.date('Y-m-h-H-i-s'), $exports);
    }

    /**
     * 取消 
     *
     * @return void
     */
    public function cancel()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('post.id');

        $item = D($this->modelName)->where($map)->find();

        if ($item === false) {
            $this->error('未查询到此记录！');
        }

        $data = array();
        $data['status'] = -1;

        if (D($this->modelName)->where($map)->limit(1)->save($data) === false) {
            $rst['success'] = false;
            $rst['errorMsg'] = '更新失败！';
            $this->ajaxReturn();
        } else {
            $rst['success'] = true;
            $this->ajaxReturn($rst);
        }
    }
}