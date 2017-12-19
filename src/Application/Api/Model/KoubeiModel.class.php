<?php

// +------------------------------------------------------------------------------------------ 
// | Author: qishanshan <qishanshan@weadoc.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  将口碑到家封装为一个类 Dates: 2016/3/1
// +------------------------------------------------------------------------------------------

namespace Api\Model;

class KoubeiModel
{
    /**
     * 订单推进接口
     *
     * @param $orderId
     * @param $orderStatus
     * @return void
     */
    public function notifyOrderStatus($param)
    {
        $orderId = (int)$param['orderId'];
        $orderStatus = $param['orderStatus'];
        
        if ($orderId <= 0 || !is_numeric($orderStatus)) {
            $result['status'] = 0;
            $result['info'] = '通知口碑到家订单状态变更接口参数传入错误!';
            return $result;
        }
        
        $bizContent = array (
            "order_no" => $orderId,
            "order_action" => $orderStatus
        );

        Vendor('alipay-sdk.aop.AopClient');
        Vendor('alipay-sdk.aop.request.AlipayOfflineMarketOrderForwardRequest');
        Vendor('alipay-sdk.aop.SignData');

        $aopClient = new \AopClient ();
        $aopClient->gatewayUrl = "http://openapi-stable.dl.alipaydev.com/gateway.do";
        $aopClient->appId = "2016010500890646";
        $aopClient->method = "alipay.offline.market.order.forward";
        $aopClient->rsaPrivateKeyFilePath = VENDOR_PATH.'/alipay-sdk/private_key.pem';
        $aopClient->postCharset = "UTF-8";
        $alipayOfflineMarketOrderForwardRequest = new \AlipayOfflineMarketOrderForwardRequest ();

        $alipayOfflineMarketOrderForwardRequest->setBizContent ( $bizContent );

        $response = $aopClient->execute ( $alipayOfflineMarketOrderForwardRequest, null, null );

        $output = json_encode($response, JSON_UNESCAPED_UNICODE );
        
        if ($output['alipay_offline_market_order_forward_response']['code'] != 10000) {
            $result['status'] = 0;
            $result['info'] = '通知口碑到家状态失败';
            return $result;
        } else {
            $result['status'] = 1;
            return $result;
        }
    }

    /**
     * 取消订单
     *
     * @param $orderId
     * @param $closeReason
     * @return void
     */
    public function notifyCloseOrder($param)
    {
        $orderId = (int)$param['orderId'];
        $closeReason = $param['closeReason'];
    
        if ($orderId <= 0 || $closeReason == '') {
            $result['status'] = 0;
            $result['info'] = '通知口碑到家取消订单接口参数传入错误!';
            return $result;
        }
        
        $bizContent = array (
            "order_no" => $orderId,
            "memo" => $closeReason
        );

        Vendor('alipay-sdk.aop.AopClient');
        Vendor('alipay-sdk.aop.request.AlipayOfflineMarketOrderCloseRequest');
        Vendor('alipay-sdk.aop.SignData');

        $aopClient = new \AopClient ();
        $aopClient->gatewayUrl = "http://openapi-stable.dl.alipaydev.com/gateway.do";
        $aopClient->appId = "2016010500890646";
        $aopClient->method = "alipay.offline.market.order.close";
        $aopClient->rsaPrivateKeyFilePath = VENDOR_PATH.'/alipay-sdk/private_key.pem';
        $aopClient->postCharset = "UTF-8";
        $alipayOfflineMarketOrderCloseRequest = new \AlipayOfflineMarketOrderCloseRequest ();


        $alipayOfflineMarketOrderCloseRequest->setBizContent ( $bizContent );

        $response = $aopClient->execute ( $alipayOfflineMarketOrderCloseRequest, null, null );

        return json_encode ( $response, JSON_UNESCAPED_UNICODE );
    }

    /**
     * 通知价格变更
     *
     * @param $orderId
     * @param $newPrice
     * @return void
     */
    public function notifyOrderPrice($param)
    {
        $orderId = (int)$param['orderId'];
        $newPrice = $param['newPrice'];
        
        if ($orderId <= 0 || $newPrice === '') {
            $result['status'] = 0;
            $result['info'] = '通知口碑到家取消订单接口参数传入错误!';
            return $result;
        }
        
        $bizContent = array (
            "order_no" => $orderId,
            "real_amount" => $newPrice
        );

        Vendor('alipay-sdk.aop.AopClient');
        Vendor('alipay-sdk.aop.request.AlipayOfflineMarketOrderAmountUpdateRequest');
        Vendor('alipay-sdk.aop.SignData');

        $aopClient = new \AopClient ();
        $aopClient->gatewayUrl = "http://openapi-stable.dl.alipaydev.com/gateway.do";
        $aopClient->appId = "2016010500890646";
        $aopClient->method = "alipay.offline.market.order.amount.update";
        $aopClient->rsaPrivateKeyFilePath = VENDOR_PATH.'/alipay-sdk/private_key.pem';
        $aopClient->postCharset = "UTF-8";
        $alipayOfflineMarketOrderAmountUpdateRequest = new \AlipayOfflineMarketOrderAmountUpdateRequest ();

        $alipayOfflineMarketOrderAmountUpdateRequest->setBizContent ( $bizContent );

        $response = $aopClient->execute ( $alipayOfflineMarketOrderAmountUpdateRequest, null, null );

        return json_encode ( $response, JSON_UNESCAPED_UNICODE );
    }

    /**
     * 通知手艺人变更
     *
     * @param $orderId
     * @param $engineer
     * @param $alipay
     * @param $idcard
     * @param $certType
     * @param $cityCode
     * @return void
     */
    public function notifyOrderEngineer($param)
    {
        $orderId = 0;
        $engineer = 0;
        $alipay = '';
        $idcard = '';
        $certType = '';
        $cityCode = '';
        
        foreach ($param as $k => $v) {
            $$k = $v;
        }
        
        $bizContent = array (
            "order_no" => $orderId,
            "external_sp_id" => $engineer,
            "alipay_logon_id" => $alipay,
            "cert_no" => $idcard,
            "cert_type" => $certType,
            "city_code" => $cityCode,
        );

        Vendor('alipay-sdk.aop.AopClient');
        Vendor('alipay-sdk.aop.request.AlipayOfflineKhomeOrderSpUpdateRequest');
        Vendor('alipay-sdk.aop.SignData');

        $aopClient = new \AopClient ();
        $aopClient->gatewayUrl = "http://openapi-stable.dl.alipaydev.com/gateway.do";
        $aopClient->appId = "2016010500890646";
        $aopClient->method = "alipay.offline.khome.order.sp.update";
        $aopClient->rsaPrivateKeyFilePath = VENDOR_PATH.'/alipay-sdk/private_key.pem';
        $aopClient->postCharset = "UTF-8";
        $AlipayOfflineKhomeOrderSpUpdateRequest = new \AlipayOfflineKhomeOrderSpUpdateRequest ();

        $AlipayOfflineKhomeOrderSpUpdateRequest->setBizContent ( $bizContent );

        $response = $aopClient->execute ( $AlipayOfflineKhomeOrderSpUpdateRequest, null, null );

        return json_encode ( $response, JSON_UNESCAPED_UNICODE );
    }

    /**
     *
     * 城市列表
     * @param $currentPage
     * @param $pageSize
     * @return void
     */
    public function getCity($param)
    {
        $currentPage = $param['currentPage'];
        $pageSize = $param['pageSize'];
        
        $bizContent = array (
            "current_page" => $currentPage,
            "page_size" => $pageSize
        );

        Vendor('alipay-sdk.aop.AopClient');
        Vendor('alipay-sdk.aop.request.AlipayOfflineKhomeCityQueryRequest');
        Vendor('alipay-sdk.aop.SignData');

        $aopClient = new \AopClient ();
        $aopClient->gatewayUrl = "http://openapi-stable.dl.alipaydev.com/gateway.do";
        $aopClient->appId = "2016010500890646";
        $aopClient->method = "alipay.offline.khome.city.query";
        $aopClient->rsaPrivateKeyFilePath = VENDOR_PATH.'/alipay-sdk/private_key.pem';
        $aopClient->postCharset = "UTF-8";
        $alipayOfflineKhomeCityQueryRequest = new \AlipayOfflineKhomeCityQueryRequest ();

        $alipayOfflineKhomeCityQueryRequest->setBizContent ( $bizContent );

        $response = $aopClient->execute ( $alipayOfflineKhomeCityQueryRequest, null, null );

        return json_encode ( $response, JSON_UNESCAPED_UNICODE );
    }

    /**
     * 类目列表接口
     *
     * @param $currentPage
     * @param $pageSize
     * @return void
     */
    public function getCategory($param)
    {
        $currentPage = $param['currentPage'];
        $pageSize = $param['pageSize'];
        
        $bizContent = array (
            "current_page" => $currentPage,
            "page_size" => $pageSize
        );

        Vendor('alipay-sdk.aop.AopClient');
        Vendor('alipay-sdk.aop.request.AlipayOfflineKhomeCategoryQueryRequest');
        Vendor('alipay-sdk.aop.SignData');

        $aopClient = new \AopClient ();
        $aopClient->gatewayUrl = "http://openapi-stable.dl.alipaydev.com/gateway.do";
        $aopClient->appId = "2016010500890646";
        $aopClient->method = "alipay.offline.khome.category.query";
        $aopClient->rsaPrivateKeyFilePath = VENDOR_PATH.'/alipay-sdk/private_key.pem';
        $aopClient->postCharset = "UTF-8";
        $alipayOfflineKhomeCategoryQueryRequest = new \AlipayOfflineKhomeCategoryQueryRequest ();


        $alipayOfflineKhomeCategoryQueryRequest->setBizContent ( $bizContent );

        $response = $aopClient->execute ( $alipayOfflineKhomeCategoryQueryRequest, null, null );

        return json_encode ( $response, JSON_UNESCAPED_UNICODE );
    }
}