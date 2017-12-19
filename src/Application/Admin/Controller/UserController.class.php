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

class UserController extends BaseController
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
     * 获取用户
     *
     * @return void
     */
    public function rows()
    {
        $map = array();
        $rst = array();
        $post = I('post.');

        if (!empty($post['organization_id'])) {
            $map['uo.organization_id'] = $post['organization_id'];
        }

        if (is_numeric($post['status'])) {
            $map['u.status'] = $post['status'];
        }

        if (!empty($post['keyword'])) {
            $where = array();
            $where['u.username']  = array('like', '%' . $post['keyword'] . '%');
            $where['u.realname']  = array('like', '%' . $post['keyword'] . '%');
            $where['u.telphone']  = array('like', '%' . $post['keyword'] . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $count = D('user')->where($map)->count();
        $rst['total'] = $count;

        $list = D('user')->field('u.*, group_concat(r.name) as role_name')
//            ->join('u left join user_organization uo on u.id=uo.user_id left join organization o on uo.organization_id=o.id')
            ->join('u left join user_role ur on ur.user_id = u.id')
            ->join('left join role r on ur.role_id = r.id')
            ->where($map)->group('u.id')->order('id')->limit($this->page())->select();
        $rst['rows'] = $list;
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
        array_unshift($list,array('id' => '','alias' => '全部'));
        //pr($list);die();
        $this->ajaxReturn($list);
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
        $data['password'] = createPassword($data['password']);
        $data['is_root'] = 0;
        $data['create_time'] = time();
        
        if (D('user')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 用户是否存在
     *
     * @return void
     */
/**     public function exists()
    {
        $username = trim($_POST['username']);
        $info = D('user')->where(array('username' => $username))->find();

        if (!empty($info)) {
            echo 'false';exit;
        } else {
            echo 'true';exit;
        }
    } */

    /**
     * 更新用户
     *
     * @return void
     */
    public function save()
    {
        $rst = array();
        $map = array();
        $map['id'] = I('get.id/d');
        $data = I('post.');
        $user = D('user')->where($map)->find();

        if ($user) {

            if ($user['is_root'] == 1) {
                $rst['success'] = false;
                $rst['errorMsg'] = '超级管理员，禁止操作！';
                $this->ajaxReturn($rst);
            }

            if (D('user')->where($map)->save($data) !== false) {
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
        $user = D('user')->where($map)->find();

        if ($user) {

            if ($user['is_root'] == 1) {
                $rst['success'] = false;
                $rst['errorMsg'] = '超级管理员，禁止操作！';
                $this->ajaxReturn($rst);
            }

            if (D('user')->where($map)->limit(1)->delete() !== false) {
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
     * 重置密码
     *
     * @return void
     */
    public function reset()
    {
        $id = I('post.id');
        $rst = array();
        $map = array();
        $map['id'] = $id;
        $user = D('user')->where($map)->find();

        if ($user) {

            if ($user['is_root'] == 1) {
                $rst['success'] = false;
                $rst['errorMsg'] = '超级管理员，禁止操作！';
                $this->ajaxReturn($rst);
            }

            $data = array();
            $data['password'] = createPassword('12345678');

            if (D('user')->where($map)->limit(1)->save($data) !== false) {
                $rst['success'] = true;
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '重置失败！';
            }
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 加入的组织
     *
     * @return void
     */
    public function joined()
    {
        $id = I('get.id', 0);
        $sql = "select o.id, o.name, o.type from user_organization uo 
            left join organization o on uo.organization_id=o.id 
            where uo.user_id = {$id} and o.status = 1";
        $list = M()->query($sql);
        $this->ajaxReturn($list);
    }

    /**
     * 未加入的组织
     *
     * @return void
     */
    public function unorganized()
    {
        $id = I('get.id', 0);
        $sql = "select id, name, type from organization where id not in (select organization_id from user_organization where user_id = {$id}) and status = 1";
        $list = M()->query($sql);
        $this->ajaxReturn($list);
    }

    /**
     * 加入组织
     *
     * @return void
     */
    public function join()
    {
        $rst = array();
        $data = array();
        $data['user_id'] = I('post.userId');
        $data['organization_id'] = I('post.organizationId');

        if (D('user_organization')->add($data) !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '添加失败！';
        }
        $this->ajaxReturn($rst);
    }

    /**
     * 退出组织
     *
     * @return void
     */
    public function exits()
    {
        $rst = array();
        $map = array();
        $map['user_id'] = I('post.userId');
        $map['organization_id'] = I('post.organizationId');

        if (D('user_organization')->where($map)->limit(1)->delete() !== false) {
            $rst['success'] = true;
        } else {
            $rst['success'] = false;
            $rst['errorMsg'] = '删除失败！';
        }
        $this->ajaxReturn($rst);
    }

    public function unlock()
    {
        $userName = trim(I('request.username'));
        $rst = array(
            'code' => -1,
            'msg' => '账号未锁定'
        );

        if($userName) {
            $map = array(
                'username' => $userName,
                'is_success' => 0,
                'time' => array('egt', strtotime(date("Y-m-d")))
            );
            $res = M('login_log')->where($map)->delete();

            if ($res > 0) {
                $rst = array('code' => 0, 'msg' => '解锁成功');
            }
        }

        $this->ajaxReturn($rst);
    }

}