<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 到位订单 Dates: 2016-12-08
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;
use Think\Exception;

class DaoweiController extends BaseController
{
    /**
     * 到位页面
     *
     * @return void
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 到位数据
     *
     * @return void
     */
    public function rows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        if (!empty($post['starttime'])) {
            $map['do.gmt_create'] = array('EGT', strtotime($post['starttime']));
        }

        if (!empty($post['endtime'])) {
            $map['do.gmt_create '] = array('ELT', strtotime($post['starttime']));
        }

        if (is_numeric($post['status']) && $post['status'] != 'all') {
            $map['do.order_status'] = $post['status'];
        }
        
        if (!empty($post['city'] && $post['city'] != 'all')) {
            $map['du.address'] = array('like', '%'. trim($post['city']) . '%');
        } else {
            
        }
        
        if (!empty($post['keyword'])) {
            $like['do.id'] = array('eq', trim($post['keyword']));
            $like['do.order_no'] = array('like', '%' . trim($post['keyword']). '%');
            $like['du.contact_name'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['du.mobile'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['du.address'] = array('like', '%' . trim($post['keyword']) . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        $join = 'do left join daowei_user du on du.order_no = do.order_no 
            left join `order` o on o.id = do.sxx_order_id';

        $count = M('daowei_order')->join($join)->where($map)->count();
        $rst['total'] = $count;

        $list = M('daowei_order')->join($join)->where($map)->limit($this->page())
            ->field('do.*, du.*, o.number as ssx_number, o.end_time as ssx_end_time')->order('do.id desc')->select();
        $rst['rows'] = $list;
        
        $this->ajaxReturn($rst);
    }
    
    /**
     * 到位服务内容
     */
    public function serviceList()
    {
        $order_no = trim(I('get.order_no'));
        
        if (!$order_no) {
            $this->ajaxReturn(array());
        } 
        
        $list = M('daowei_service')->where(array('order_no' => $order_no))->select();
        $this->ajaxReturn($list);
    }
}