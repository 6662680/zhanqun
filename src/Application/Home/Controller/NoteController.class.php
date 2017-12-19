<?php

// +------------------------------------------------------------------------------------------
// | Author: longDD <longdd_love@163.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: 短息接口 Dates: 2015-08-05
// +------------------------------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

class NoteController extends Controller
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
                'prefix' => 'note_',
                'expire' => 3600,
            )
        );
    }

    /**
     * 发送验证码
     *
     * @param str $phoneNumber 手机号码
     * @return void
     */
    public function sendCode($phoneNumber = null)
    {
        return false;
        
        if (!preg_match("/^1[34578][0-9]{9}$/", $phoneNumber)) {
            $result['status'] = 0;
            $result['msg'] = '手机号码错误！';
        } else {
            /** 一分钟之内同一号码只能发送一次 */
            $numberInfo = S($phoneNumber);
            $result = array();

            if ($phoneNumber && (time() <= ($numberInfo['time'] + 60))) {
                $result['status'] = 0;
                $result['msg'] = '请60秒后再次发送！';
            } else {
                $code = $this->createCode();
                $time = time();

                $numberInfo = array(
                    'time' => $time,
                    'code' => $code,
                    'isVerify' => false,
                );

                S($phoneNumber, $numberInfo);

				$msg = array();
				$msg['code'] = $code;
				$msg['product'] = '闪修侠';

				$this->sendNote($phoneNumber, $msg, 'SMS_5024350');
				$result['status'] = 1;
			}
		}

        echo json_encode($result);
    }

    /**
     * 下单成功 - 提示信息
     *
     * @param string $phoneNumber 手机号码
     * @param string $orderNumber 订单号码
     * @return boolean
     */
    public function sendOrderInfo($phoneNumber = null, $orderNumber = null)
    {
        if (empty($orderNumber) || empty($phoneNumber)) {
            return false;
        }

		/** $msg = "您好，你的订单（" . $orderNumber . "）已下单成功，您可以在网站查询订单状态，如需帮助请联系4008433580"; */
		$msg = array();
		$msg['orderNumber'] = $orderNumber;
		return $this->sendNote($phoneNumber, $msg, 'SMS_5225044');
	}

	/**
	 * 发送短信
	 *
	 * @return void
	 */
	private function sendNote($number, $msg, $template)
	{
		/** $node = new \Org\Util\Note();
		return $node->send($number, $msg); */

		$note = new \Vendor\aliNote\aliNote();
        return $note->send($number, $msg, $template);
	}

    /**
     * 创建验证码
     *
     * @return void
     */
    private function createCode()
    {
        return rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
    }

    /**
     * 验证手机
     *
     * @param str $phoneNumber 手机号码
     * @param str $code 验证码
     * @return void
     */
    public function verifyCode($phoneNumber = null, $code = null)
    {
        $result = array();
        if (empty($phoneNumber) || empty($code)) {
            $result['status'] = 0;
            $result['msg'] = "验证码错误！！！";
        } else {
            $numberInfo = S($phoneNumber);

            if ($numberInfo['code'] == $code) {
                $result['status'] = 1;
                $numberInfo['isVerify'] = true;
                S($phoneNumber, $numberInfo);
                $_SESSION['phoneNumber']=$phoneNumber;
            } else {
                $result['status'] = 0;
                $result['msg'] = "验证码错误！";
            }
        }

        echo json_encode($result);
    }
}