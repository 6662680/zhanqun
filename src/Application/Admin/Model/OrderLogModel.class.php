<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 订单日志 Dates: 2015-07-22
// +------------------------------------------------------------------------------------------

namespace Admin\Model;

use Think\Model;

class OrderLogModel extends Model
{
	public $table = "order_log";

	public function add($data)
	{
		return M($this->table)->add($data);
	}
}
