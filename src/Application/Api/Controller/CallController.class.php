<?php

namespace Api\Controller;

use Think\Controller;

class CallController extends Controller
{
//    const host = 'http://apitest.emic.com.cn'; // 地址
//    const softVersion = '20161021'; // 版本
//    const phoneNumber = '057128256072'; // 总机号
    const validTime = "-3 day"; // 中转号码有效时间为结束维修后3天内，（即3天后，中转号码被释放）
    const customServiceNumber = '4000105678'; // 客服电话

//    private $commonUrl;
//    private $accountID = '881a2a670fb4fc72fbff8f02435b2bca'; // 主账户ID
//    private $accountToken = '1dc6f46357d0a7d99947402c8fa7f73a'; // 主账户Token
//    private $appID = '589920c51bbcc9eacac56325c9d48edc'; // 应用ID（AppId）
//    private $subAccountID = 'ffb82b85c127f6a3bb27e1d34227827c'; // 子账户1 ID
//    private $subAccountToken = '02e18490d5d8f0b9cfc8acbe15972b68'; // 子账户1Token

//    public function __construct()
//    {
//        parent::__construct();
//        $this->commonUrl = self::host . '/' . self::softVersion;
//    }

    public function call()
    {
        if ($fileContent = file_get_contents("php://input")) {

            $xml = simplexml_load_string($fileContent);
            $caller = $xml->caller;
            $transed = $xml->useNumber;
            $called = $this->getCalledPhoneBy($caller, $transed);
            $called = $called ? $called : self::customServiceNumber;

            $data = array(
                'retcode' => 0,
                'called' => $called
            );

            echo xml_encode($data, 'response');
        }
    }

    public function callTest()
    {
        $caller = trim(I('request.caller'));
        $transed = trim(I('request.useNumber'));
        $called = $this->getCalledPhoneBy($caller, $transed);

        $called = $called ? $called : self::customServiceNumber;

        $data = array(
            'retcode' => 0,
            'called' => $called
        );

        $this->ajaxReturn($data);
    }

    public function appCall()
    {
        $id = trim(I('request.order_id'));
        $rst = $this->getTransPhoneInfoByOrderId($id);
        $this->ajaxReturn($rst);
    }

    /**
     * 根据主叫号（工程师或客户的号码）与中转号码得到被叫号码
     *
     * @param $caller 主叫号
     * @param $transed 中转号码
     * @return string 被叫号码
     */
    private function getCalledPhoneBy($caller, $transed)
    {
        $called = '';

        if ($caller && $transed) {
            $map = array(
                '`o`.cellphone' => $caller,
                '`e`.`cellphone`' => $caller,
                '_logic' => 'OR'
            );

            $where['`o`.`maintain_end_time`'] = array(0, array('EGT', strtotime(self::validTime)), 'OR');
            $where['`tp`.`number`'] = $transed;
            $where['_complex'] = $map;

            $orderModel = M('Order');
            $orderInfo = $orderModel
                ->join('AS `o` LEFT JOIN `engineer` AS `e` ON `e`.`id` = `o`.`engineer_id`')
                ->join('LEFT JOIN `trans_phone` AS `tp` ON `o`.`trans_phone_id` = `tp`.`id`')
                ->field('`o`.cellphone AS `customer_number`, `tp`.`number`, `e`.`cellphone` AS `engineer_number`')
                ->where($where)
                ->find();

            $called = $caller == $orderInfo['customer_number'] ? $orderInfo['engineer_number'] : $orderInfo['customer_number'];
        }

        return $called;
    }

    /**
     * 根据订单id得到中转电话内容
     *
     * @param $order_id 订单ID
     * @return array
     */
    private function getTransPhoneInfoByOrderId($order_id)
    {
        $res = array(
            'code' => -99, // 代码编号,0 正常，其它为异常
            'msg' => '未知错误', // 代码涵义
            'tellphone' => '' // 中转电话号码，出错时为空
        );

        $order_id = intval($order_id);

        if ($order_id < 1) { // 订单ID出错
            $res['code'] = -1;
            $res['msg'] = '订单ID出错';
            return $res;
        }

        $orderModel = M('Order');
        $map['`o`.`id`'] = $order_id;
        $orderInfo = $orderModel
            ->join('AS `o` LEFT JOIN `trans_phone` AS `tp` ON `o`.`trans_phone_id` = `tp`.`id`')
            ->field('`tp`.`number`, `o`.`id`, `o`.`engineer_id`, `o`.`maintain_end_time`, `o`.`trans_phone_id`')
            ->where($map)
            ->find();
        unset($map);

        if (!$orderInfo) { // 订单信息出错
            $res['code'] = -2;
            $res['msg'] = '订单信息不存在';

            return $res;
        }

        $engineer_id = intval($orderInfo['engineer_id']);

        if ($engineer_id < 1) {
            $res['code'] = -3;
            $res['msg'] = '非法请求';

            return $res;
        }

        $maintain_end_time = intval($orderInfo['maintain_end_time']);

        if ($maintain_end_time > 0 && $maintain_end_time < strtotime(self::validTime)) { // 超出电话有效时间

            $res['code'] = -4;
            $res['msg'] = '号码已失效';

            return $res;
        }

        if ($orderInfo['number']) { // 中转号码存在且有效
            $res['code'] = 0;
            $res['msg'] = '成功';
            $res['tellphone'] = $orderInfo['number'];

            return $res;
        }

        // 查找出工程师不能用于该订单的中转号码的id
        $where['engineer_id'] =  $engineer_id;
        $where['trans_phone_id'] = array('exp','is not null');
        $where['id'] = array('NEQ', $order_id);
        $where['maintain_end_time'] = array(0, array('GT', strtotime(self::validTime)), 'OR');

        $transPhoneIds = $orderModel->field('trans_phone_id')->where($where)->select();

        // 查找出合适的中转号码
        $map['status'] = 0;

        if ($transPhoneIds) {
            $transPhoneIdArray = array();

            foreach ($transPhoneIds as $pID) {
                $transPhoneIdArray[] = $pID['trans_phone_id'];
            }
            $map['id'] = array('NOT IN', $transPhoneIdArray);
        }

        $transPhoneModel = M('TransPhone');

        // 查找出一个可用的中转号码
        $usable = $transPhoneModel->field('id, number')->where($map)->find();

        if (!$usable) { // 没有可用的有效中转号码
            $res['code'] = -5;
            $res['msg'] = '没有可用的电话号码了，请联系管理员';

            return $res;
        }

        // 更新订单的中转号码信息
        $updateRes = $orderModel->where(array('id' => $order_id))->save(array('trans_phone_id' => $usable['id']));

        if ($updateRes === false) { // 更新失败
            $res['code'] = -6;
            $res['msg'] = '数据异常';

            return $res;
        }

        $res['code'] = 0;
        $res['msg'] = '成功';
        $res['tellphone'] = $usable['number'];

        return $res;
    }

//    public function accountInfo()
//    {
//        $url = $this->commonUrl.'/Accounts/'.$this->accountID.'/AccountInfo?sig='.$this->getSig();
//        $info = $this->getInfoByUrl($url, $this->getAuth());
//        dump($info);
//    }
//
//    public function subFreeNumbers()
//    {
//        $url = $this->subURL('Enterprises/freeNumbers');
//        $data = array(
//            'freeNumbers' => array(
//                'appId' => $this->appID
//            )
//        );
//        $info = $this->getSubInfoByUrl($url, $data);
//        dump($info);
//    }
//
//    public function subCreateNumberPair()
//    {
//        $url = $this->subURL('Enterprises/createNumberPair');
//        $data = array(
//            'createNumberPair' => array(
//                'appId' => $this->appID,
//                'numberA' => '13269129906', // '15267151601',
//                'numberB' => '15267151601'  // '13269129906'
//            )
//        );
//        $info = $this->getSubInfoByUrl($url, $data);
//        dump($info);
//    }
//
//    public function subDropNumberPair()
//    {
//        $url = $this->subURL('Enterprises/dropNumberPair');
//        $data = array(
//            'dropNumberPair' => array(
//                'appId' => $this->appID,
//                'numberA' => '15267151601',
//                'numberB' => '13269129906'
//            )
//        );
//        $info = $this->getSubInfoByUrl($url, $data);
//        dump($info);
//    }
//
//    public function subCreateUser()
//    {
//        $url = $this->subURL('Enterprises/createUser');
//        $data = array(
//            'createUser' => array(
//                'appId' => $this->appID,
//                'mobile' => '15267151601',
//                'displayName' => 'test'
//            )
//        );
//        $info = $this->getSubInfoByUrl($url, $data);
//        dump($info);
//    }
//
//    public function subDropUser()
//    {
//        $url = $this->subURL('Enterprises/dropUser');
//        $data = array(
//            'dropUser' => array(
//                'appId' => $this->appID,
//                'mobile' => '15267151601',
//            )
//        );
//        $info = $this->getSubInfoByUrl($url, $data);
//        dump($info);
//    }
//
//    private function subURL($middleUrl)
//    {
//        return $this->commonUrl.'/SubAccounts/'.$this->subAccountID.'/'.$middleUrl.'?sig='.$this->getSubSig();
//    }
//
//    private function getSubInfoByUrl($url, $data = null)
//    {
//        return $this->getInfoByUrl($url, $this->getSubAuth(), $data);
//    }
//
//    private function getInfoByUrl($url, $auth, $data = null)
//    {
//        $ch = curl_init();
//
//        if (is_array($data)) {
//            $data = json_encode($data);
//        }
//
//        $header =  array(
//            "Accept:application/json;",
//            "Content-Type:application/json;charset=utf-8;",
//            "Authorization:".$auth
//        );
//
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_POST, true);
//
//        if ($data) {
//            $header[] = "Content-Length:".strlen($data);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//        }
//
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
//
//        $result = curl_exec($ch);
//        curl_close($ch);
//
//        return $result;
//    }
//
//    private function getSig()
//    {
//        return strtoupper(md5($this->accountID . $this->accountToken . date("YmdHis", time())));
//    }
//
//    private function getSubSig()
//    {
//        return strtoupper(md5($this->subAccountID . $this->subAccountToken . date("YmdHis", time())));
//    }
//
//    private function getAuth()
//    {
//        return base64_encode($this->accountID . ':' . date("YmdHis", time()));
//    }
//
//    private function getSubAuth()
//    {
//        return base64_encode($this->subAccountID . ':' . date("YmdHis", time()));
//    }
}