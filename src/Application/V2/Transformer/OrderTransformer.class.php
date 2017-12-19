<?php
/**
 * Created by PhpStorm.
 * User: zhujianping
 * Date: 2017/5/12 0012
 * Time: 上午 11:51
 * 订单响应转化器
 */

namespace V2\Transformer;
use V2\Transformer\Transformer;

class OrderTransformer extends Transformer
{
    public function transform($item)
    {
        $data['orderId'] = $item['orderId'];
        $data['orderNumber'] = $item['number'];
        pushNoteQueue($data);
        return $data;
    }


    public function makeInsurance($list)
    {
        $insurance=[];
        $baseUrl = C('baseUrl');
        $pio_status =C('PIO_STATUS');
        $pio_color =C('PIO_COLOR');
        $time = time();
        foreach ($list as $item) {

            $item['create_time'] = date('Y-m-d', $item['create_time']);
            $item['time'] = date('Y.m.d', $item['effect_time']) . ' - ' . date('Y.m.d', $item['failure_time']); //保险时间
            $item['i_status'] = $pio_status[$item['status']];
            $item['i_color'] = $pio_color[$item['status']];
            $item['img'] = $baseUrl . '/' . $item['img'];
            $item['is_insurance'] = 1;
            if ($item['status'] == 0) { //未付款
                $item['pay_url'] = $baseUrl . U("api/pay/handle?id={$item['id']}&number={$item['number']}&type=I");
                $item['pay_img'] = $baseUrl . U('Api/pay/qrcode') . '?url=' . urlencode($item['pay_url']);
                $item['weixin_img'] = $baseUrl . U("api/weixinpay/handle?id={$item['id']}&number={$item['number']}&type=I&show_type=1");
            } else if ($item['status'] == 3) { //出险

                if ($item['broken_flag'] == 1) {
                    $item['i_flag'] = '审核通过';
                } else if ($item['broken_flag'] == -1) {
                    $item['i_flag'] = '审核不通过';
                } else {
                    $item['i_flag'] = '审核中';
                }
            }
            //是否可以理赔
            $item['claims'] = 0;
            if (in_array($item['status'], array(1, 2)) || ($item['status'] == 3 && $item['broken_flag'] < 1)) {

                if ($item['effect_time'] <= $time && $time <= $item['failure_time']) {
                    $item['claims'] = 1;
                }
            }
            $insurance[$item['old_order_id']] = $item;
        }
        return $insurance;
    }

    public function makeOrder($list,$insurance)
    {
        $baseUrl = C('baseUrl');
        $status =C('ORDER_STATUS');
        $color =C('ORDER_COLOR');
        foreach ($list as $k => $val) {
            $val['engineer_phone'] = $val['engineer_phone'] ? $val['engineer_phone'] : '';
            $val['create_time'] = date('Y-m-d', $val['create_time']);
            $val['color'] = isset($color[$val['status']]) ? $color[$val['status']] : '';
            $val['status'] = $status[$val['status']];
            $val['img'] = $baseUrl . '/' . $val['img'];
            $val['is_insurance'] = 0;
            $data[] = $val;
            if (isset($insurance[$val['id']]) && $insurance[$val['id']]) {
                $data[] = $insurance[$val['id']];
            }
        }
        return $data;
    }



}