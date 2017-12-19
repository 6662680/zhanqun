<?php
namespace Api\Controller;

use Think\Page;

class ThirdRecycleController extends ThirdController {

    private $requestParams = [];

    /**
     *
     * 每次请求前都判断请求是否合法或有错误
     * 如果有错误，直接拦截返回错误，不到具体业务里去
     *
     * @return void
     */
    public function _initialize()
    {

        if( $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ) {
            C('THIRD_RECYCLE_MODE', 1);
        }

        $this->virtualData(); //模拟数据，上线去掉

        $this->requestParams = I('post.');

        $this->detectRequestParams($this->requestParams); //拦截必要参数错误 直接退出

        if (!$this->chechSign($this->sign($this->requestParams, $this->thirdUser['third_key']))) {
            $this->ajax(['msg' => '签名错误!'], self::ERROR_SIGN);
        }

        //订单状态请求前做一次判断
        if(substr(ACTION_NAME,0,5) == 'order') {
            $this->checkOrderParams();
        }
    }

    /**
     *
     * 第三方请求过来的订单接口
     * 我方需要做自己的入库回收处理
     *
     * @return void
     */
    public function newRecycleOrder(){

        $thirdRecycleModel = D('ThirdRecycle');

        //启动事务处理
        $recycleModel = D('RecycleOrder');

        if(!$thirdRecycleModel->create()) { //数据无法验证
            $ajaxData = [];
        }

        if(!$recycleModel->create()) { //数据无法验证
            $ajaxData = [];
        }

        $recycleModel->order_num = $orderNumber = buildOrderNum();
        $recycleModel->order_time = NOW_TIME;


        $recycleModel->create_time = NOW_TIME;
        $recycleModel->status = 1;
        $recycleModel->reference_price = $recycleModel->actual_price = $thirdRecycleModel->third_quatation;
        $recycleModel->payment_way = $thirdRecycleModel->third_payway;

        $thirdRecycleModel->create_time = NOW_TIME;

        //开始事务
        $recycleModel->startTrans();

        //第三方回收表记录
        $thirdRecycleResult = $thirdRecycleModel->add(); // 写入数据第三方回收

        $recycleModel->third_recycle_id = $thirdRecycleResult;

        //回收表
        $recycleResult = $recycleModel->add(); // 写入数据到自己回收表

        //第三方检测细节记录表
        $recycleDetectorDetailResult = $thirdRecycleModel->saveEveryDetectResult($thirdRecycleResult);

        if($recycleResult && $thirdRecycleResult && $recycleDetectorDetailResult){
            $recycleModel->commit();
        } else {
            $ajaxData = ['msg'=>'请求成功，操作失败，请重试'];
            $recycleModel->rollback();  //出错回滚
        }

        if(isset($ajaxData['msg'])) {
            $this->ajax($ajaxData);
        }

        //正常请求返回特定的结构体
        $thirdUserInfo = S('third_user_'.$this->thirdUser['third_id']);
        $data = [
            'third_id' => $thirdUserInfo['third_id'],
            'third_name' =>$thirdUserInfo['third_name'],
            'order_number' => $orderNumber,
            'order_time' => NOW_TIME,
        ];

        $this->ajax($data);

    }


    /**
     *
     * 第三方用户做了取消订单处理后我方做的处理
     *
     * 未付款的订单都可以做取消处理
     *
     * @return void
     */
    public function orderCancel() {

        $data = $this->checkOrderParams();

        if(!D('RecycleOrder')->canCancel($data['order_num'])){
            $ajaxData = ['msg'=>'当前该订单无法取消 或 已取消', 'error'=>self::ERROR_MOD_RECYCLE_NOPARAM];
            $this->ajax($ajaxData);
        }

        if(!D('RecycleOrder')->modOrderStatus($data['order_num'], 0, $data['status_info'])) {
            $ajaxData = ['msg'=>'请求取消订单失败，请重试', 'error'=>self::ERROR_MOD_RECYCLE_STATUS];
            $this->ajax($ajaxData);
        }

        $this->ajax([
            'order_num'=>$data['order_num'],
            'success'=> 1
        ]);

    }


    /**
     *
     * 第三方用户做了结单订单处理接口
     *
     * @return void
     */
    public function orderFinish() {

        $data = $this->checkOrderParams();

        if(!D('RecycleOrder')->canFinish($data['order_num'])){
            $ajaxData = ['msg'=>'当前该订单无法结单 或 已结单', 'error'=>self::ERROR_MOD_RECYCLE_STATUS_CANNT_FINISH];
            $this->ajax($ajaxData);
        }

        if(!D('RecycleOrder')->modOrderStatus($data['order_num'], 5, $data['status_info'])) {
            $ajaxData = ['msg'=>'请求结单订单失败，请重试', 'error'=>self::ERROR_MOD_RECYCLE_STATUS];
            $this->ajax($ajaxData);
        }

        $this->ajax([
            'order_num'=>$data['order_num'],
            'success'=> 1
        ]);

    }


    /**
     *
     * 第三方用户做了结单订单处理接口
     *
     *
     *
     * @return void
     */
    public function orderStorage() {

        $data = $this->checkOrderParams();

        if(!D('RecycleOrder')->canStorage($data['order_num'])){
            $ajaxData = ['msg'=>'当前该订单无法入库 或 已入库', 'error'=>self::ERROR_MOD_RECYCLE_STATUS_CANNT_FINISH];
            $this->ajax($ajaxData);
        }

        if(!D('RecycleOrder')->modOrderStatus($data['order_num'], 6, $data['status_info'])) {
            $ajaxData = ['msg'=>'请求入库订单失败，请重试', 'error'=>self::ERROR_MOD_RECYCLE_STATUS];
            $this->ajax($ajaxData);
        }

        $this->ajax([
            'order_num'=>$data['order_num'],
            'success'=> 1
        ]);

    }

    /**
     *
     * 检查
     * @return array
     */
    private function checkOrderParams(){

        $orderNum  = I('post.order_num');
        $statusInfo = I('post.status_info', '');

        if(!$orderNum) $this->ajax(['msg'=>'请求回收订单的参数缺失，order_num', 'error'=>self::ERROR_MOD_RECYCLE_NOPARAM]);

        return [
            'order_num' =>$orderNum,
            'status_info' =>$statusInfo
        ];

    }


    /**
     *
     * 第三方获取我方的订单数据列表
     *
     * @return void
     */
    public function getOrderLists() {

        $recycleModel = D('RecycleOrder');

        $where = [];

        $pagesize = I('post.pagesize', 20);

        $count      = $recycleModel->where($where)->count();
        $page       = $_GET['p'] = I('post.page', 1);
        $Page       = new Page($count,$pagesize);
        $lists      = $recycleModel->where($where)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();


        if(!$lists) {
            $ajaxData = ['msg'=>'找不到更多的回收订单的数据', 'error'=>self::ERROR_NOT_RECYCLE_RECORD];
            $this->ajax($ajaxData);
        }

        $totalPage  = ceil($count / $pagesize); //总页数;

        $this->ajax([
            'lists' =>$lists,
            'count' => count($lists),
            'page'  => $page,
            'total' => $totalPage
        ]);
    }


    /**
     *
     * 临时测试 无视
     * @return void
     */
    public function test(){

        $hsbRecycle = \Vendors\Recycle\Recycle::getCompanyObject('huishoubao');

        $hsbRecycle->setBaseConfig( C('THIRD_RECYCLE_BUSINESS.HUISHOUBAO') );

        var_dump($hsbRecycle->getProducts(['itemid'=>2]));
    }


}