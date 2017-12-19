<?php
// +------------------------------------------------------------------------------------------
// | Author: zhujianping <zhujianping@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: 第三方保险  Dates: 2017-06-05
// +------------------------------------------------------------------------------------------

namespace V2\Controller;

use Think\Controller;
use Think\App;
use Think\Exception;
use V2\Validate\ThirdInsuranceValidate;

class ThirdPartyInsuranceController extends ApiController
{
    private $baseUrl = 'http://api.shanxiuxia.com';

    /**
     * 验证来源和令牌
     */
    public function _initialize()
    {
        header("Access-Control-Allow-Origin:*");
        if(in_array(strtolower(ACTION_NAME),array('customerthird','brokeninfo'))){
            return;
        }
        $model=M('third_party_token');
        if(!$data=$model->where(array('source'=>I('get.source'),'token'=>I('get.token')))->find()){
            $this->error('非法操作');
        }
        $this->source=$data['source'];
        $this->token=$data['token'];
        $this->assign('baseurl',$this->baseUrl);
        $this->assign('source',$data['source']);
        $this->assign('token',$data['token']);

    }

    /**
     * 获取服务器当前时间
     */
    public function getDate()
    {
        $text = date('Y-m-d H:i:s');
        $font = './Public/fonts/msyh.ttf';
        
        $im = imagecreate(700, 100);
        
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        
        imagefilledrectangle($im, 0, 0, 700, 100, $white);
                      
        imagettftext($im, 50, 0, 20, 75, $black, $font, $text); //在阴影上输出一个黑色的字符串
        
        header("Content-type: image/png");
        imagepng($im);
        imagedestroy($im);
    }

    /**
     * 保险单下单页
     */
    public function getPolicy()
    {

        /*$validate=new ThirdInsuranceValidate();
        if(!$validate->verification(I('get.')) || !$validate->verificationOther()){
            $this->responseError($validate->errorInfo['msg'],$validate->errorInfo['code']);
        }
        //$insurance = M('phomal_insurance')->where(array('id' => I('get.piId'), 'status' => 1))->find();
        $data = array();
        $data['order_number'] = I('get.order_number');
        $data['customer'] = I('get.customer');
        $data['cellphone'] = I('get.cellphone');
        $data['phone_name'] = I('get.phoneName');
        $data['phone_imei'] = I('get.imei');
        //$data['insurance_id'] = $insurance['id'];
        //$data['service_title'] = $insurance['title'];
        //$data['duration'] = $insurance['duration'];
        //$data['price'] = $insurance['price'];
        $data['effect_time'] = date('Y-m-d', strtotime('tomorrow'));
        
        $this->responseSuccess($data);*/

        $this->display();

    }

    public function getCreatePolicy()
    {
        $brandData=json_decode(file_get_contents($this->baseUrl.'/V2/Phone/brand'),true);
        $this->assign('brands',$brandData['data']);
        $this->display();
    }

    /**
     * 保险订单列表
     */
    public function getPolicies()
    {
        $model = M('third_insurance_order');
        $source = I('get.source');
        $map = array('third_party_order.source'=>$source);

        $join = "tio left join `third_party_order` on third_party_order.order_number = tio.old_order_number
                left join `third_party_order` as o2 on o2.order_number = tio.order_number
                ";
        $count = $model->join($join)->where($map)->count();
        $Page = new \Think\Page($count,30);
        $Page->setConfig('header','');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $data = $model->join($join)
            ->field("tio.id,tio.invoice_img,tio.imei_img,tio.number,third_party_order.order_price as price,tio.audit_status,tio.status,tio.customer,tio.cellphone, third_party_order.phone_name,
                             third_party_order.comment,third_party_order.phone_imei,tio.reason,tio.remark,tio.create_time
                            ")
            ->where($map)->order('tio.broken_time desc, tio.create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        //$this->responseSuccess($data);
        $this->assign('page',$Page->show());
        $this->assign('list',$data);
        $this->display();
    }

    /**
     * 保险单下单页
     */
    public function getEditPolicy()
    {
        $brandData=json_decode(file_get_contents($this->baseUrl.'/V2/Phone/brand'),true);

        $model = M('third_insurance_order');
        $id = I('get.id');
        $map = array('tio.id'=>$id);

        $join = "tio left join `third_party_order` on third_party_order.order_number = tio.old_order_number
                left join `third_party_order` as o2 on o2.order_number = tio.order_number
                ";
        $data = $model->join($join)
            ->field("tio.id,tio.brand_id,tio.phone_id,tio.invoice_img,tio.imei_img,tio.number,third_party_order.order_price,tio.audit_status,tio.status,tio.customer,tio.cellphone, third_party_order.phone_name,
                             third_party_order.color_name,third_party_order.comment,third_party_order.phone_imei,tio.reason,tio.remark,tio.create_time
                            ")
            ->where($map)->order('tio.broken_time desc, tio.create_time desc')->find();
        //$this->responseSuccess($data);
        $this->assign('brands',$brandData['data']);
        $this->assign('item',$data);
        $this->display();
    }
    /**
     * 编辑保险单
     */
    public function postEditPolicy()
    {
        $model = M('third_insurance_order');
        $validate=new ThirdInsuranceValidate();
        if(!$validate->verification(I('post.')) || !$validate->verificationOther()){
            $this->error($validate->errorInfo['msg']);
        }
        $data=I('post.');
        $id=I('post.id');

        if($_FILES['file']['name']!='' || $_FILES['imei_file']['name']!=''){
            $info = $this->upload();
            if (!$info['success']) {
                $this->error('请上传图片');
            }
            if (isset($info['info']['file'])) {
                $invoice_img = '/upload/' . $info['info']['file']['savepath'] . $info['info']['file']['savename'];
                $data['invoice_img']=$invoice_img;
            }
            if (isset($info['info']['imei_file'])) {
                $imei_img = '/upload/' . $info['info']['imei_file']['savepath'] . $info['info']['imei_file']['savename'];
                $data['imei_img']=$imei_img;
            }
        }

        try{
            $model->startTrans();
            unset($data['id']);
            $number=$model->where(array('id'=>$id))->getField('old_order_number');
            $model->where(array('id'=>$id))->save($data);
            M('third_party_order')->where(array('order_number'=>$number))->save($data);
            $model->commit();
            header('Location:'.U('policies',array('source'=>I('param.source'),'token'=>I('param.token'))));
        }catch (Exception $e){
            $model->rollback();
        }



    }


    /**
     * 保险订单列表
     */
    public function getBrokens()
    {
        $model = M('third_insurance_order');
        $source = I('get.source');
        $map = array('third_party_order.source'=>$source,'audit_status'=>1);

        $join = "tio left join `third_party_order` on third_party_order.order_number = tio.old_order_number
                left join `third_party_order` as o2 on o2.order_number = tio.order_number
                ";
        $count = $model->join($join)->where($map)->count();
        $Page = new \Think\Page($count,30);
        $Page->setConfig('header','');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $data = $model->join($join)
            ->field("tio.id,tio.broken_flag,tio.broken_comment,tio.invoice_img,tio.broken_img,tio.number,third_party_order.order_price as price,tio.audit_status,tio.status,tio.customer,tio.cellphone, third_party_order.phone_name,
                             third_party_order.phone_imei,tio.reason,tio.remark,tio.create_time
                            ")
            ->where($map)->order('tio.broken_time desc, tio.create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        //$this->responseSuccess($data);
        $this->assign('page',$Page->show());
        $this->assign('list',$data);
        $this->display();
    }

    
    /**
     * 投保下单
     */
    public function postGeneratePolicy()
    {

        $validate=new ThirdInsuranceValidate();
        if(!$validate->verification(I('post.')) || !$validate->verificationOther()){
            $this->error($validate->errorInfo['msg']);
        }

        $info = $this->upload();

        if (!$info['success']) {
            $this->error('请上传图片');
        }
        if (!$info['info']['file']) {
            $this->error('请上传发票图片');
        }
        if (!$info['info']['imei_file']) {
            $this->error('请上传IMEI图片');
        }
        $invoice_img = '/upload/' . $info['info']['file']['savepath'] . $info['info']['file']['savename'];
        $imei_img = '/upload/' . $info['info']['imei_file']['savepath'] . $info['info']['imei_file']['savename'];
        $data = I('post.');
        $data['invoice_img'] = $invoice_img;
        $data['imei_img'] = $imei_img;
        try {

            M()->startTrans();

            //生成保险单
            $insurance = D('Admin/ThirdInsurance')->addInsuranceOrder($data);

            M()->commit();
            $this->success('添加保险资料成功,请等待审核',U('policies',array('source'=>$this->source,'token'=>$this->token)));
        } catch (\Exception $e) {
            \Think\Log::record("orderId:".I('post.order_number')."-insuranceId:".I('post.order_number')."-错误:" . $e->getMessage());
            M()->rollback();
            $this->error($e->getMessage());
        }
    }

    /**
     * 生成付款链接
     */
    public function getPayUrl()
    {
        $id=I('get.id');
        $number=I('get.number');
        if(empty($id) || empty($number)){
            $this->responseError('缺少订单参数');
        }
        if(!$data=M('third_insurance_order')->where(array('id'=>$id,'number'=>$number))->find()){
            $this->responseError('没有相关保险订单');
        }
        if($data['status']!=0){
            $this->responseError('该保险订单无法付款');
        }

        $pay_url = $this->baseUrl . U("V2/pay/handle?id={$id}&number={$number}&type=T");
        $pay_img = $this->baseUrl . U('V2/pay/qrcode') . '?url=' . urlencode($pay_url);
        $wx_img  = $this->baseUrl . U("V2/weixinpay/handle?id={$id}&number={$number}&type=T&show_type=1");

        // TODO 跳转到付款页面
        $result['pay_url'] = $pay_url;
        $result['pay_img'] = $pay_img;
        $result['weixin_img'] = $wx_img;

        $this->responseSuccess($result);
    }

    /**
     * 生成二维码
     *
     * @return void
     */
    public function getQrcode($url)
    {
        $url=I('get.url');
        require_once(VENDOR_PATH . "phpqrcode/phpqrcode.php");
        header('Content-Type: image/png');
        ob_clean();
        \QRcode::png($url);
    }







    public function getEditBroken()
    {
        $id = I('get.id');
        if (!$id) {
            $this->error('缺少保险ID号');
        }

        $map = array('tio.id' => $id);

        $order = M('third_insurance_order')->join('tio left join `third_party_order` o on o.order_number = tio.old_order_number')
            ->field('tio.*, o.phone_name, o.phone_imei')->where($map)->find();
        if (!$order) {
            $this->error('查询不到保险单信息');
        }

        if ($order['audit_status'] < 1 || $order['status'] < 1 || $order['status'] > 3) {
            $this->error('当前保险单不能申请理赔');
        }

        if ($order['status'] == 3 && $order['broken_flag'] == 0) {
            $this->error('保险单理赔申请正在审核中，无法提交申请信息！');
        }

        if ($order['status'] == 3 && $order['broken_flag'] == 1) {
            $this->error('保险单理赔申请审核已通过，不需要重复申请！');
        }

        $time = time();

        if ($order['effect_time'] > $time)
        {
            $this->error('保险单还未生效，无法申请理赔！');
        }

        if ($time > $order['failure_time']) {
            $this->error('保险单已过期，无法申请理赔！');
        }

        $data = array();
        $data['id'] = $order['id'];
        $data['customer'] = $order['customer'];
        $data['cellphone'] = $order['cellphone'];
        $data['effect_time'] = date('Y-m-d', $order['effect_time']);
        $data['failure_time'] = date('Y-m-d', $order['failure_time']);
        $data['phone_name'] = $order['phone_name'];
        $data['phone_imei'] = $order['phone_imei'];
        $this->assign('item',$data);
        $this->display();
    }

    /**
     * 理赔
     */
    public function postBroken()
    {
        $id = I('post.id');
        
        if (!$id) {
            $this->error('缺少保险订单号');
        }
        $map = array('id' => $id);
        $order = M('third_insurance_order')->where($map)->find();
        if (!$order) {
            $this->error('查询不到保险单，无法理赔！');
        }

        if ($order['audit_status'] == 0) {
            $this->error('保险资料未审核');
        } else if ($order['audit_status'] == -1) {
            $this->error('保险审核未通过');
        } else if ($order['status'] < 0) {
            $this->error('保险已取消，无法理赔！');
        }  else if ($order['status'] == 1 && $order['effect_time'] > time()) {
            $this->error('保险单还未生效，无法申请理赔！');
        } else if ($order['status'] == 2 && $order['failure_time'] < time()) {
            $this->error('保险单已过期，无法申请理赔！');
        } else if ($order['status'] == 4) {
            $this->error('保险单已过期，无法申请理赔！');
        } else if ($order['status'] == 5) {
            $this->error('保险服务已完成，无法再次理赔！');
        } else if ($order['status'] == 3 && $order['broken_flag'] == 0) {
            $this->error('保险单理赔申请正在审核中，无法提交申请信息！');
        } else if ($order['status'] == 3 && $order['broken_flag'] == 1) {
            $this->error('保险单理赔申请审核已通过，不需要重复申请！');
        }

        $info = $this->upload();
        
        if (!$info['success']) {
            $this->error('请上传理赔所需图片',1006);
        }
        
        $data = array();
        $data['status'] = 3;
        $data['broken_time'] = time();
        $data['broken_flag'] = 0;
        $data['broken_comment'] = I('post.broken_comment');
        $data['broken_img'] = '/upload/' . $info['info']['file']['savepath'] . $info['info']['file']['savename'];
        
        if (M('third_insurance_order')->where($map)->save($data) !== false) {
            $result['status'] = 1;
            //通知值班员工
            $list = M('insurance_work')
                ->join('left join user on user.id = insurance_work.user_id')
                ->where(array('switch' => array('EQ', 1)))
                ->field('user.telphone')
                ->select();

            $phone = '';

            foreach ($list as $value) {
                $phone .= $value['telphone'].',';
            }

            $sms = new \Vendor\aliNote\aliNote();
            $rst = $sms->send($phone, array('name' => '第三方保险单'.$order['number']),'SMS_38385145');


        } else {
            $this->error('理赔申请提交出错，请刷新重试！',1000);
        }
        //$this->setMsg('理赔申请提交成功')->responseSuccess();
        $this->success('理赔申请提交成功',U('brokens',array('source'=>$this->source,'token'=>$this->token)));
    }

    /**
     * 第三方理赔保险订单列表
     */
    public function getCustomerThird()
    {
        $cellphone = I('get.cellphone');
        if(empty($cellphone)){
            $this->responseError('手机号不得为空',1001);
        }
        $model = M('third_insurance_order');
        $map = array('tio.cellphone'=>$cellphone,'audit_status'=>1);

        $join = "tio left join `third_party_order` on third_party_order.order_number = tio.old_order_number
                left join `third_party_order` as o2 on o2.order_number = tio.order_number
                ";
        $data = $model->join($join)
            ->field("tio.id,tio.broken_flag,tio.number,third_party_order.order_price as price,tio.status,tio.customer,tio.cellphone, third_party_order.phone_name,
                             third_party_order.color_name,tio.create_time
                            ")
            ->where($map)->order('tio.broken_time desc, tio.create_time desc')->select();
        $this->responseSuccess($data);
    }

    /**
     * 理赔页面接口
     */
    public function getBrokenInfo()
    {
        $number = I('get.number');
        if (!$number) {
            $this->responseError('缺少保险订单号',1001);
        }

        $map = array('tio.number' => $number);

        $order = M('third_insurance_order')->join('tio left join `third_party_order` o on o.order_number = tio.old_order_number')
            ->field('tio.*,o.phone_name, o.color_name,o.phone_imei')->where($map)->find();
        if (!$order) {
            $this->responseError('查询不到保险单信息',1022);
        }

        if ($order['audit_status'] < 1 || $order['status'] < 1 || $order['status'] > 3) {
            $this->responseError('当前保险单不能申请理赔',1057);
        }

        if ($order['status'] == 3 && $order['broken_flag'] == 0) {
            $this->responseError('保险单理赔申请正在审核中，无法提交申请信息！',1058);
        }

        if ($order['status'] == 3 && $order['broken_flag'] == 1) {
            $this->responseError('保险单理赔申请审核已通过，不需要重复申请！',1059);
        }

        $time = time();

        if ($order['effect_time'] > $time)
        {
            $this->responseError('保险单还未生效，无法申请理赔！',1060);
        }

        if ($time > $order['failure_time']) {
            $this->responseError('保险单已过期，无法申请理赔！',1061);
        }

        $data = array();
        $data['id'] = $order['id'];
        $data['number'] = $order['number'];
        $data['customer'] = $order['customer'];
        $data['cellphone'] = $order['cellphone'];
        $data['effect_time'] = date('Y-m-d', $order['effect_time']);
        $data['failure_time'] = date('Y-m-d', $order['failure_time']);
        $data['phone_name'] = $order['phone_name'];
        $data['color_name'] = $order['color_name'];
        $data['phone_imei'] = $order['phone_imei'];

        $this->responseSuccess($data);
    }


    /**
     * 理赔接口
     */
    public function postBrokenInfo()
    {
        $number = I('post.number');

        if (empty($number)) {
            $this->responseError('缺少保险订单号',1001);
        }
        $map = array('number' => $number);
        $order = M('third_insurance_order')->where($map)->find();
        if (!$order) {
            $this->responseError('查询不到保险单，无法理赔！',1022);
        }

        if ($order['audit_status'] == 0) {
            $this->responseError('保险资料未审核',1062);
        } else if ($order['audit_status'] == -1) {
            $this->responseError('保险审核未通过',1062);
        } else if ($order['status'] < 0) {
            $this->responseError('保险已取消，无法理赔！',1062);
        }  else if ($order['status'] == 1 && $order['effect_time'] > time()) {
            $this->responseError('保险单还未生效，无法申请理赔！',1060);
        } else if ($order['status'] == 2 && $order['failure_time'] < time()) {
            $this->responseError('保险单已过期，无法申请理赔！',1061);
        } else if ($order['status'] == 4) {
            $this->responseError('保险单已过期，无法申请理赔！',1061);
        } else if ($order['status'] == 5) {
            $this->responseError('保险服务已完成，无法再次理赔！',1064);
        } else if ($order['status'] == 3 && $order['broken_flag'] == 0) {
            $this->responseError('保险单理赔申请正在审核中，无法提交申请信息！',1058);
        } else if ($order['status'] == 3 && $order['broken_flag'] == 1) {
            $this->responseError('保险单理赔申请审核已通过，不需要重复申请！',1059);
        }

        $info = $this->upload();

        if (!$info['success']) {
            $this->responseError('请上传理赔所需图片',1006);
        }

        $data = array();
        $data['status'] = 3;
        $data['broken_time'] = time();
        $data['broken_flag'] = 0;
        //$data['broken_comment'] = I('post.broken_comment');
        $data['broken_img'] = '/upload/' . $info['info']['file']['savepath'] . $info['info']['file']['savename'];

        if (M('third_insurance_order')->where($map)->save($data) !== false) {
            $result['status'] = 1;
            //通知值班员工
            $list = M('insurance_work')
                ->join('left join user on user.id = insurance_work.user_id')
                ->where(array('switch' => array('EQ', 1)))
                ->field('user.telphone')
                ->select();

            $phone = '';

            foreach ($list as $value) {
                $phone .= $value['telphone'].',';
            }

            $sms = new \Vendor\aliNote\aliNote();
            $rst = $sms->send($phone, array('name' => '第三方保险单'.$order['number']),'SMS_38385145');


        } else {
            $this->responseError('理赔申请提交出错，请刷新重试！',1000);
        }
        //$this->setMsg('理赔申请提交成功')->responseSuccess();
        $this->responseSuccess('理赔申请提交成功');
    }


}