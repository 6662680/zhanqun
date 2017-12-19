<?php
namespace V2\Controller;
use V2\Model\OrderModel;
use Think\Controller;
use V2\Transformer\OrderTransformer;
use V2\Validate\OrderValidate;

/**
 * 订单控制器
 * author :liyang
 * time : 2016-8-4
 */

class OrderController extends ApiController
{

    protected $model=null;


    public function _initialize()
    {
        $this->model=D('Order');
    }


    /**
     * 用户下单操作
     */
    public function postCreate()
    {
        $validate=new OrderValidate();
        $data=I('post.');
        //验证
        if(!$validate->verification($data)){
            $this->setStatusCode($validate->errorInfo['code'])
                ->responseError($validate->errorInfo['msg']);
    }
        if(($orderInfo=$this->model->addOrder($data))!==false){
            $this->responseSuccess($orderInfo,new OrderTransformer);
        }else{
            $this->setStatusCode(1020)->responseError($this->model->getError());
        }
    }

    /**
     * 对比验证码
     * @author liyang
     * @return void
     */
    public function getVcode()
    {
        if(S('code'.I('post.mobile')) == I('post.code')){
            $this->responseSuccess();
        } else {
            $this->setStatusCode(1006)->responseError('验证码错误');
        }
    }

    /**
     * 统计单量
     */
    public function getCount()
    {
        $num=$this->model->count()*2;
        $this->responseSuccess(compact('num'));
    }

    /**
     * 客户查询订单接口
     * @param  string cellphone
     */
    public function postOrders()
    {
        $cellphone = trim(I('post.cellphone'));
        $rst = array();
        if (!$cellphone) {
            $this->responseError('请传递手机号',1004);
        }
        $data = array();
        //响应构建器
        $OrderTransformer=new OrderTransformer();
        //保险订单
        $list = D('PhomalInsuranceOrder')->getInsuranceOrder(['pio.cellphone' => $cellphone]);
        $insurance =$OrderTransformer->makeInsurance($list);
        //普通订单
        $list =D('Order')->getCustomerOrders(['o.cellphone' => $cellphone]);

        //合并保险订单和普通订单
        $data=$OrderTransformer->makeOrder($list,$insurance);
        rsort($data);
        $this->responseSuccess($data);
    }
}