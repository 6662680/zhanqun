<?php

// +------------------------------------------------------------------------------------------ 
// | Author: qikailin <qklandy@gmail.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2017/7/3
// | 第三方合作回收管理
// | 功能包含：工程师结单管理，订单状态管理
// +------------------------------------------------------------------------------------------
namespace Admin\Controller;

use Admin\Controller;
use Think\Hook;

class ThirdrecycleController extends BaseController
{

    //tp 对象实例化自动调用方法
    public function _initialize()
    {
        Hook::listen('auto_self_tags');
    }


    /**
     *
     * 获取recycle_order表的回收数据 可搜索条件
     * @return void
     */
    public function getRecycleLists()
    {
        $map = array();
        if (I('post.order_num')) {
            $map['r.order_num'] = I('post.order_num');
        }
        if (I('post.engineer_id')) {
            $map['r.engineer_id'] = I('post.engineer_id');
        }

        $recycleModel = D('Api/RecycleOrder');
        $rst = $recycleModel->getRecycleLists($map, $this->page());
        $this->ajaxReturn($rst);
    }
    public function getRecycleCancelReason()
    {
        $recycleModel = D('Api/RecycleOrder');
        $rst = $recycleModel->getRecycleCancelReason($this->page());
        $this->ajaxReturn($rst);
    }
    public function getRecycle()
    {
        $id = I('get.id', 0);
        $recycleModel = D('Api/RecycleOrder');
        $rst = $recycleModel->getRecycle($id);
        $this->ajaxReturn($rst);
    }
    public function saveBase(){

        $recycleModel = D('Api/RecycleOrder');
        $ret = $recycleModel->saveBase();
        if(is_error($ret)) {
            $this->ajaxReturn($ret ? : ['ret'=>90000, 'errorMsg'=>'数据无修改']);
        }

        //判断一定行为后去通知第三方
//        Hook::listen('third_test');

        $this->ajaxReturn(['ret'=>0]);
    }
    public function recyclelists()
    {
        $this->display();
    }








    /**
     *
     * 获取库存表的数据 可搜索条件
     * @return void
     */
    public function getStockLists()
    {
        $recycleStockModel = D('Api/RecycleStock');
        $rst = $recycleStockModel->getStockLists($this->page());
        $this->ajaxReturn($rst);
    }
    public function stock()
    {
        $this->display();
    }



    /**
     *
     * 获取工程师回收分成表的数据 可搜索条件
     * @return void
     */
    public function getProfitLists()
    {
        $recycleProfitModel = D('Api/RecycleProfit');
        $rst = $recycleProfitModel->getStockLists($this->page());
        $this->ajaxReturn($rst);
    }
    public function profit()
    {
        $this->display();
    }


    /**
     *
     * 获取所有工程师
     * @return void
     */
    public function getEngineers()
    {
        $engineerLists = D('Engineer')->getEngineers();
        if($engineerLists) {
            array_unshift($engineerLists, ['id'=>0, 'name'=>'全部']);
        }

        $this->ajaxReturn($engineerLists ? : []);

    }



}