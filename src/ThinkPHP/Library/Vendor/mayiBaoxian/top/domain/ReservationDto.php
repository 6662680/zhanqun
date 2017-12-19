<?php

/**
 * 派单参数
 * @author auto create
 */
class ReservationDto
{
	
	/** 
	 * 天猫订单号，如果是多个中间用英文逗号分隔：1313123213113,132131232333
	 **/
	public $order_ids;
	
	/** 
	 * 唯一性，一个卖家下面保证唯一就好
	 **/
	public $outer_id;
	
	/** 
	 * 服务类型，家装的送货安装一体的：传入 0； 旗舰店的安装： 传入1； 建材的送货入户： 传入2； 建材的安装： 传入3；
	 **/
	public $service_type;
	
	/** 
	 * 工人的手机号
	 **/
	public $worker_mobile;
	
	/** 
	 * 工人名字
	 **/
	public $worker_name;	
}
?>