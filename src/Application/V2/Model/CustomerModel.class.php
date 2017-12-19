<?php
/**
 * Created by PhpStorm.
 * User: zhujianping
 * Date: 2017/5/16 0016
 * Time: 上午 9:45
 */

namespace V2\Model;


use Think\Model;

class CustomerModel extends Model
{
    /**
     * 添加用户信息到custormer
     * @param data
     * @return Boolean
     */
    public function addCustomer($data)
    {
        $data = [
            'create_time'=>time(),
            'name'=>$data['name'],
            'address'=>$data['address'],
            'cellphone'=>$data['mobile']
        ];
        if($customer_id = $this->add($data)){
            return $customer_id;
        } else {
            return false;
        }
    }
}