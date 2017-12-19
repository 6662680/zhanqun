<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 数据统计 Dates: 2016-11-23
// +------------------------------------------------------------------------------------------ 

namespace Cli\Controller;

use Think\Controller;

class CountController extends Controller
{
    /**
     * 执行数据迁移
     *
     * @return void
     */
    public function run()
    {
        $count = 0;

        $map = array();
        $map['status'] = 0;
        $items = M('waste_refund')->where($map)->select();

        $wastes = M('waste')->getField('id, price');

        foreach ($items as $key => $value) {
            $tmp = json_decode($value['wastes'], true);
            $item = $tmp[0];

            if (isset($wastes[$item['waste_id']])) {
                $worth = $item['amount'] * $wastes[$item['waste_id']];
                $count += $worth;

                echo '机型：' . $item['phone'] . '废件： ' . $item['name'] . '数量： ' . $item['amount'] . '价格：' . $wastes[$item['waste_id']] . '<br/>';
            }
        }

        echo '总价：' . $count;
    }
}