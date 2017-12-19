<?php

// +------------------------------------------------------------------------------------------ 
// | Author: qishanshan <qishanshan@weadoc.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2017/6/29
// +------------------------------------------------------------------------------------------
namespace Api\Model;
use Think\Model;

class ThirdUserModel extends Model {

    public function hasThirdUser($thirdId){

        return $this->find($thirdId);
    }


}