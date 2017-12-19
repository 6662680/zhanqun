<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 组织 Dates: 2016-08-15
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class OrgController extends BaseController
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

        if (is_numeric($post['status'])) {
            $map['status'] = $post['status'];
        }

        $count = M('organization')->where($map)->count();
        $rst['total'] = $count;

        $list = M('organization')->where($map)->limit($this->page())->select();
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
        
        if (M('organization')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
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
        $list = M('address')->where($map)->select();

        if (!$pid) {
            array_unshift($list, array('id' => '9999', 'name' => '全国'));
        } else if($pid == '9999'){
            $list[] = array('id' => '9999', 'name' => '全国');
        }

        $this->ajaxReturn($list);
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
        $item = D('organization')->where($map)->find();

        if ($item) {

            if (D('organization')->where($map)->save($data) !== false) {
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
        $item = D('organization')->where($map)->find();

        if ($item) {

            if (D('organization')->where($map)->limit(1)->delete() !== false) {
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
     * 组织
     *
     * @return void
     */
    public function organization()
    {
        $list = M('organization')->select();
        $this->ajaxReturn($list);
    }
}