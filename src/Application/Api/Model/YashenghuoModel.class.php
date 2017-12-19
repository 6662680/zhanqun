<?php

// +------------------------------------------------------------------------------------------ 
// | Author: liyang <664577655@QQ.com>
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 雅生活 Dates: 2016-12-08
// +------------------------------------------------------------------------------------------

namespace Api\Model;

class YashenghuoModel
{
    public static $app_id = "d40fde887138902da6cb91d0eb432622";
    public static $app_ser = "XHVEVASynmHYh3n6yqCFUtLw6mZG5KPAQcsWK6rxeiIXy0Ef7Den21m3Ll3rFmHr";
    public static $api_url = "http://api.4006983383.com/api.do?";
    public static $merchantId = "17BF8E8A9B244507B2C0C98F8D1F2F6C";
    public static $uid = "uid";
    public static $ver = "1.0";

    /**
     * 验证用户登录接口
     */
    public function checkLogin($token)
    {
        $ret = array ();
        $param = array('accessToken' => $token);

        if (empty($param['accessToken'])) {

            $ret ['code'] = - 1002;
            $ret ['msg_cn'] = 'accessToken不能为空！';
        } else {
            $time = time ();
            $api_params = array (
                'accessToken' => $param['accessToken']
            );
            $ret = $this->requestProcess('life.login.checkLogin', $api_params);
        }

        return $ret;
    }
    /**
     * 对数组排序
     *
     * @param $para 排序前的数组
     *        	return 排序后的数组
     */
    public static function argSort($para)
    {
        ksort ( $para );
        reset ( $para );

        return $para;
    }

    /*
     * 支付
     */
    public function pay($number)
    {
        $model = M('order');

        $model->where(array('number' => array('eq', $number)));
        $model->field('id,actual_price,phone_name');
        $order = $model->find();

        $params = array(
            'key1' => $order['id'],
            'key2' => $order['actual_price'] * 100,
            'key3' => 'http://' . $_SERVER["SERVER_NAME"] . '/api/yashenghuo/callbackUrl',
            'key4' => (string) D('order')->generate_code(9),
            'key5' => self::$app_id,
            'key6' => self::$merchantId,
            'key7' => $order['phone_name'].'维修',
            'key8' => $order['phone_name'].'维修',
            'key9' => $_SERVER['HTTP_REFERER'].'#/personal?',
        );

        /*安全码+商家密匙*/
        S('securityCode' . $params['key1'], md5($params['key4'] . self::$app_ser));

        return $params;
    }

    /**
     * 订单退单接口
     */
    public function deal($param = array())
    {
        $ret = array ();

        if (empty($param ['orderId'])) {
            $ret ['code'] = - 1002;
            $ret ['msg_cn'] = '订单号不能为空！';
            return;
        }

        $param ['opTime'] = ! empty ( $param ['opTime'] ) ? $param ['opTime'] : time ();
        $api_params = array (
            'orderNo' => $param ['orderId'],
            'opTime' => date( 'Y-m-d H:i', $param ['opTime'] ),
            'remark' => '取消订单'
        );

        $ret = $this->requestProcess('life.order.cancelOrder', $api_params );

        $model = M('order');
        $model->find($param ['orderId']);

        $send = array(
            'orderNo' => $model->id,
            'uid' => '',
            'mobile' => $mobile = M('order_partner')->where(array('order_id' => $param ['orderId']))->getField('third_party_user_no'),
            'msgway' => 0,
            'type' => 0,
            'pushway' => 3,
            'title' => '取消订单',
            'msg' => '订单取消：您好，您的订单已取消，如有问题，欢迎拨打我们客服热线：4000105678。【闪修侠-手机快修专家】',
        );

        //退单消息推送
        $this->sendMessage($send);

        return $ret;
    }

    /**
     * 订单提交接口
     */
    public function submitOrder($param = array())
    {
        $ret = array ();

        if (empty ( $param ['orderNo'] )) {
            $ret ['code'] = - 1002;
            $ret ['msg_cn'] = '订单号不能为空！';

        } else {
            $param ['applytime'] = ! empty ( $param ['applytime'] ) ? $param ['applytime'] : time ();
            $api_params = array (
                'orderNo' => $param ['orderNo'],
                'uid' => $param ['uid'],
                'mobile' => $param ['mobile'],
                'type' => 3, // 上门服务
                'address' => $param ['address'],
                'applytime' => date('Y-m-d H:i', $param ['applytime']),
                'receiver' => $param ['receiver']
            );
            $ret = $this->requestProcess ( 'life.order.submitOrder', $api_params );
        }

        return $ret;
    }


    /**
     * 除去数组中的空值和签名参数
     *
     * @param $para 签名参数组
     *        	return 去掉空值与签名参数后的新签名参数组
     */
    public function paraFilter($para)
    {
        $para_filter = array ();

        while (list($key, $val) = each ($para)) {

            if ($key == "sign" || $key == "sign_type" || $val == "")
                continue;
            else
                $para_filter [$key] = $para [$key];
        }

        return $para_filter;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     *
     * @param $para 需要拼接的数组
     *        	return 拼接完成以后的字符串
     */
    public function createLinkstring($para)
    {
        $arg = "";
        while ( list ( $key, $val ) = each ( $para ) ) {
            $arg .= $key . "=" . $val . "&";
        }
        // 去掉最后一个&字符
        $arg = substr ( $arg, 0, count ( $arg ) - 2 );

        // 如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc ()) {
            $arg = stripslashes ( $arg );
        }

        return $arg;
    }

    /**
     * 获取返回时的签名验证结果
     *
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
    public function getSignVeryfy($para_temp,$app_secret = 'XHVEVASynmHYh3n6yqCFUtLw6mZG5KPAQcsWK6rxeiIXy0Ef7Den21m3Ll3rFmHr')
    {
        // 除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);
        // 对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        // 对业务参数按字母数字排序
        if (!empty($para_sort ['params'])) {
            $temp_arr = $this->argSort ( $para_sort ['params']);
            $para_sort ['params'] = json_encode ($temp_arr, JSON_UNESCAPED_UNICODE);
        }
        // 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring ($para_sort);
        $str = $prestr . $app_secret;
        // 签名
        $sign = md5 ($str);

        // 对业务参数进行urlencode
        if (!empty($para_sort ['params'])) {
            $para_sort ['params'] = urlencode ($para_sort ['params']);
            $prestr = $this->createLinkstring ($para_sort);
        }

        $ret = $prestr . '&sign=' . $sign;

        return $ret;
    }

    public function requestProcess($method, $api_params = array())
    {
        $ret = array();

        if (empty ($method)) {
            $ret ['code'] = - 1002;
            $ret ['msg_cn'] = '方法名不能为空！';
            return;
        }

        $time = time ();
        $para_temp = array (
            'method' => $method,
            'ver' => self::$ver,
            'app_id' => self::$app_id,
            'timestamp' => $time
        );

        if (!empty($api_params)) {
            $para_temp ['params'] = $api_params;
        }

        $query = $this->getSignVeryfy($para_temp);

        $url = self::$api_url . $query;

        $json = file_get_contents($url);
        $ret = json_decode($json, true);

        error_log('【'.date('Y-m-d H:i:s').'】'.var_export($para_temp,true)."\n".$json."\n", 3, '/web/php/tmp/order/cooperation.log');
        return $ret;
    }

    /**
     * 消息提醒接口
     */
    public function sendMessage($param = array())
    {
        $ret = array ();
        if (empty($param ['orderNo'])) {
            $ret ['code'] = - 1002;
            $ret ['msg_cn'] = '订单号不能为空！';
            return $ret;
        }
        $param ['opTime'] = ! empty ( $param ['opTime'] ) ? $param ['opTime'] : time ();
        $api_params = array (
            'uid' => $param ['uid'],
            'mobile' => $param ['mobile'],
            'msgway' => $param ['msgway'], // 0 – APP通知；1 – 短信通知(需申请开通)
            'type' => $param['type'], // 消息类型 0 – 业务类通知；1 – 推荐优惠消息；2 – 其它消息
            'msg' => $param['msg'],
            'title' => $param['title'],
            'pushway' => $param['pushway'], // 推送方式 0 – 外推；1 – 内推；2 – 内外推;
        );

        $ret = $this->requestProcess('life.message.sendMessage', $api_params );

        return $ret;
    }

    /**
     * 订单进度
     */
    public function orderProgress($param = array())
    {
        $ret = array ();
        if (empty($param ['orderNo'])) {
            $ret ['code'] = - 1002;
            $ret ['msg_cn'] = '订单号不能为空！';
            return $ret;
        }
        $param ['opTime'] = ! empty ( $param ['opTime'] ) ? $param ['opTime'] : time ();
        $api_params = array (
            'orderNo' => $param['orderNo'],
            'opTime' => date('Y-m-d H:i:s', $param['orderNo']),
            'status' => 6,
        );

        $ret = $this->requestProcess('life.order.progressOrder', $api_params );

        return $ret;
    }

}