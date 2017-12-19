<?php
/**
 * TOP API: tmall.service.settleadjustment.query request
 * 
 * @author auto create
 * @since 1.0, 2016.12.01
 */
class TmallServiceSettleadjustmentQueryRequest
{
	/** 
	 * 调整单ID
	 **/
	private $id;
	
	/** 
	 * 工单ID
	 **/
	private $sourceId;
	
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

	public function setSourceId($sourceId)
	{
		$this->sourceId = $sourceId;
		$this->apiParas["source_id"] = $sourceId;
	}

	public function getSourceId()
	{
		return $this->sourceId;
	}

	public function getApiMethodName()
	{
		return "tmall.service.settleadjustment.query";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->sourceId,"sourceId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
