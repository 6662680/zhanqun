<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 极光推送 Dates: 2015-09-22
// +------------------------------------------------------------------------------------------

namespace Vendor\Jpush;

require_once 'Jpush/autoload.php';

use JPush\Client;
use JPush\Exceptions\APIRequestException;
use JPush\Exceptions\APIConnectionException;

class Jpush
{
    /** 秘钥 */
    public $appKey = 'fc5286cb58d6ef74befc18f0';
    /** 秘钥 */
    public $masterSecret = '81277447bc09e2b5fef64663';
    /** Jpush对象 */
    public $jpush;

    /**
     * 构造函数
     *
     * @return void
     */
    public function __construct()
    {
        $this->jpush = new Client($this->appKey, $this->masterSecret);
    }

    /**
     * 发送订单推送
     *
     * @param string $registrationId 设备标识
     * @param int $orderId 订单ID
     * @return boolean 推送成功返回 true, 推送失败返回 false
     */
    public function push($registrationId, $orderId)
    {
        $registration_ids = array();
        $registration_ids[] = $registrationId;

        $data = array();
        $data['orderId'] = $orderId;
        
        try {
            $result = $this->jpush->push()
                        ->setPlatform('all')
                        //->addAllAudience()
                        ->addRegistrationId($registration_ids)
                        ->iosNotification('您有新的订单!', array(
                            'sound' => 'unbelievable.caf',
                            'badge' => 1,
                            'content-available' => true,
                            'extras' => $data,
                        ))
                        ->options(array(
                            'sendno' => time(),
                            "apns_production" => true
                        ))
                        ->send();

            return true;
        } catch (APIRequestException $e) {
//             $log = "Push Fail:"
//                 .'Http Code : ' . $e->httpCode
//                 . '-code : ' . $e->code
//                 . '-Error Message : ' . $e->message
//                 . '-Response JSON : ' . $e->json
//                 . '-rateLimitLimit : ' . $e->rateLimitLimit
//                 . '-rateLimitRemaining : ' . $e->rateLimitRemaining
//                 . '-rateLimitReset : ' . $e->rateLimitReset;
            
            \Think\Log::record($e, 'ERR');
            return false;
        } catch (APIConnectionException $e) {
//             $log = 'Push Fail: '
//                 . 'Error Message: ' . $e->getMessage()
//                 . 'IsResponseTimeout: ' . $e->isResponseTimeout;

            \Think\Log::record($e, 'ERR');
            return false;
        }
    }

    public function pushMessage($title, $content, $msg_id, $registration_ids)
    {
        $message = array(
            'msg_title' => $title,
            'msg_content' => $content,
            'msg_pageType' => 1,
            'msg_id' => $msg_id,
            'msg_time' => date("Y-m-d H:i:s")
        );

        try {
            $this->jpush->push()
                ->setPlatform('all')
                ->addRegistrationId($registration_ids)
                ->iosNotification($title, array(
                    'sound' => 'sound',
                    'badge' => 1,
                    'content-available' => true,
                    'extras' => $message,
                ))
                ->options(array(
                    'sendno' => time(),
                    "apns_production" => true
                ))
                ->send();

            return true;
        } catch (APIRequestException $e) {

            \Think\Log::record($e, 'ERR');
            return false;
        } catch (APIConnectionException $e) {

            \Think\Log::record($e, 'ERR');
            return false;
        }
    }
}