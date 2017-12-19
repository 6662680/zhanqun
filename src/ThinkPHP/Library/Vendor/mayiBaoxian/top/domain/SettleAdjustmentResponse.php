<?php

/**
 * dataModule
 * @author auto create
 */
class SettleAdjustmentResponse
{
	
	/** 
	 * comments
	 **/
	public $comments;
	
	/** 
	 * cost，单位分
	 **/
	public $cost;
	
	/** 
	 * gmtCreate
	 **/
	public $create_time;
	
	/** 
	 * description
	 **/
	public $description;
	
	/** 
	 * id
	 **/
	public $id;
	
	/** 
	 * gmtModified
	 **/
	public $modified_time;
	
	/** 
	 * pictureUrls，多条已冒号分隔
	 **/
	public $picture_urls;
	
	/** 
	 * priceFactors
	 **/
	public $price_factors;
	
	/** 
	 * serviceOrderId
	 **/
	public $service_order_id;
	
	/** 
	 * 调整单状态 待商家确认:1, 商家已确认:2,  待小二判定:3,  小二判定有效:4,  小二判定无效:5,  小二无法判定:6, 服务商取消:7, 超时确认:8, 完成:9
	 **/
	public $status;
	
	/** 
	 * 调整单类型
	 **/
	public $type;
	
	/** 
	 * 工单ID
	 **/
	public $workcard_id;	
}
?>