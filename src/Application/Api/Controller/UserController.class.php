<?php
namespace Api\Controller;
use Api\Library\Think\AdminController;
use Api\Library\Think\HomeController;
use Api\Model\CheckModel;
use Api\Model\RequestModel;
use Think\Controller;
/**
 * 搜索结果控制
 * author :liyang
 * time : 2016-8-4
 */

class UserController extends BaseController {

    /**
     * @return array 历史订单
     */
    public function historyOrder(){


        $result = D('order')->historyOrder(I('post.mobile'),I('post.page')-1);
        $this->_callBack($result);
    }

    public function index(){

    }
}