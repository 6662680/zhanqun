<?php

/**
 * Created by PhpStorm
 * @Desc: 葡萄生活 中间付费模式
 * @User: tianchunguang@weadoc.com
 * @Date: 2016/10/26
 */

namespace Api\Controller;

use Think\Controller;

class PutaoController extends Controller
{
    /**
     * @Desc: 获取可预约时间列表, post
     */
    public function queryAvailableTimeslots()
    {
        if (IS_POST) {
            $param = I('param.');
            $param['sku'] = trim($_POST['sku']);
            $this->ajaxReturn(D('Putao')->getAvailableTimeslots($param));
        }
    }

    /**
     * @Desc: 创建订单, post
     */
    public function createOrder()
    {
        if (IS_POST) {
            $param = I('param.');
            $param['sku'] = trim($_POST['sku']);
            $param['customizeInfo'] = trim($_POST['customizeInfo']);
            $this->ajaxReturn(D('Putao')->createOrder($param));
        }
    }

    /**
     * @Desc: 同步订单的交易状态, post, trade_status 2:成功 3:取消
     */
    public function orderPaied()
    {
        if (IS_POST) {
            $param = I('param.');
            $param['coupon_info'] = trim($_POST['coupon_info']);
            $this->ajaxReturn(D('Putao')->updateOrderPaied($param));
        }
    }

    /**
     * @Desc: 更新订单信息, 取消订单, post
     */
    public function updateOrderInfo()
    {
        if (IS_POST) {
            $this->ajaxReturn(D('Putao')->updateOrderInfo(I('param.')));
        }
    }
}
