<?php
/**
 * TOP API: tmall.servicecenter.workcard.push request
 * 
 * @author auto create
 * @since 1.0, 2016.12.06
 */
class TmallServicecenterWorkcardPushRequest
{
	/** 
	 * 属性列表。使用半角分号隔开,字符串前后都需要有半角分号
	 **/
	private $attributes;
	
	/** 
	 * 淘宝交易订单号
	 **/
	private $bizOrderId;
	
	/** 
	 * 描述
	 **/
	private $desc;
	
	/** 
	 * 服务预约安装地址。四级地址与街道地址用空格隔开
	 **/
	private $serviceReserveAddress;
	
	/** 
	 * 服务预约安装时间
	 **/
	private $serviceReserveTime;
	
	/** 
	 * 0=初始化, 3=授理， 10=拒绝 ，4=执行 ，5=成功，11=失败
	 **/
	private $status;
	
	private $apiParas = array();
	
	public function setAttributes($attributes)
	{
		$this->attributes = $attributes;
		$this->apiParas["attributes"] = $attributes;
	}

	public function getAttributes()
	{
		return $this->attributes;
	}

	public function setBizOrderId($bizOrderId)
	{
		$this->bizOrderId = $bizOrderId;
		$this->apiParas["biz_order_id"] = $bizOrderId;
	}

	public function getBizOrderId()
	{
		return $this->bizOrderId;
	}

	public function setDesc($desc)
	{
		$this->desc = $desc;
		$this->apiParas["desc"] = $desc;
	}

	public function getDesc()
	{
		return $this->desc;
	}

	public function setServiceReserveAddress($serviceReserveAddress)
	{
		$this->serviceReserveAddress = $serviceReserveAddress;
		$this->apiParas["service_reserve_address"] = $serviceReserveAddress;
	}

	public function getServiceReserveAddress()
	{
		return $this->serviceReserveAddress;
	}

	public function setServiceReserveTime($serviceReserveTime)
	{
		$this->serviceReserveTime = $serviceReserveTime;
		$this->apiParas["service_reserve_time"] = $serviceReserveTime;
	}

	public function getServiceReserveTime()
	{
		return $this->serviceReserveTime;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		$this->apiParas["status"] = $status;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getApiMethodName()
	{
		return "tmall.servicecenter.workcard.push";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->bizOrderId,"bizOrderId");
		RequestCheckUtil::checkNotNull($this->status,"status");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
