<?php
 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 天猫砕屏险 Dates: 2016-08-05
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

class InsuranceController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insurance(){
        $this->display();
    }

    /**
     * insurance列表
     *
     * @return void
     */
    public function insuranceRows()
    {
        $model = M('insurance');
        $post = I('post.');

        if (!empty($post['start_time']) && empty($post['end_time'])) {
            $map['effect_date'] = array('EGT', $post['start_time']);
        } else if(!empty($post['end_time']) && empty($post['start_time'])) {
            $map['effect_date'] = array('ELT', date("Y-m-d H:i:s", strtotime($post['end_time']) + 24 * 60 * 60 - 1));
        } else if(!empty($post['start_time']) && !empty($post['end_time'])) {
            $map['effect_date'] = array(array('EGT', $post['start_time']), array('ELT', date("Y-m-d H:i:s", strtotime($post['end_time']) + 24 * 60 * 60 - 1)));
        }

        if (!empty($post['address']) && $post['address'] != '全部') {
            $map['buyer_address'] = array('like', '%' . $post['address'] . '%');
        }

        if (I('post.keyword')) {
            $like['third_part_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['buyer_mobile'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['buyer_name'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['biz_order_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['parent_order_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['service_order_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['biz_order_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        $model->where($map);
        $model->limit($this->page());
        $model->order('id desc');
        $rst['rows'] = $model->select();

        $model->where($map);
        $rst['total'] = $model->count();

        $this->ajaxReturn($rst);
    }


    /**
     * insuranceOrder列表
     *
     * @return void
     */
    public function insuranceOrderRows()
    {
        $model = M('insurance_order');
        if (I('post.create_time_start') && empty(I('post.create_time_end'))) {
            $map['appointment_time'] = array('EGT' => strtotime(I('post.create_time_start')));
        }

        if (I('post.create_time_end') && empty(I('post.create_time_start'))) {
            $map['appointment_time'] = array('ELT' => strtotime(I('post.create_time_start')) + 24 * 60 * 60 - 1);
        }

        if (I('post.create_time_start') &&  I('post.create_time_end')) {
                $map['appointment_time '] = array(array('gt',strtotime(I('post.create_time_start'))),array('lt',strtotime(I('post.create_time_end')) +24*60*60-1),'and');
        }

        if (I('post.keyword')) {
            $like['third_part_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['buyer_mobile'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['buyer_name'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['biz_order_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['service_order_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        if (I('post.address')) {
            $map['buyer_address'] = array('LIKE', '%' . I('post.address') . '%');
        }

        if (I('post.status') && I('post.status') != 'all') {
            $map['status'] = I('post.status');
        }

        $model->limit($this->page());
        $model->order('id desc');
        $model->where($map);

        $rst['rows'] = $model->select();
        $rst['total'] = $model->count();

        $this->ajaxReturn($rst);
    }

    /**
     * 接单 status 3
     *
     * @return void
     */
    public function receiveOrder()
    {
        $map = array();
        $map['id'] = I('post.id');

        $data = array();
        $data['modify_time'] = date('Y-m-d H:i:s');
        $data['status'] = 3;

        if (M('insurance_order')->where($map)->save($data) === false) {
            $this->ajaxReturn(['success'=>false,'errorMsg'=>'预约失败']);
        } else {
            $items = M('insurance_order')->where($map)->find();

            /** 通知天猫 */
            if ($this->tmall($items)) {
                $this->ajaxReturn(['success' => true ,'errorMsg'=>'修改完成']);
            } else {
                $this->ajaxReturn(['success'=>false,'errorMsg'=>'预约成功！通知天猫失败，请联系管理员']);
            }
        }
    }

    /**
     * 预约 status 4
     *
     * @return void
     */
    public function appointmentOrder()
    {
        $engineer = M('engineer')->where(array('id' => I('post.contact_engineer')))->find();

        $map = array();
        $map['id'] = I('get.id');

        $data = array();
        $data['contact_engineer'] = I('post.contact_engineer');
        $data['contact_name'] = $engineer['name'];
        $data['contact_phone'] = $engineer['cellphone'];
        $data['address'] = I('post.address');
        $data['comments'] = I('post.comments');
        $data['modify_time'] = date('Y-m-d H:i:s');
        $data['status'] = 4;
        $data['appointment_time'] = strtotime(I('post.appointment_time'));

        if (M('insurance_order')->where($map)->save($data) === false) {
            $this->ajaxReturn(['success'=>false,'errorMsg'=>'预约失败']);
        } else {
            $item = M('insurance_order')->where($map)->find();

            /** 通知天猫 */
            if ($this->tmall($item)) {
                $this->ajaxReturn(['success' => true ,'errorMsg'=>'修改完成']);
            } else {
                $this->ajaxReturn(['success'=>false,'errorMsg'=>'预约成功！通知天猫失败，请联系管理员']);
            }
        }
    }

    /**
     * 订单详情
     *
     * @return void
     */
    public function detailOrder($id)
    {
        $map = array();
        $map['id'] = $id;
        $rst = M('insurance')->where($map)->find();

        $this->ajaxReturn($rst);
    }

    /**
     * 完成订单 status 5
     *
     * @return void
     */
    public function finishOrder($id)
    {
        $post = I('post.');

        if (empty($post['is_visited'])) {
            $this->ajaxReturn(array('success' => false, 'errorMsg' => '是否上门不能为空'));
        }

        if (empty($post['service_fee'])) {
            $this->ajaxReturn(array('success' => false, 'errorMsg' => '服务金额不能为空'));
        }

        if (empty($post['phone_imei'])) {
            $this->ajaxReturn(array('success' => false, 'errorMsg' => 'IMEI号码不能为空'));
        }

        if (empty($post['before_service_memo'])) {
            $this->ajaxReturn(array('success' => false, 'errorMsg' => '故障描述不能为空'));
        }

        if (empty($post['after_service_memo'])) {
            $this->ajaxReturn(array('success' => false, 'errorMsg' => '故障描述不能为空'));
        }

        $map = array();
        $map['id'] = I('get.id');

        $data = array();
        $data['status'] = 5;
        $data['finish_time'] = time();
        $data['is_visited'] = $post['is_visited'];
        $data['service_fee'] = $post['service_fee'];
        $data['before_service_memo'] = $post['before_service_memo'];
        $data['after_service_memo'] = $post['after_service_memo'];
        $data['phone_imei'] = $post['phone_imei'];

        // pr($data);exit;

        if (M('insurance_order')->where($map)->save($data) === false) {
            $this->error('完结失败！');
        } else {
            $item = M('insurance_order')->where($map)->find();

            /** 通知天猫 */
            if ($this->tmall($item)) {
                $this->ajaxReturn(['success'=>true,'errorMsg'=>'修改完成']);
            } else {
                $this->ajaxReturn(['success'=>false,'errorMsg'=>'预约成功！通知天猫失败，请联系管理员']);
            }
        }
    }

    /**
     * 取消订单 status 10
     *
     * @return void
     */
    public function cancelOrder($id)
    {
        $map = array();
        $map['id'] = I('get.id');

        if (empty(I('post.comments'))) {
            $this->ajaxReturn(array('success' => false, 'errorMsg'=>'备注不能为空'));
        }

        if (empty(I('post.is_visited'))) {
            $this->ajaxReturn(array('success' => false, 'errorMsg'=>'是否上门不能为空'));
        }

        $item = M('insurance_order')->where($map)->find();

        $data = array();

        if ($item['status'] == 4 && isset($item['service_name']) && $item['service_name'] == 'suiping_maintain') {
            $data['status'] = 11;
        } else {
            $data['status'] = 10;
        }
        
        $data['comments'] = I('post.comments');
        $data['finish_time'] = time();
        $data['is_visited'] = I('post.is_visited');

        // pr($data);exit;

        if (M('insurance_order')->where($map)->save($data) === false) {
            $this->error('预约失败！');
        } else {
            $item = M('insurance_order')->where($map)->find();

            if ($this->tmall($item)) {
                $this->ajaxReturn(['success' => true,'errorMsg'=>'修改完成']);
            } else {
                $this->ajaxReturn(['success'=>false,'errorMsg'=>'预约成功！通知天猫失败，请联系管理员']);
            }
        }
    }

    /**
     * 通知天猫
     *
     * @return void
     */
    public function tmall($item)
    {
        Vendor('mayiBaoxian.TopSdk');

        $appKey = '23343855';
        $appSecret = '5415b52df1b96658cd9bfaa74df76605';
        // $sessionKey = '62006107be7ce045e8ZZ77555c746bffed6dd6c4c76fa382411934879';
        $sessionKey = '6100917f2d4823157a3a5de56141f4574ff18b77ac29c7e2411934879';

        /** 沙箱 app key */
        // $testAppKey = '1023343855';
        /** 沙箱 app secret */
        // $testAppSecret = 'sandboxdf1b96658cd9bfaa74df76605';
        /** 沙箱请求地址 */
        // $testGateWay = 'http://gw.api.tbsandbox.com/router/rest';
        /** 沙箱 session key */
        // $testSessionKey = '610080608062b01c16f710bcf11b9b398cc6dd3945f4e672074082786';

        // $client = new \TopClient;
        // $client->gatewayUrl = $testGateWay;
        // $client->appkey = $testAppKey;
        // $client->secretKey = $testAppSecret;
        // $client->format = 'json';

        $client = new \TopClient;
        $client->appkey = $appKey;
        $client->secretKey = $appSecret;
        $client->format = 'json';

        $req = new \TmallServicecenterWorkcardStatusUpdateRequest;
        $req->setWorkcardId($item['third_part_id']);
        $req->setType("1");
        $req->setStatus($item['status']);
        $req->setBuyerId($item['buyer_id']);
        $req->setUpdater("闪修侠");
        $req->setUpdateDate((string)strtotime($item['modify_time']) . '000');

        if (isset($item['service_name']) && $item['service_name'] == 'suiping_maintain') {
            switch ($item['status']) {
                case '1':
                    break;
                case '3':
                    break;
                case '4':
                    $req->setComments($item['comments']);
                    $req->setAddress($item['address']);
                    $req->setContactName($item['contact_name']);
                    $req->setContactPhone($item['contact_phone']);
                    $req->setServiceDate((string)$item['appointment_time'] . '000');
                    break;
                case '5':
                    $req->setCompleteDate((string)$item['finish_time'] . '000'); # 完成时间
                    $req->setContactName($item['contact_name']); # 工程师名称
                    $req->setContactPhone($item['contact_phone']); # 工程师电话
                    $req->setServiceFee((string)($item['service_fee'] * 100)); # 服务费 单位分
                    $req->setIsVisit((int)$item['is_visited']); # 是否上门 1 上门 2 未上门
                    $req->setBeforeServiceMemo($item['before_service_memo']); # 维修前故障描述
                    $req->setAfterServiceMemo($item['after_service_memo']); # 维修后故障描述
                    $req->setPhoneImei($item['phone_imei']); # imei
                    break;
                case '10':
                    $req->setCompleteDate((string)$item['finish_time'] . '000'); # 完成时间
                    $req->setComments($item['comments']);
                    $req->setIsVisit(($item['is_visited'] == 1) ? 'true' : 'false'); # 是否上门 1 上门 2 未上门
                    break;
                case '11':
                    $req->setCompleteDate((string)$item['finish_time'] . '000'); # 完成时间
                    $req->setComments($item['comments']);
                    $req->setIsVisit(($item['is_visited'] == 1) ? 'true' : 'false'); # 是否上门 1 上门 2 未上门
                    break;
                case '12':
                    break;
                case '108':
                    break;
                case '103':
                    break;
                default:
                    break;
            }
        } else {
            switch ($item['status']) {
                case '1':
                case '3':
                case '4':
                    // 兼容一期 4 -》 3
                    $req->setStatus('3');
                    $req->setComments($item['comments']);
                    $req->setAddress($item['address']);
                    $req->setContactName($item['contact_name']);
                    $req->setContactPhone($item['contact_phone']);
                case '5':
                case '10':
                    $req->setComments($item['comments']);
                case '12':
                case '108':
                case '103':
                default:
            }
        }

        /* pr($req);exit; */

        $resp = $client->execute($req, $sessionKey);

        // pr($resp);exit;

        if ($resp->rs) {
            return 1;
        } else {
            $msg = 'Error: Insurance update api error, data [' . json_encode($resp) . '], error code [' . $resp->code . '], error msg [';
            $msg .= $resp->msg . ']';
            \Think\Log::write($msg, 'ERR');
            return 0;
        }
    }

    public function insuranceOrder() 
    {
        $this->display();
    }

   /*
    * 组织地区
    *  @return void
    * */
    public function organization()
    {
        $orgs = session('organizations');
        array_unshift($orgs,array('alias'=>'全部','id'=>false));
        $this->ajaxReturn(array_values($orgs));
    }
    
    /**
     * 导出 (工单)
     */
    public function insuranceExport() 
    {
        $model = M('insurance_order');
        if (I('post.create_time_start') && empty(I('post.create_time_end'))) {
            $map['appointment_time'] = array('EGT' => strtotime(I('post.create_time_start')));
        }
        
        if (I('post.create_time_end') && empty(I('post.create_time_start'))) {
            $map['appointment_time'] = array('ELT' => strtotime(I('post.create_time_start')) + 24 * 60 * 60 - 1);
        }
        
        if (I('post.create_time_start') &&  I('post.create_time_end')) {
            $map['appointment_time '] = array(array('gt',strtotime(I('post.create_time_start'))),array('lt',strtotime(I('post.create_time_end')) +24*60*60-1),'and');
        }
        
        if (I('post.keyword')) {
            $like['third_part_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['buyer_mobile'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['buyer_name'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['biz_order_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['service_order_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }
        
        if (I('post.address')) {
            $map['buyer_address'] = array('LIKE', '%' . I('post.address') . '%');
        }
        
        if (I('post.status') && I('post.status') != 'all') {
            $map['status'] = I('post.status');
        }
        
        $model->order('id desc');
        $model->where($map);
        $list = $model->select();
        
        
        $status = array(
            1 => '下单',
            3 => '预约',
            5 => '已完成',
            10 => '不满足服务规则',
            108 => '预约超时',
            103 => '履行超时',
            12 => '买家撤销',
            11 => '出险失败'
        );
        
        $params = array();
        $params[] = array('订单ID', '订单号', '合同编号', '服务名称', '商品编号','商品名称', '商品价格', 
                        '客户名', '手机号码', '邮箱', '地址', '申请时间', '更新时间', '预约时间', '结单时间', '状态', '备注');
        
        foreach ($list as $item) {
            $params[] = array(
                $item['id'], $item['third_part_id'], $item['contract_id'], $item['name'],' '.$item['commodity_number'], $item['commodity_name'], round($item['commodity_price'] / 100, 2),
                $item['buyer_name'], $item['buyer_mobile'], $item['buyer_email'], $item['buyer_address'], $item['apply_time'], 
                $item['modify_time'], date('Y-m-d H:i:s', $item['appointment_time']), date('Y-m-d H:i:s', $item['finish_time']), 
                $status[$item['status']], $item['remark'] . ';' .$item['comments']
            );
        }
        
        $this->exportData('天猫工单-'.date('Y-m-h-H-i-s'), $params);
    }

    /**
     * 导出 (保险单)
     */
    public function contractExport() 
    {
        $post = I('post.');
        $map = array();

        if (!empty($post['start_time']) && empty($post['end_time'])) {
            $map['receive_time'] = array('EGT' => date('Y-m-d H:i:s', strtotime($post['start_time'])));
        }

        if (!empty($post['end_time']) && empty($post['start_time'])) {
            $map['receive_time'] = array('ELT' => date('Y-m-d H:i:s', strtotime($post['end_time']) + 24 * 60 * 60 - 1));
        }

        if (!empty($post['end_time']) && !empty($post['start_time'])) {
            $map['receive_time'] = array('ELT' => date('Y-m-d H:i:s', strtotime($post['end_time']) + 24 * 60 * 60 - 1));

            $map['receive_time'] = array(
                array('EGT', date('Y-m-d H:i:s', strtotime($post['start_time']))),
                array('ELT', date('Y-m-d H:i:s', strtotime($post['end_time']) + 24 * 60 * 60 - 1)),
                'AND'
            );
        }

        if (!empty($post['address']) && $post['address'] != '全部') {
            $map['buyer_address'] = array('like', '%' . $post['address'] . '%');
        }

        if (I('post.keyword')) {
            $like['third_part_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['buyer_mobile'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['buyer_name'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['biz_order_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['service_order_id'] = array('LIKE', '%' . I('post.keyword') . '%');
            $like['_logic'] = 'or';
            $map['_complex'] = $like;
        }

        $list = M('insurance')->where($map)->order('id desc')->select();

        $params = array();
        $params[] = array(
            '订单ID',
            '合同编号',
            '订单编号',
            '商品名称',
            '商品价格',
            '服务名称',
            '服务次数',
            '客户名',
            '手机号码',
            '邮箱',
            '地址',
            '下单时间',
            '生效时间',
            '失效时间'
        );

        foreach ($list as $item) {
            $params[] = array(
                $item['id'], 
                $item['third_part_id'],
                ' ' . $item['parent_order_id'],
                $item['commodity_name'],
                $item['commodity_price'],
                $item['service_name'],
                $item['service_count'],
                $item['buyer_name'],
                $item['buyer_phone'],
                $item['buyer_email'],
                $item['buyer_address'],
                $item['receive_time'],
                $item['effect_date'],
                $item['expire_date'],
            );
        }
        
        $this->exportData('天猫保险-' . date('Y-m-h-H-i-s'), $params);
    }

    /**
     * 保险通知
     */
    public function notice()
    {
        $this->display();
    }

    /*
     *获取所有员工
     */
    public function userRow()
    {
        $rst['rows'] = M('user')->field('user.id, user.realname')->select();
        $rst['total'] = M('user')->count();

        $this->ajaxReturn($rst);
    }

    /*
     * 添加员工
     */
    public function addUser()
    {

        $post = I('post.data');
        $model = M('insurance_work');

        foreach ($post as $value) {
            $result = $model->where(array('user_id' => array('EQ', $value['id'])))->select();

            if (!$result) {
                $data = array();
                $data['user_id'] = $value['id'];
                $model->add($data);
            }
        }

        $model->where(array('switch' => array('EQ', '0')));
        $model->join('left join user on user.id = insurance_work.user_id');
        $model->field('user.id, user.realname');
        $rst['rows']  = $model->select();

        $this->ajaxReturn($rst);

    }

    /*
     * 删除员工
     */
    public function delUser()
    {
        $post = I('post.data');
        $model = M('insurance_work');

        foreach ($post as $value) {
            $model->where(array('user_id' => array('EQ', $value['id'])))->delete();
        }

        $model->where(array('switch' => array('EQ', '0')));
        $model->join('left join user on user.id = insurance_work.user_id');
        $model->field('user.id, user.realname');
        $rst['rows']  = $model->select();

        $this->ajaxReturn($rst);

    }

    /*
     * 获取工作员工
     */
    public function workUser()
    {
        $get = I('get.');
        $model = M('insurance_work');
        $model->join('left join user on user.id = insurance_work.user_id');
        $model->where(array('switch' => array('EQ', $get['param'])));
        $model->field('user.id, user.realname');
        $rst['rows'] = $model->select();

        $this->ajaxReturn($rst);
    }

    /*
    * 员工上下班
    */
    public function upWork()
    {
        $get = I('get.');
        $post = I('post.');

        $model = M('insurance_work');

        foreach ($post['data'] as $value) {
            $where = $model->where(array('user_id' => array('EQ', $value['id'])));

            if ($get['param'] == 1) {
                $where->save(array('switch' => 1));
            } else {
                $where->save(array('switch' => 0));
            }
        }

        $model->join('left join user on user.id = insurance_work.user_id');
        $model->where(array('switch' => array('EQ', $get['param'] == 1 ? $get['param'] == 0 : $get['param'] == 1 )));
        $model->field('user.id, user.realname');
        $rst['rows'] = $model->select();

        $this->ajaxReturn($rst);
    }

    /**
     * 上传核赔图片
     *
     * @return void
     */
    private function uploadClaimImg($filepath)
    {
        Vendor('mayiBaoxian.TopSdk');

        $bizNoMock = "policy_biz_no_mock";
        $userIdMock = "shanxiuxia";
        $filename = basename($filepath);

        $req = new \AlipayBaoxianClaimUploadattachmentRequest;
        $req->setOutBizNo(uniqid());
        $req->setBizSource('shanxiuxia');
        $req->setAttachmentKey($filename);
        $req->setPolicyBizNo($bizNoMock);
        $req->setUploadUser($userIdMock);
        $req->setBase64Bytes("false");
        $req->setAttachmentByte('@' . $filepath);

        $appKey = '23343855';
        $appSecret = '5415b52df1b96658cd9bfaa74df76605';
        $sessionKey = '6100917f2d4823157a3a5de56141f4574ff18b77ac29c7e2411934879';

        /** 沙箱 app key */
        // $testAppKey = '1023343855';
        /** 沙箱 app secret */
        // $testAppSecret = 'sandboxdf1b96658cd9bfaa74df76605';
        /** 沙箱请求地址 */
        // $testGateWay = 'http://gw.api.tbsandbox.com/router/rest';
        /** 沙箱 session key */
        // $testSessionKey = '610080608062b01c16f710bcf11b9b398cc6dd3945f4e672074082786';

        // $client = new \TopClient;
        // $client->gatewayUrl = $testGateWay;
        // $client->appkey = $testAppKey;
        // $client->secretKey = $testAppSecret;
        // $client->format = 'json';

        $client = new \TopClient;
        $client->appkey = $appKey;
        $client->secretKey = $appSecret;
        $client->format = 'json';

        /** pr($client);exit; */

        //执行请求
        $resp = $client->execute($req, $sessionKey);

        /** pr($resp);exit; */

/**         object(stdClass)#11 (2) {
  ["upload_result"]=>
  object(stdClass)#12 (2) {
    ["is_success"]=>
    bool(true)
    ["model"]=>
    object(stdClass)#13 (3) {
      ["e_tag"]=>
      string(32) "DD1256940A19B09EE2B03F37EC9FB139"
      ["oss_path"]=>
      string(185) "http://cn-hangzhou.oss.aliyun-inc.com/bxcloudstoretest/0/policy_biz_no_mock/5912c29ba294f.jpg?Expires=1809857144&OSSAccessKeyId=Rex1c2QQNnDrmtzd&Signature=Pl3sTUxAGT1xezeNAe9xvkUYnP8%3D"
      ["size"]=>
      int(8897)
    }
  }
  ["request_id"]=>
  string(13) "15a04te5bo9wk"
} */
        if ($resp->upload_result->is_success) {
            return $resp->upload_result->model;
        } else {
            $msg = 'Error: Insurance order upload img api error, request data [' . serialize($req) . '], response data [' . json_encode($resp) . ']';
            \Think\Log::write($msg, 'ERR');
            return false;
        }
    }

    /**
     * 更新核赔材料
     *
     * @return void
     */
    private function updateClaimStatus($item, $oss)
    {
        $infos = array();
        $infos[] = array(
            'name' => 'before_repair',
            'path' => $oss['img1']->oss_path,
            'size' => $oss['img1']->size,
            'type' => '1',
        );

        $infos[] = array(
            'name' => 'before_repair_part',
            'path' => $oss['img2']->oss_path,
            'size' => $oss['img2']->size,
            'type' => '1',
        );

        $infos[] = array(
            'name' => 'after_repair',
            'path' => $oss['img3']->oss_path,
            'size' => $oss['img3']->size,
            'type' => '1',
        );

        $infos[] = array(
            'name' => 'invoice',
            'path' => $oss['img4']->oss_path,
            'size' => $oss['img4']->size,
            'type' => '1',
        );

        //上传请求
        $req = new \AlipayBaoxianClaimUpdateRequest();
        $req->setOutBizNo(uniqid());
        $req->setBizSource('shanxiuxia');
        $req->setSpNo('6260');
        $req->setClaimAttachments(json_encode($infos));
        $req->setClaimOutBizNo($item['third_part_id']);
        $req->setPolicyBizNo($item['contract_id']);

        $appKey = '23343855';
        $appSecret = '5415b52df1b96658cd9bfaa74df76605';
        $sessionKey = '6100917f2d4823157a3a5de56141f4574ff18b77ac29c7e2411934879'; 

        /** 沙箱 app key */
        // $testAppKey = '1023343855';
        /** 沙箱 app secret */
        // $testAppSecret = 'sandboxdf1b96658cd9bfaa74df76605';
        /** 沙箱请求地址 */
        // $testGateWay = 'http://gw.api.tbsandbox.com/router/rest';
        /** 沙箱 session key */
        // $testSessionKey = '610080608062b01c16f710bcf11b9b398cc6dd3945f4e672074082786';

        // $client = new \TopClient;
        // $client->gatewayUrl = $testGateWay;
        // $client->appkey = $testAppKey;
        // $client->secretKey = $testAppSecret;
        // $client->format = 'json';

        $client = new \TopClient;
        $client->appkey = $appKey;
        $client->secretKey = $appSecret;
        $client->format = 'json';

        //执行请求
        $resp = $client->execute($req, $sessionKey);

        if ($resp->result->is_success) {
            return true;
        } else {
            $msg = 'Error: Insurance order update claim api error, request data [' . serialize($req) . '], response data [' . json_encode($resp) . ']';
            \Think\Log::write($msg, 'ERR');
            return false;
        }
    }

    /**
     * 核赔
     *
     * @return void
     */
    public function updateAuditDatum()
    {
        $map = array();
        $map['id'] = I('get.id');

        $item = M('insurance_order')->where($map)->find();

        $rst = array();

        if (empty($item)) {
            $rst['success'] = false;
            $rst['errorMsg'] = '记录不存在！';
            $this->ajaxReturn($rst);
        }

        $upload = new \Think\Upload();
        $upload->maxSize = 10485760;
        $upload->exts = explode(',', 'jpg,gif,png,jpeg');
        $upload->rootPath = './upload/tmall/';
        $upload->saveName = array('uniqid','');
        $upload->autoSub = true;
        $upload->subName = array('date','Ymd');
        $info = $upload->upload();

        if (!$info) {
            $rst['success'] = false;
            $rst['errorMsg'] = $upload->getError();
            $this->ajaxReturn($rst);
        }

        if (count($info) < 4) {
            $rst['success'] = false;
            $rst['errorMsg'] = '图片数量错误！';
            $this->ajaxReturn($rst);
        }

        $data = array();
        $data['img1'] = $upload->rootPath . $info['img1']['savepath'] . $info['img1']['savename'];
        $data['img2'] = $upload->rootPath . $info['img2']['savepath'] . $info['img2']['savename'];
        $data['img3'] = $upload->rootPath . $info['img3']['savepath'] . $info['img3']['savename'];
        $data['img4'] = $upload->rootPath . $info['img4']['savepath'] . $info['img4']['savename'];

        /** 上传天猫 */
        $oss = array();
        $oss['img1'] = $this->uploadClaimImg($data['img1']);
        $oss['img2'] = $this->uploadClaimImg($data['img2']);
        $oss['img3'] = $this->uploadClaimImg($data['img3']);
        $oss['img4'] = $this->uploadClaimImg($data['img4']);

        if (!$oss['img1'] || !$oss['img2'] || !$oss['img3'] || !$oss['img4']) {
            $rst['success'] = false;
            $rst['errorMsg'] = '图片上传天猫错误，请联系研发处理！';
            $this->ajaxReturn($rst);
        }

        /** 更新服务状态 */
        if (!$this->updateClaimStatus($item, $oss)) {
            $rst['success'] = false;
            $rst['errorMsg'] = '核赔资料上传天猫错误，请联系研发处理！';
            $this->ajaxReturn($rst);
        }

        /** 更新数据 */
        $data['oss_img1'] = $oss['img1']->oss_path;
        $data['oss_img2'] = $oss['img2']->oss_path;
        $data['oss_img3'] = $oss['img3']->oss_path;
        $data['oss_img4'] = $oss['img4']->oss_path;
        $data['is_update_claim'] = 1;
        
        /** pr($data);exit; */

        $map = array();
        $map['id'] = I('get.id');

        if (M('insurance_order')->where($map)->limit(1)->save($data) === false) {
            $rst['success'] = false;
            $rst['errorMsg'] = '数据保存错误！';
            $this->ajaxReturn($rst);
        }

        $rst['success'] = true;
        $this->ajaxReturn($rst);
    }
}