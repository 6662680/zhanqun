<?php
/**
 * TOP API: tmall.service.settleadjustment.get request
 * 
 * @author auto create
 * @since 1.0, 2016.08.25
 */
class TmallServiceSettleadjustmentGetRequest
{
	/** 
	 * 结算调整单ID
	 **/
	private $id;
	
	private $apiParas = array();
	
	public function setId($id)
	{
		$this->id = $id;
		$this->apiParas["id"] = $id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getApiMethodName()
	{
		return "tmall.service.settleadjustment.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->id,"id");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
