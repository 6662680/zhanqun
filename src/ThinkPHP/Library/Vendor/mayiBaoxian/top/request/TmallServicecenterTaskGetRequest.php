<?php
/**
 * TOP API: tmall.servicecenter.task.get request
 * 
 * @author auto create
 * @since 1.0, 2017.06.01
 */
class TmallServicecenterTaskGetRequest
{
	/** 
	 * Taobao主交易订单ID
	 **/
	private $parentBizOrderId;
	
	private $apiParas = array();
	
	public function setParentBizOrderId($parentBizOrderId)
	{
		$this->parentBizOrderId = $parentBizOrderId;
		$this->apiParas["parent_biz_order_id"] = $parentBizOrderId;
	}

	public function getParentBizOrderId()
	{
		return $this->parentBizOrderId;
	}

	public function getApiMethodName()
	{
		return "tmall.servicecenter.task.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->parentBizOrderId,"parentBizOrderId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
