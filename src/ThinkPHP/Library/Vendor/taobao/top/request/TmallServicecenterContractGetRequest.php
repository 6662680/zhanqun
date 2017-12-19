<?php
/**
 * TOP API: tmall.servicecenter.contract.get request
 * 
 * @author auto create
 * @since 1.0, 2016.03.16
 */
class TmallServicecenterContractGetRequest
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
		return "tmall.servicecenter.contract.get";
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
