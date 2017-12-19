<?php
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 到位通知接口 Dates:  2016-12-06
// +------------------------------------------------------------------------------------------ 

namespace Api\Model;

class DaoweiModel
{
    /** aopClient */
    public $client = null;

    /**
     * 构造函数
     *
     * @return void
     */
    public function __construct()
    {
        Vendor('daowei.AopSdk');
        $this->client = new \AopClient;
        $this->client->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $this->client->appId = "app_id";
        $this->client->rsaPrivateKey = '请填写开发者私钥去头去尾去回车，一行字符串';
        $this->client->alipayrsaPublicKey = '请填写支付宝公钥，一行字符串';
    }

    /**
     * 城市列表查询
     *
     * @return void
     */
    public function queryAddress()
    {
        $map = array();
        $map['current_page'] = 1;
        $map['page_size'] = 50;

        /** alipay.daowei.city.query */
        /** $request = new \AlipayDaoweiCityQuery();
        $request->bizContent = json_encode($map);
        $response = $this->client->execute($request); */

        $response = '{
    "alipay_daowei_city_query_response": {
        "code": "10000",
        "msg": "Success",
        "city_list": [
            {
                "city_code": "300100",
                "city_name": "杭州",
                "district_list": [
                    {
                        "district_code": "300101",
                        "district_name": "西湖区"
                    }
                ]
            }
        ]
    }
}';

pr(json_decode($response, 1));exit;


        pr($response);exit;
    }

    /**
     * 类目列表查询
     *
     * @return void
     */
    public function queryCategory()
    {
        /** alipay.daowei.category.query */
        $request = new AlipayDaoweiCategoryQuery();
    }

    /**
     * 第三方注册登记
     *
     * @return void
     */
    public function register()
    {
        /** alipay.daowei.tenant.create */
        $request = new AlipayDaoweiTenantCreate();
    }

    /**
     * 订单查询
     *
     * @return void
     */
    public function queryOrder()
    {
        /** alipay.daowei.order.query */
        $request = new AlipayDaoweiOrderCreate();
    }

    /**
     * 确认接单
     *
     * @return void
     */
    public function confirmOrder()
    {
        /** alipay.daowei.order.confirm */
        $request = new AlipayDaoweiOrderConfirm();
    }

    /**
     * 拒绝接单
     *
     * @return void
     */
    public function refuseOrder()
    {
        /** alipay.daowei.order.refuse */
        $request = new AlipayDaoweiOrderRefuse();
    }

    /**
     * 订单服务者变更
     *
     * @return void
     */
    public function changeServer()
    {
        /** alipay.daowei.order.sp.modify */
        $request = new AlipayDaoweiOrderSpModify();
    }

    /**
     * 订单修改
     *
     * @return void
     */
    public function updateOrder()
    {
        /** alipay.daowei.order.modify */
        $request = new AlipayDaoweiOrderModify();
    }

    /**
     * 订单状态推进
     *
     * @return void
     */
    public function updateOrderStatus()
    {
        /** alipay.daowei.order.transfer */
        $request = new AlipayDaoweiOrderTransfer();

        /** 1. 开始上门 alipay.daowei.order.transfer*/
        /** 2. 结束维修 alipay.daowei.order.transfer */
    }

    /**
     * 订单退款
     *
     * @return void
     */
    public function refundOrder()
    {
        /** alipay.daowei.order.refund */
        $request = new AlipayDaoweiOrderRefund();
    }

    /**
     * 订单评价查询
     *
     * @return void
     */
    public function queryOrderComment()
    {
        /** alipay.daowei.comment.query */
        $request = new AlipayDaoweiCommentQuery();
    }

    /**
     * 新增服务者信息
     *
     * @return void
     */
    public function addServer()
    {
        /** alipay.daowei.sp.create */
        $request = new AlipayDaoweiSpCreate();
    }

    /**
     * 更新服务者信息
     *
     * @return void
     */
    public function updateServer()
    {
        /** alipay.daowei.sp.modify */
        $request = new AlipayDaoweiSpModify();
    }

}