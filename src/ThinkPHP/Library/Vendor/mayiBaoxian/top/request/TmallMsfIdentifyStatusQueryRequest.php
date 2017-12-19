<?php
/**
 * TOP API: tmall.msf.identify.status.query request
 * 
 * @author auto create
 * @since 1.0, 2016.12.06
 */
class TmallMsfIdentifyStatusQueryRequest
{
	/** 
	 * 天猫订单号
	 **/
	private $orderId;
	
	/** 
	 * 服务类型，0 家装的送货上门并安装 1 单向安装 2 建材的送货上门 3 建材的安装
	 **/
	private $serviceType;
	
	private $apiParas = array();
	
	public function setOrderId($orderId)
	{
		$this->orderId = $orderId;
		$this->apiParas["order_id"] = $orderId;
	}

	public function getOrderId()
	{
		return $this->orderId;
	}

	public function setServiceType($serviceType)
	{
		$this->serviceType = $serviceType;
		$this->apiParas["service_type"] = $serviceType;
	}

	public function getServiceType()
	{
		return $this->serviceType;
	}

	public function getApiMethodName()
	{
		return "tmall.msf.identify.status.query";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->orderId,"orderId");
		RequestCheckUtil::checkNotNull($this->serviceType,"serviceType");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
