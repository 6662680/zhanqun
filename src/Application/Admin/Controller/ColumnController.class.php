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

class ColumnController extends BaseController
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
     * 获取栏目
     *
     * @return void
     */
    public function rows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        if (is_numeric($post['status'])) {
            $map['u.status'] = $post['status'];
        }

        if (!empty($post['keyword'])) {
            $where = array();
            $where['column_name']  = array('like', '%' . $post['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $count = M('column')->where($map)->count();
        $rst['total'] = $count;

        $list = M('column')->where($map)->order('id')->limit($this->page())->select();
        $rst['rows'] = $list;
        $this->ajaxReturn($rst);
    }


    /**
     * 增加用户
     *
     * @return void
     */
    public function add()
    {
        $rst = array();
        $data = array();
        $data = I('post.');
        $data['column_name'] = $data['column_name'];

        if (D('column')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 更新栏目
     *
     * @return void
     */
    public function save()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $user = D('column')->where($map)->find();

        if ($user) {

            if ($user['is_root'] == 1) {
                $rst['success'] = false;
                $rst['errorMsg'] = '超级管理员，禁止操作！';
                $this->ajaxReturn($rst);
            }

            if (D('column')->where($map)->save($data) !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '更新失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }

        $this->ajaxReturn($rst);
    }

   	/**
   	 * 删除用户
   	 *
   	 * @return void
   	 */
   	public function delete()
   	{
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $user = D('column')->where($map)->find();

        if ($user) {

            if ($user['is_root'] == 1) {
                $rst['success'] = false;
                $rst['errorMsg'] = '超级管理员，禁止操作！';
                $this->ajaxReturn($rst);
            }

            if (D('column')->where($map)->limit(1)->delete() !== false) {
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

}