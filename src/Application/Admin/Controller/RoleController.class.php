<?php
 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 角色 Dates: 2016-08-05
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class RoleController extends BaseController
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
        $rst = array();
        $post = I('post.');

        if (is_numeric($post['status'])) {
            $map['status'] = $post['status'];
        }

        if (!empty($post['keyword'])) {
            $map['name']  = array('like', '%' . $post['keyword'] . '%');
        }

        $count = D('role')->where($map)->count();
        $rst['total'] = $count;

        $list = D('role')->where($map)->order('id')->limit($this->page())->select();
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

        if (D('role')->add($data) !== false) {
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

        if (D('role')->where($map)->save($data) !== false) {
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

        if (D('role')->where($map)->limit(1)->delete() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 当前角色下成员
     *
     * @return void
     */
    public function inRole()
    {
        $id = I('get.id', 0);
        $sql = "select u.id, u.username, u.realname from user_role ur
            left join user u on ur.user_id=u.id 
            /*left join user_organization uo on u.id=uo.user_id
            left join organization o on uo.organization_id=o.id*/
            where ur.role_id = {$id} and u.status = 1 group by u.id";
        $list = M()->query($sql);
        $this->ajaxReturn($list);
    }

    /**
     * 非当前角色下成员
     *
     * @return void
     */
    public function notRole()
    {
        $id = I('get.id', 0);
        $sql = "select u.id, u.username, u.realname from `user` u
            where u.id not in (select user_id from user_role where role_id = {$id}) and u.status = 1 group by u.id";
        $list = M()->query($sql);
        $this->ajaxReturn($list);
    }

    /**
     * 添加成员
     *
     * @return void
     */
    public function addUser()
    {
        $rst = array();
        $data = array();
        $data['user_id'] = I('post.userId');
        $data['role_id'] = I('post.roleId');

        if (D('user_role')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 减少成员
     *
     * @return void
     */
    public function removeUser()
    {
        $rst = array();
        $map = array();
        $map['user_id'] = I('post.userId');
        $map['role_id'] = I('post.roleId');

        if (D('user_role')->where($map)->limit(1)->delete() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 权限节点
     *
     * @return void
     */
    public function nodes()
    {
        $map = array();
        $map['status'] = 1;
        $nodes = M('node')->where($map)->order('id')->getField('id, pid, category, alias as text');

        $map = array();
        $map['role_id'] = I('get.id', 0);
        $roleNodes = M('role_node')->where($map)->getField('node_id', true);

        /** 选中的节点 */
        foreach ($nodes as $key => $value) {

            if (in_array($key, $roleNodes)) {
                $nodes[$key]['checked'] = true;
            }

/**             if ($value['category'] == 2) {
                $nodes[$key]['state'] = 'closed';
            } else {
                $nodes[$key]['state'] = 'open';
            } */
        }

        $rst = \Org\Tool\Tree::makeTree($nodes);
        $this->ajaxReturn($rst);
    }

    /**
     * 授权
     *
     * @return void
     */
    public function authorization()
    {
        $flag = true;
        $roleId = I('post.roleId', 0);
        $nodes = I('post.nodes');

        M()->startTrans();

        $rst = array();
        $data = array();
        $map = array();
        $map['role_id'] = $roleId;

        if (M('role_node')->where($map)->limit('1000')->delete() === false) {
            M()->rollback();
            $flag = false;
            $rst['errorMsg'] = '授权失败！(清除旧权限)';
        }

        if (!empty($nodes) && count($nodes) >= 1) {
            foreach ($nodes as $key => $value) {
                $item = array();
                $item['role_id'] = $roleId;
                $item['node_id'] = $value;
                $data[] = $item;
            }

            if (M('role_node')->addAll($data) === false) {
                M()->rollback();
                $flag = false;
                $rst['errorMsg'] = '授权失败！(写入新权限)';
            }
        }

        if ($flag) {
            M()->commit();
            $rst['success'] = true;
        } else {
            $rst['success'] = fasle;
        }

        $this->ajaxReturn($rst);
    }

}