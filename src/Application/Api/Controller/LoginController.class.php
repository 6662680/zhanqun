<?php
namespace Api\Controller;
use Api\Model\CheckModel;
use Api\Model\RequestModel;
use Think\Controller;
use Api\Extend\Vendor\aliNote;
/**
 * 搜索结果控制
 * author :liyang
 * time : 2016-8-4
 */

class LoginController extends BaseController 
{
    public function __construct()
    {
        parent::__construct();

        /** 初始化redis */
        S(
            array(
                'type' => 'redis',
                'host' => C('REDIS_HOST'),
                'port' => C('REDIS_PORT'),
                'expire' => 3600,
            )
        );
    }

    /*
     * 用户登录
     * */
    public function index()
    {
        $model = M('customer');
        if (I('post.code') != S('code'.I('post.mobile')))$this->_error(401,'验证码错误');
        if (!$model->where(['cellphone'=>I('post.mobile')])->find()) $this->_error(401,'您没有维修记录');
        S('codeSwitch'.I('post.mobile'),true);
        S('code'.I('post.mobile'),NULL);
        $this->_callBack();
    }

    /*
     * 手机用户查询记录
     * */
    public function index_mobile()
    {
        $mobile = I('get.mobile');
        $token = I('get.accessToken');

        if (empty($mobile) && empty($token)) {
            $this->_error(503,'手机号错误');
        }

        if (!empty($token)) {
            $userinfo = D('yashenghuo')->checkLogin($token);
            $mobile = $userinfo['data']['mobile'];

            $rst = D('order')->getThreeOrder($mobile, 5);
        } else {
            $rst = D('order')->getOrder($mobile, 5);
        }

        foreach($rst as &$value) {

            /*状态变更*/
            if ($value['status'] == -1) {
                $value['status'] = '订单取消';
            }

            if ($value['status'] == 1 || $value['status'] == 2 ) {
                $value['status'] = '等待维修';
            }

            if ($value['status'] >= 3 && $value['status'] < 6) {
                $value['pay'] = true;
                $value['status'] = '我要支付';

            }

            if ($value['status'] >= 6) {
                $value['status'] = '订单完成';
            }

            if (!empty($value['img'])) {
                $value['img'] = 'http://'.$_SERVER["SERVER_NAME"].$value['img'];
            }

        }

        if (empty($rst)) {
            $this->_error(503,'您没有维修记录');
        }

        $this->_callBack($rst);
    }


    /*
     * 对比验证码
     * */
    public function vcode()
    {
        if(S('code'.I('post.mobile')) == I('post.code')){
            $this->_callBack();
        } else {
            $this->_error(401,'验证码错误');
        }
    }
}