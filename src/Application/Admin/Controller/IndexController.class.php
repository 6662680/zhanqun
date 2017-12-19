<?php
namespace Admin\Controller;

use Admin\Controller;

class IndexController extends BaseController
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
     * 个人信息页面
     * @author liyang
     * @return void
     */
    public function myinfo()
    {
        if (I('post.')) {
            $model = M('user');
            $model->find(session('userId'));
            if ($model->realname ==  I('post.realname') &&  $model->telphone == I('post.telphone')) {
                $rst['success'] = false;
                $rst['errorMsg'] = '名称和手机号与之前这一致';
            } else {
                $model->realname = I('post.realname');
                $model->telphone = I('post.telphone');
                if ($model->save()){
                    $rst['success'] = ture;
                    $rst['errorMsg'] = '修改成功';
                } else {
                    $rst['success'] = false;
                    $rst['errorMsg'] = '修改失败';
                }
            }

            $this->ajaxReturn($rst);
        } else {

            if ($_SESSION['isRoot'] == 1) {
                $_SESSION['userInfo']['role'] = '超级管理员';
            }
            $model = M('user');
            $model->join('left join `user_role` ur on user.id = ur.user_id');
            $model->join('left join `role` on ur.role_id = role.id');
            $model->where(array('user.id' => $_SESSION['userInfo']['id']));
            $model->field('user.*,role.name as role');
            $role = $model->find();

            $_SESSION['userInfo']['role'] = $role['role'];
            $_SESSION['userInfo']['organization_name'] = $role['organization_name'];

            $this->display();
        }
    }

    /**
     * 修改密码
     * @author liyang
     * @return void
     */
    public function password()
    {
        if (I('post.')) {
            $model = M('user');
            $model->find(session('userId'));

            if (I('post.new_password') == I('post.repeat_password')){

                if ($model->password == createPassword(I('post.password'))) {

                    if ($model->password != createPassword(I('post.new_password'))) {

                        $model->password = createPassword(I('post.new_password'));

                        if ($model->save()){
                            $rst['success'] = ture;
                            $rst['errorMsg'] = '修改成功';
                        } else {
                            $rst['success'] = false;
                            $rst['errorMsg'] = '修改失败';
                        }

                    } else {
                        $rst['success'] = false;
                        $rst['errorMsg'] = '新密码与旧密码相同';
                    }

                } else {
                    $rst['success'] = false;
                    $rst['errorMsg'] = '旧密码输入错误';
                }
            } else {
                $rst['success'] = false;
                $rst['errorMsg'] = '两次密码输入不一致';
            }

            $this->ajaxReturn($rst);

        } else {
            $this->display();
        }
    }
}