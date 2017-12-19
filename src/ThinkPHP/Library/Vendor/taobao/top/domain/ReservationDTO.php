<?php

/**
 * 预约内容
 * @author auto create
 */
class ReservationDTO
{
	
	/** 
	 * 天猫订单号列表
	 **/
	public $order_ids;
	
	/** 
	 * 内部订单号
	 **/
	public $outer_id;
	
	/** 
	 * 预约日期,2015-12-15
	 **/
	public $resv_date;
	
	/** 
	 * 预约时间,0:上午,1:下午,2:晚上
	 **/
	public $resv_time;
	
	/** 
	 * 服务类型，0 家装的送货上门并安装 1 单向安装 2 建材的送货上门 3 建材的安装
	 **/
	public $service_type;
	
	/** 
	 * 预约工人手机号码
	 **/
	public $worker_mobile;
	
	/** 
	 * 工人名称
	 **/
	public $worker_name;	
}
?>