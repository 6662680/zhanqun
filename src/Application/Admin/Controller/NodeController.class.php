<?php
 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2016-08-05
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class NodeController extends BaseController
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
     * 获取
     *
     * @return void
     */
    public function rows()
    {
        $map = array();
        $nodes = M('node')->where($map)->order('id')->select(array('index' => 'id'));
        $rst = \Org\Tool\Tree::makeTree($nodes);
        $this->ajaxReturn($rst);
    }

    /**
     * 树形
     *
     * @return void
     */
    public function tree()
    {
        $map = array();
        $map['status'] = 1;
        $nodes = M('node')->where($map)->order('id')->getField('id, pid, alias as text');
        $rst = \Org\Tool\Tree::makeTree($nodes);
        array_unshift($rst, array('id' => 0, 'text' => '根目录'));
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
        $data['name'] = trim($data['name']);
        $data['alias'] = trim($data['alias']);
        $data['action'] = strtolower(trim($data['action']));

        if (M('node')->add($data) !== false) {
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
        $data['name'] = trim($data['name']);
        $data['alias'] = trim($data['alias']);
        $data['action'] = strtolower(trim($data['action']));

        if (D('node')->where($map)->save($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '更新失败！';
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

        if (D('node')->where($map)->limit(1)->delete() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
        }

        $this->ajaxReturn($rst);
    }

}