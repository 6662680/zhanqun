<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 验证 Dates: 2017-04-14
// +------------------------------------------------------------------------------------------

namespace Api\Controller;

use Think\Controller;

class VerifyController extends Controller
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

    /**
     * 获取验证码
     *
     * @return void
     */
    public function authCode()
    {
        /** $config = array(
            'fontSize' => 20,
            'length' => 4,
            'useNoise' => false,
            'useCurve' => false,
            'expire' => 60,
            'imageW' => 140,
            'imageH' => 40,
            'fontttf' => '6.ttf',
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry(); */

        $token =  md5(time() + mt_rand(99, 999));
        $code = $this->generateSmsCode();
        S($token, $code, 1800);

        $rst = array();
        $rst['token'] = $token;
        $rst['code'] = $code;

        header("Access-Control-Allow-Origin:*");
        echo json_encode($rst);
        exit;
    }

    /**
     * 验证图像验证码, 发送手机验证码
     *
     * @return void
     */
    public function smsCode()
    {
        $result = array();
        $mobile = I('post.mobile');
        $code = I('post.code');
        $token = I('post.token');

        header("Access-Control-Allow-Origin:*");
        
        if (empty($mobile)) {
            $result['status'] = 0;
            $result['msg'] = '手机号码错误！';
            echo json_encode($result);
            exit;
        }

        if (S($token) != $code) {
            $result['status'] = 0;
            $result['msg'] = '验证码错误！';
            echo json_encode($result);
            exit;
        }

        $code = $this->generateSmsCode();
        $sms = new \Vendor\aliNote\aliNote();
        $rst = $sms->send($mobile, array('product' => $mobile, 'code' => strval($code)), 'SMS_5024351');

        if ($rst) {
            S('code' . $mobile, $code, 1800);
            $result['status'] = 1;
            echo json_encode($result);
            exit;
        } else {
            $result['status'] = 0;
            $result['msg'] = '短信发送失败！请重试！';
            echo json_encode($result);
            exit;
        }
    }

    /**
     * 生成验证码
     *
     * @return void
     */
    private function generateSmsCode($length = 4)
    {
        $min = pow(10, ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);
    }
}