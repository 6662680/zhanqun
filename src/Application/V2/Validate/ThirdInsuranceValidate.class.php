<?php
/**
 * Created by PhpStorm.
 * User: zhujianping
 * Date: 2017/5/15 0015
 * Time: 下午 16:07
 */

namespace V2\Validate;
use V2\Model\CheckModel;

class ThirdInsuranceValidate extends Validate
{
    /** 错误信息 */
    public $errorInfo = array();
    /** 需要验证的数据 */
    public $data = array();
    /** 验证规则 */
    public $rules = array('cellphone'=>'客户手机', 'customer'=>'客户名称', 'phone_imei'=>'imei号','phone_name'=>'手机型号','color_name'=>'手机颜色');

    /**
     * 验证
     *
     * @return void
     */
    public function verification($data)
    {
        $this->data = $data;
        foreach ($this->rules as $rule=>$msg) {

            if (empty($data[$rule])) {
                $this->errorInfo['msg'] = '[' . $msg . ']不能为空';
                $this->errorInfo['code']=1004;
                return false;
            }

            if (method_exists($this,$rule) && !$this->$rule($data[$rule])) {
                return false;
            }
        }

        return true;
    }

    public function verificationOther()
    {
        /*if (M('third_insurance_order')->where(array('source'=>I('post.source'),'old_order_number' => I('post.order_number')))->count()) {
            $this->errorInfo['msg'] = '您已购买过保险服务了！';
            $this->errorInfo['code']=1055;
            return false;
        }

        $insurance = M('phomal_insurance')->where(array('id' => I('post.piId'), 'status' => 1))->find();

        if (!$insurance) {
            $this->errorInfo['msg'] = '没有对应的保险服务！';
            $this->errorInfo['code']=1050;
            return false;
        }*/
        return true;
    }

    /**
     * 姓名
     *
     * @return void
     */
    private function customer($name)
    {
        if (parent::regexp('name', $name)) {
            return true;
        } else {
            $this->errorInfo['code']=1005;
            $this->errorInfo['msg'] = '请输入2~16位字母数字中文';
            return false;
        }
    }

    /**
     * 手机号码
     *
     * @return void
     */
    private function cellphone($mobile)
    {
        if (parent::regexp('mobile', $mobile)) {
            return true;
        } else {
            $this->errorInfo['code']=1005;
            $this->errorInfo['msg'] = '手机号格式错误';
            return false;
        }
    }

}