<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 登录 Dates: 2016-07-13
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Think\Controller;

class LoginController extends Controller 
{
	/**
	 * 登录页
	 *
	 * @return void
	 */
    public function index()
    {
        $this->display('login');
    }

    /**
     * 登录处理
     *
     * @return void
     */
    public function handle()
    {
    	$username = I('username');
    	$password = I('password');

        /* 错误次数统计 */
        $map = array();
        $map['username'] = $username;
        $map['is_success'] = 0;
        $map['time']  = array('gt', strtotime('today'));

        if (M('login_log')->where($map)->count() > 3) {
            $this->error('今日登陆错误次数超过3次，账号已锁定，请连续管理员解禁！');
        }

        /* 用户信息 */
        $map = array();
        $map['username'] = $username;
        $map['status'] = 1;
    	$user = M('user')->where($map)->find();

        /* 登陆信息 */
        $info = array();
        $info['username'] = $username;
        $info['password'] = $password;

    	if ($user !== false && count($user) > 0) {

    		if ($user['password'] == createPassword($password)) {
    			session('userId', $user['id']);
    			session('userInfo', $user);

                if ($user['is_root']) {
                    session('isRoot', true);
                }

                /* 更新用户信息 */
                $this->updateUser($user['id']);
                /* 更新登陆日志 */
                $this->updateLogin($user, 1);
                /* 授权 */
                \Org\Tool\Permission::authorization();
//                /** 组织信息 */
//                $this->orgInfo($user['id'], $user['is_root']);

    			$this->redirect('admin/index/index');
    		} else {
                $this->updateLogin($info, 0);
    			$this->error('密码错误！');
    		}
    	} else {
            $this->updateLogin($info, 0);
    		$this->error('用户不存在！');
    	}
    }

    /**
     * 组织信息
     *
     * @return void
     */
    public function orgInfo($userId, $isRoot = false)
    {
        $addresses = array();
        $organizations = array();

        /** 全国 */
        $nationwide = M()->query("select o.id, o.type, o.name, o.alias, o.brief, o.province, o.city, o.county from user_organization uo
            left join organization o on uo.organization_id=o.id
            where o.status=1 and o.city=9999 and uo.user_id={$userId}");

        if ($isRoot || ($nationwide && count($nationwide) > 0)) {
            $orgs = M()->query("select o.id, o.type, o.name, o.alias, brief, province, o.city, county, a.name as cityname from organization o
                left join address a on o.city = a.id where status=1");
        } else {
            $orgs = M()->query("select o.id, o.type, o.name, o.alias, o.brief, o.province, o.city, o.county, a.name as cityname from user_organization uo
                left join organization o on uo.organization_id=o.id
                left join address as a on o.city = a.id 
                where o.status=1 and uo.user_id={$userId}");
        }

        if (!empty($orgs)) {
            foreach ($orgs as $key => $value) {
                $org = array();
                $org['id'] = $value['id'];
                $org['alias'] = $value['alias'];
                $organizations[$org['id']] = $org;

                if ($value['type'] == 1) {
                    $address = array();
                    $address['city'] = $value['city'];
                    $address['cityname'] = $value['cityname'];
                    $addresses[$value['city']] = $address;
                }
            }

            if (isset($addresses['9999']) && current($addresses) != $addresses['9999']) {
                $addresses['9999']['cityname'] = '全国';
                $address = array($addresses['9999']);
                unset($addresses['9999']);
                $addresses = array_merge($address, $addresses);
            }
        }

        session('organizations', $organizations);
        session('addresses', array_values($addresses));

    }

    /**
     * 更新用户信息
     *
     * @return void
     */
    private function updateUser($userId)
    {
        $map = array();
        $map['id'] = $userId;
        $data = array();
        $data['last_login_time'] = time();
        $data['last_login_ip'] = get_client_ip(0, 1);
        M('user')->where($map)->save($data);
    }

    /**
     * 更新登陆日志
     *
     * @return void
     */
    private function updateLogin($info, $isSuccess)
    {
        $data = array();

        if ($isSuccess) {
            $data['is_success'] = 1;
            $data['user_id'] = $info['id'];
        } else {
            $data['is_success'] = 0;
            $data['username'] = $info['username'];
            $data['password'] = $info['password'];
        }
        
        $data['time'] = time();
        $data['ip'] = get_client_ip(0, 1);
        M('login_log')->add($data);
    }

    /**
     * 退出登录
     *
     * @return void
     */
    public function logout()
    {
        session_start();
        session_destroy();

        $this->redirect('Admin/Login/index');
    }

    /**
     * websocket
     *
     * @return void
     */
    // public function websocket() 
    // {
    //     $rst = array();

    //     if (!session('?adminId') || !session('?adminInfo.id')) {
    //         $rst['status'] = 0;
    //         $rst['msg'] = '用户未登录！';
    //     } else {
    //         $accessList = $_SESSION['_ACCESS_LIST'];

    //         if (isset($accessList[strtoupper(APP_NAME)][strtoupper('admin')]['GROUP'][strtoupper('index')][strtoupper('websocket')])) {
    //             $rst['status'] = 1;
    //             /** 用户信息 */
    //             $rst['adminInfo'] = session('adminInfo');
    //             /** 链接地址 */
    //             $rst['websocketAddress'] = C('WEBSOCKET_ADDRESS');
    //         } else {
    //             $rst['status'] = 0;
    //             $rst['msg'] = '没有操作权限！';
    //         }
    //     }

    //     echo json_encode($rst);
    // }

}