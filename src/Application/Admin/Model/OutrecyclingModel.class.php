<?php

namespace Admin\Model;

use Think\Model\RelationModel;
class OutRecyclingModel extends RelationModel
{
    /**
     * 统计仓库配件库存
     * @param fitting_id 配件ID
     * @param org_id  仓库ID
     * @param amount  数量
     * @return id
     */
    public function countStock($fitting_id, $org_id, $amount)
    {
        $map['fitting_id'] = $fitting_id;
        $map['organization_id'] = $org_id;
        $map['status'] = 1;
        $rst = M('stock')->where($map)->field('id')->limit($amount)->select();

        if (count($rst) < $amount) {
            return false;
        } else {
            return $rst;
        }
    }
}