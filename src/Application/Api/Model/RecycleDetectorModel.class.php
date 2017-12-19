<?php

// +------------------------------------------------------------------------------------------ 
// | Author: qikailin <qklandy@gmail.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2017/7/4
// +------------------------------------------------------------------------------------------
namespace Api\Model;
use Think\Model;

class RecycleDetectorModel extends Model {

    protected $_map = [
        'third_items' => 'items'
    ];


    public function saveEveryDetectResult($thirdRecycleId){

        $selects = explode("#", $this->selects);

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