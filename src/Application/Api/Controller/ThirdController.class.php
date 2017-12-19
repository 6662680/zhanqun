<?php
namespace Api\Controller;

class ThirdController extends BaseController {

    const ERROR_NO_SIGN = 10000;
    const ERROR_SIGN = 10001;
    const ERROR_NO_DATA = 10002;
    const ERROR_IS_EXIST = 10003;
    const ERROR_CANNT_CANCEL = 10004;
    const ERROR_NO_THIRDUSER = 10005;
    const ERROR_NOT_EXIST_THIRDUSER = 10006;
    const ERROR_NOT_EXIST_RECYCLE_ORDER = 10007;
    const ERROR_NOT_RECYCLE_RECORD = 10008;

    const ERROR_MOD_RECYCLE_NOPARAM = 20000;
    const ERROR_MOD_RECYCLE_STATUS = 20001;
    const ERROR_MOD_RECYCLE_STATUS_IS_FINISH = 20002;
    const ERROR_MOD_RECYCLE_STATUS_CANNT_FINISH = 20003;
    const ERROR_MOD_RECYCLE_STATUS_IS_CANCEL = 20004;
    const ERROR_MOD_RECYCLE_STATUS_CANNT_CANCEL = 20005;


    protected $thirdUser = [];

    //测试用 模拟POST请求里的部分数据，可无视本方法，
    protected function virtualData(){

        if(!C('THIRD_RECYCLE_MODE')) return;

        $_POST['sign'] = 111;

        ##订单入库模拟
        $_POST['third_id'] = 10001;
        $_POST['third_order_time'] = 111;
        $_POST['third_order_num'] = 111;
        $_POST['third_engineer_id'] = 123;
        $_POST['third_payway'] = 123;
        $_POST['third_quatation'] = 10000.22;
        $_POST['third_status'] = 1;
        $_POST['third_items'] = "123#435#345";
        $_POST['third_items_desc'] = "描述可空";
        $_POST['third_uid'] = 22222;
        $_POST['third_name'] = '回收宝用户123';
        $_POST['third_remark'] = "更多描述";


        ##订单状态更改模拟
        $_POST['order_num'] = '2017063057525449';

    }


    /**
     *
     * 检查必要的请求参数，不通过直接返回json错误数据
     * @param $params 请求的参数
     * @return void null
     */
    protected function detectRequestParams($params){

        if (!$params['sign']) {
            $this->ajax(['msg' => '参数里不包含sign签名!'], self::ERROR_NO_SIGN);
        }

        if(!isset($params['third_id'])) {
            $this->ajax(['msg' => '参数里不包含第三方合作id!'], self::ERROR_NO_THIRDUSER);
        }

        if (!$this->thirdUser = D('ThirdUser')->field('third_id,third_name')->find($params['third_id'])) {
            $this->ajax(['msg' => '找不到对应的第三方合作id!'], self::ERROR_NOT_EXIST_THIRDUSER);
        }

        //缓存当前的请求合作商资料
        if(!S('third_user_'.$this->thirdUser['third_id'])) {
            S('third_user_'.$this->thirdUser['third_id'], $this->thirdUser);
        }

    }


    /**
     *
     * 签名方式
     * @param $params 请求的参数
     * @param $key    第三方的key
     * @return string 返回签名字符串
     */
    protected function sign($params, $key) {

        return strtoupper( md5( $this->buildQuery($params)."&key={$key}" ) );
    }

    /**
     *
     * 拼接请求参数
     * @param $params 请求的参数
     * @return string 返回拼接好的字符串
     */
    protected function buildQuery($params) {

        if($params['sign']) {
            unset($params['sign']);
        }

        array_filter($params); //去重

        ksort($params); //按key键值排序数组

        return http_build_query($params);
    }

    /**
     *
     * 本次请求检查签名函数
     * @param $requestSign 请求的签名
     * @param $rightSign   正确的签名
     * @return bool
     */
    protected function chechSign($requestSign, $rightSign) {

        return C('THIRD_RECYCLE_MODE') ? : $requestSign == $rightSign;

    }

    protected function ajax($data, $error = 0){

        if($error) {
            $data = array_merge($data, array('error'=>$error));
        }

        $this->ajaxReturn($data);
    }



}
