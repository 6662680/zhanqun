<?php

// +------------------------------------------------------------------------------------------ 
// | Author: qikailin <qklandy@gmail.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2017/6/30
// +------------------------------------------------------------------------------------------
namespace Api\Model;
use Think\Model;

class ThirdRecycleModel extends Model {

    public function saveEveryDetectResult($thirdRecycleId){

        $selects = explode("#", I('post.third_items'));

        $sql = "INSERT INTO " . $this->tablePrefix
                              . "third_recycle_detector_detail(third_recycle_id, item) VALUES";

        $insertStr = '';
        foreach($selects as $val) {
            $insertStr .= ",({$thirdRecycleId}, {$val})";
        }

        $sql .= substr($insertStr, 1);

        return $this->execute($sql);
    }

}