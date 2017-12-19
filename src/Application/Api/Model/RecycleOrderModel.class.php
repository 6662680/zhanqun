<?php

// +------------------------------------------------------------------------------------------ 
// | Author: qikailin <qklandy@gmail.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2017/6/30
// +------------------------------------------------------------------------------------------
namespace Api\Model;
use Common\Model\BaseModel;

class RecycleOrderModel extends BaseModel {

//    protected $tableName = 'recycle_order';
//    protected $tablePrefix = '';
//    protected $trueTableName = 'recycle_order';
//    protected $dbName = 'shanxiuxia3';

    //验证规则
//    protected $_validate = [
//        array('verify','require','验证码必须！'),
//    ];

    //自动完成
//    protected $_auto = [];


    public function canFinish($orderNum){

        $status = $this->getOrderStatus($orderNum);

        if(!$status && intval($status) !== 0) return false;

        if(!C('THIRD_RECYCLE_MODE')) {
            return true;
        }

        if( intval($status) <= 6) {
            return false;
        }

        if(intval($status) == 7) {
            return false;
        }

        return true;

    }

    public function canCancel($orderNum){

        $status = $this->getOrderStatus($orderNum);

        if(!$status && intval($status) !== 0) return false;

        if(!C('THIRD_RECYCLE_MODE')) {
            return true;
        }

        if( intval($status) >= 5) {
            return false;
        }

        if(intval($status) === 0) {
            return false;
        }

        return true;

    }

    public function canStorage($orderNum){

        $status = $this->getOrderStatus($orderNum);

        if(!$status && intval($status) !== 0) return false;

        if(!C('THIRD_RECYCLE_MODE')) {
            return true;
        }

        if( intval($status) >= 6) {
            return false;
        }

        return true;

    }

    private function getOrderStatus($orderNum){
        return $status = $this->where(['order_number'=>$orderNum])->getField('status');
    }

    /**
     *
     * 更改订单状态（包含自己回收表recycle_order和第三方的回收表状态）
     * @param $orderNumber          自己回收表订单号
     * @param $status               需要更改的状态
     * @param string $statusInfo    状态 0取消订单 1 下单 2 派单 3 接单 4 处理中 5 结单 6 入库
     * @return bool
     */
    public function modOrderStatus($orderNumber, $status, $statusInfo = ''){

        $recycleInfo = $this->field('id, third_recycle_id, status')->where(['order_number'=>$orderNumber])->find();

        if(!$recycleInfo) return false; //无订单信息

        if(empty($recycleInfo['third_recycle_id'])) return false; //订单信息无关联第三方信息

        $thirdRecycleModel = D('ThirdRecycle');
        $thirdRecycleInfo  = $thirdRecycleModel->find($recycleInfo['third_recycle_id']);

        if(!$thirdRecycleInfo) return false; //无第三方订单信息

        $this->status      =  $thirdRecycleModel->status = $status;
        if(!$statusInfo) {
            $thirdRecycleModel->status_info = $statusInfo;
        }

        $this->startTrans();

        $recycleResult      = $this->save();
        $thirdRecycleResult = $thirdRecycleModel->save();

        if($recycleResult && $thirdRecycleResult) {
            $this->commit();
            return true;
        } else {
            $this->rollback();
            return false;
        }
    }

    public function getRecycle($id){
        return $this->find($id);
    }


    public function getRecycleLists($map, $limitStr){

        $join = "r LEFT JOIN " . $this->tablePrefix . "third_recycle tr ON tr.id = r.third_recycle_id"
               ."  LEFT JOIN " . $this->tablePrefix . "engineer eg ON eg.id = r.engineer_id"
               ."  LEFT JOIN " . $this->tablePrefix . "third_user tu ON tu.third_id = tr.third_id";

        $fileds = 'r.id, r.order_num, r.brand_cn, r.brand_model_cn,r.color_cn, r.payment_way, tr.third_user_name,'
                 .'tr.third_user_name, tr.third_user_phone, r.reference_price,r.actual_price,'
                 .'eg.id as engineer_id, IFNULL(eg.name, \'未指派\') as engineer_name,r.status, r.remark';

        $lists = $this->field($fileds)
                      ->join($join)
                      ->where($map)
                      ->order('r.create_time desc')
                      ->limit($limitStr)
                      ->select();

//        echo $this->_sql();exit;

        $rst['total'] = $this->table(['recycle_order'=>'r'])->where($map)->count();
        $rst['rows'] = $lists;

        return $rst;
    }


    public function getRecycleCancelReason($limitStr){

        $rst = [];
        $rst['total'] = M('RecycleCancelReason')->count();
        if(!empty($rst)) {
            $rst['rows'] = M('RecycleCancelReason')->limit($limitStr)->select();
        }

        return $rst;
    }

    /**
     *
     * 单纯保存recyle表，字段我关联更新的基本保存
     * engineer_id, brand,brand_model,color, reference_price
     * actual_price payment_way, payment_num, status
     * @return void
     */
    public function saveBase(){

        //验证规则并获取请求
        if(!$this->create()) {
            return error($this->getError(), 10002);
        }

        if(!$this->id) return error('缺少必要参数', 10001);

        if(!empty($tmpCanceliD = $this->cancel_id)) {

            if(!M('RecycleCancelReason')->find($this->cancel_id)) {
                return error('你选择了不存在的取消理由', 10004);
            }

            if($this->field('id, status,cancel_id')->find($this->id)) {

                if($this->cancel_id > 0) {
                    return error('已取消的订单，无法重复取消', 10003);
                }
                if($this->status >= 6) {
                    return error('已结单的订单，无法取消', 10004);
                }
            }

            //删除必须要的status
            unset($this->data['status']);

            $this->cancel_id   = $tmpCanceliD;
            $this->cancel_time = NOW_TIME;
        }

        return $this->save();
    }

}