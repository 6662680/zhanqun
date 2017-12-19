<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 到位接口 Dates: 2016-12-05
// +------------------------------------------------------------------------------------------

namespace Api\Controller;

use Api\Controller;

class DaoweiController extends BaseController
{
    /**
     * 同步服务列表
     *
     * @return void
     */
    public function syncService()
    {
        #code...
    }

    /**
     * 同步服务者
     *
     * @return void
     */
    public function syncServer()
    {
        #code...
    }

    /**
     * 查询价格
     *
     * @return void
     */
    public function queryPrice()
    {
        #code...
    }

    /**
     * 订单通知
     *
     * @return void
     */
    public function informOrder()
    {
        #code...
    }

    /**
     * 消费者评价通知
     *
     * @return void
     */
    public function informComment()
    {
        #code...
    }

    /**
     * 通过结果通知
     *
     * @return void
     */
    public function informResult()
    {
        #code...
    }

    /**
     * test
     *
     * @return void
     */
    public function test()
    {
        $rst = D('daowei')->queryAddress();
        pr($rst);
        exit;
    }

}