<?php
/**
 * TOP API: tmall.service.settleadjustment.modify request
 * 
 * @author auto create
 * @since 1.0, 2016.12.01
 */
class TmallServiceSettleadjustmentModifyRequest
{
	/** 
	 * 结算调整单父节点
	 **/
	private $paramSettleAdjustmentRequest;
	
	private $apiParas = array();
	
	public function setParamSettleAdjustmentRequest($paramSettleAdjustmentRequest)
	{
		$this->paramSettleAdjustmentRequest = $paramSettleAdjustmentRequest;
		$this->apiParas["param_settle_adjustment_request"] = $paramSettleAdjustmentRequest;
	}

	public function getParamSettleAdjustmentRequest()
	{
		return $this->paramSettleAdjustmentRequest;
	}

	public function getApiMethodName()
	{
		return "tmall.service.settleadjustment.modify";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
