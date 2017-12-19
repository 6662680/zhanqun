<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 网站联盟公用基类
 * author : Tom
 * time : 2015-11-30
 */
class BaseController extends Controller
{

    public function _initialize()
    {
        //初始化数据
        $this->assign('banner',  CONTROLLER_NAME);
        //获取控制器名称
        $this->assign('ACTION_NAME', ACTION_NAME);
    }

    public function is_user()
    {
        $map=array();
        $map['user'] = $_SESSION['share_username'];

        if(!$map['user']){
            $this->error('登录超时', 'index');
        }
    }
}