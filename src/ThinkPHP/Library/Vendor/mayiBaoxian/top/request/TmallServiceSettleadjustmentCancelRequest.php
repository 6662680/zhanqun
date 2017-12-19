<?php
/**
 * TOP API: tmall.service.settleadjustment.cancel request
 * 
 * @author auto create
 * @since 1.0, 2016.08.18
 */
class TmallServiceSettleadjustmentCancelRequest
{
	/** 
	 * 取消原因说明
	 **/
	private $comments;
	
	/** 
	 * 结算调整单ID
	 **/
	private $id;
	
	private $apiParas = array();
	
	public function setComments($comments)
	{
		$this->comments = $comments;
		$this->apiParas["comments"] = $comments;
	}

	public function getComments()
	{
		return $this->comments;
	}

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
		return "tmall.service.settleadjustment.cancel";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->comments,"comments");
		RequestCheckUtil::checkNotNull($this->id,"id");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
