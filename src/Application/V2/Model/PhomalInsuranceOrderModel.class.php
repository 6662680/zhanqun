<?php
/**
 * Created by PhpStorm.
 * User: zhujianping
 * Date: 2017/5/17 0017
 * Time: 上午 11:18
 */

namespace V2\Model;


use Think\Model;

class PhomalInsuranceOrderModel extends Model
{
    protected $tableName='phomal_insurance_order';

    /**
     * 查询保险订单
     * @param $where 条件
     */
    public function getInsuranceOrder($where)
    {
        $list = $this->join('pio left join phomal_insurance pi on pio.phomal_insurance_id = pi.id')
            ->join('left join `phone` p on p.id = pi.phone_id')
            ->join('left join `order` o on o.id = pio.old_order_id')
            ->field('pio.id, pio.number, pio.price, pio.cellphone, pio.status, pio.effect_time, pio.failure_time, 
                    pio.create_time, pio.old_order_id, p.alias as phone_name, p.img, pio.broken_flag, pio.remark, o.color as color_name')
            ->where($where)->order('pio.id desc')->select();
        return $list;
    }
}