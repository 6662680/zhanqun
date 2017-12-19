<?php

// +------------------------------------------------------------------------------------------ 
// | Author: qikailin <qklandy@gmail.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2017/6/30
// +------------------------------------------------------------------------------------------
namespace Vendors\Recycle\Company;

use Vendors\Http\Curl;

class Huishoubao extends Base{

    const API_GET_PRODUCT = 'http://openapi.huishoubao.com/get_products';
    const API_GET_PRODUCT_PARAM = '';

    private function __clone() {}

    public function __construct() {}

    private function buildSign($str, $type)
    {
        parse_str($str, $params);

        switch($type) {
            //pid=10001&type=2&time=1423623250&expire=600&key=xxx
            case 'getProducts' : {
                unset($params['mid']);
            } break;
        }

        return $this->redureMd5($params, $this->getMd5Times($params));

    }

    private function getMd5Times($params)
    {
        $waitSign = http_build_query($params);
        $size = strlen($waitSign);
        $sum = 0;
        for($i = 0; $i < $size; $i++ ) {
            $tmp = substr($waitSign,$i, 1);
            if(is_numeric($tmp)) {
                $sum += $tmp;
                continue;
            }
            $sum += ord($tmp);
        }

        return ($sum % 3 ) + 3;
    }

    private function redureMd5($params, $times)
    {
        $str = http_build_query($params);
        while($times >= 1) {
            $str = strtoupper( md5($str) );
            --$times;
        }
        return $str;
    }

    public function getProducts($params)
    {

        $postParams = [
            'pid' => $this->appid,
            'type' => 2,
            'itemid' => $params['itemid'],
            'time' => NOW_TIME,
        ];
        $result = Curl::ihttp_post(self::API_GET_PRODUCT, $params);

        return $result;

    }


    /**
     *
     * 向回收宝发起同步订单请求
     * @return void
     */
    public function syncOrder()
    {


    }



    /**
     *
     * @return void
     */
    public function getProductParam()
    {

    }




    /**
     *
     * @return void
     */
    public function detectionEvaluate()
    {

    }



    /**
     *
     * @return void
     */
    public function paymentRequest()
    {

    }


    /**
     *
     * @return void
     */
    public function syncCheckInfo()
    {

    }





































}