<?php

// +------------------------------------------------------------------------------------------ 
// | Author: qikailin <qklandy@gmail.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2017/7/5
// +------------------------------------------------------------------------------------------
namespace Common\Model;

use Think\Model;

class BaseModel extends Model
{

    /**
     * 获取本表的单条记录
     * @param $map     array|string
     * @param $fields  可默认tp的filed字段里类型，array|string
     * @return mixed   获取到数据直接返回 或 NULL
     */
    public function getOne($map, $fields)
    {
        if(!$fields) {
            return $this->where($map)->find();
        }

        return $this->field($fields)->where($map)->find();

    }
}