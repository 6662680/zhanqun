<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 订单验证 Dates: 2016-11-10
// +------------------------------------------------------------------------------------------ 

namespace Api\Model;

class OrderVerificationModel
{
    /** 错误信息 */
    public $errorInfo = array();
    /** 需要验证的数据 */
    public $data = array();
    /** 验证规则 */
    public $rules = array('name', 'mobile', 'phone_id');

    /**
     * 验证
     *
     * @return void
     */
    public function verification($data)
    {
        $this->data = $data;

        foreach ($this->rules as $rule) {

            if (empty($data[$rule])) {
                $this->errorInfo['msg'] = '[' . $rule . ']不能为空';
                return false;
            }

            if (!$this->$rule($data[$rule])) {
                return false;
            }
        }

        return true;
    }

    /**
     * 姓名
     *
     * @return void
     */
    private function name($name)
    {
        if (CheckModel::regexp('name', $name)) {
            return true;
        } else {
            $this->errorInfo['msg'] = '请输入2~16位字母数字中文';
            return false;
        }
    }

    /**
     * 手机号码
     *
     * @return void
     */
    private function mobile($mobile)
    {
        if (CheckModel::regexp('mobile', $mobile)) {
            return true;
        } else {
            $this->errorInfo['msg'] = '手机号格式错误';
            return false;
        }
    }

    /**
     * 验证码(短信)
     *
     * @return void
     */
    private function code($code)
    {
        $key = 'code' . $this->data['mobile'];

        if (S($key) == $this->data['code']) {
           S($key, NULL);
           return ture;
        } else {
            $this->errorInfo['mobile'] = '短信验证码错误';
            return false;
        }
    }

    /**
     * 地址
     *
     * @return void
     */
    private function detailed_address($address)
    {
        if (CheckModel::regexp('address', $address)) {
            return true;
        } else {
            $this->errorInfo['msg'] = '请输入字母数字中文2~32位';
            return false;
        }

    }

    /**
     * 上门时间
     *
     * @return void
     */
    private function date($date)
    {
        if (CheckModel::regexp('int', $date)) {
            return true;
        } else {
            $this->errorInfo['msg'] = 'date必须为时间戳';
            return false;
        }
    }

    /**
     * 机型ID
     *
     * @return void
     */
    private function phone_id($phone_id)
    {
        $map = array();
        $map['id'] = $phone_id;

        if (M('phone')->where($map)->find()) {
            return true;
        } else {
            $this->errorInfo['msg'] = 'phone_id错误,对应机型不存在';
            return false;
        }
    }

    /**
     * 颜色
     *
     * @return void
     */
    private function color_id($color_id)
    {
        $map = array();
        $map['id'] = $color_id;

        if (M('goods_color')->where($map)->find()) {
           return true;
        } else {
            $this->errorInfo['color_id'] = '颜色ID错误,对应颜色不存在';
            return false;
        }
    }

}
