<?php

// +------------------------------------------------------------------------------------------
// | Author: TCG <tianchunguang@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: 订单取消原因控制器 Dates: 2016-09-26
// +------------------------------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Controller;

class OrderclosereasonController extends BaseController
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
    
        $count = M('order_close_reason')->where($map)->count();
        $rst['total'] = $count;
    
        $list = M('order_close_reason')->where($map)->limit($this->page())->select();
        $rst['rows'] = $list;
    
        $this->ajaxReturn($rst);
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
    
        if (M('order_close_reason')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
    
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
        $data = I('post.');
        $item = M('order_close_reason')->where($map)->find();
    
        if ($item) {
            
            if (M('order_close_reason')->where($map)->save($data) !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '编辑失败！';
            }
    
            $this->ajaxReturn($rst);
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
        $rst = array();
        $map = array();
        $map['id'] = I('post.id');
        $item = M('order_close_reason')->where($map)->find();
    
        if ($item) {
            if (M('order_close_reason')->where($map)->delete() !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '删除失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }
    
        $this->ajaxReturn($rst);
    }
    
    /**
     * 订单关闭原因
     */
    public function orderCloseReasons()
    {
        $list = M('order_close_reason')->field('id, name')->select();
        $this->ajaxReturn($list);
    }
}