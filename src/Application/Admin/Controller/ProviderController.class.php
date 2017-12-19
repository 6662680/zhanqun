<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 供应商 Dates: 2016-09-06
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class ProviderController extends BaseController
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

        $count = M('provider')->where($map)->count();
        $rst['total'] = $count;

        $list = M('provider')->where($map)->limit($this->page())->select();
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

        if (M('provider')->add($data) !== false) {
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
    public function save()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $item = D('provider')->where($map)->find();

        if ($item) {

            if (D('provider')->where($map)->save($data) !== false) {
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
        $item = D('provider')->where($map)->find();

        if ($item) {

            if (D('provider')->where($map)->limit(1)->delete() !== false) {
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