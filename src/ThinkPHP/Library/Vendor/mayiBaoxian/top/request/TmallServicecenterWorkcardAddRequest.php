<?php
/**
 * TOP API: tmall.servicecenter.workcard.add request
 * 
 * @author auto create
 * @since 1.0, 2015.10.02
 */
class TmallServicecenterWorkcardAddRequest
{
	/** 
	 * 报修人地址，默认从订单信息获取
	 **/
	private $applyAddress;
	
	/** 
	 * 申请日期
	 **/
	private $applyDate;
	
	/** 
	 * 报修人联系电话，默认从订单信息获取
	 **/
	private $applyMobile;
	
	/** 
	 * 报修人姓名，默认从订单信息获取
	 **/
	private $applyName;
	
	/** 
	 * Taobao买家Nick
	 **/
	private $buyerNick;
	
	/** 
	 * 备注
	 **/
	private $comments;
	
	/** 
	 * 期望日期
	 **/
	private $expectDate;
	
	/** 
	 * Taobao交易主订单id
	 **/
	private $parentBizOrderId;
	
	/** 
	 * Taobao服务子订单id
	 **/
	private $serviceOrderId;
	
	private $apiParas = array();
	
	public function setApplyAddress($applyAddress)
	{
		$this->applyAddress = $applyAddress;
		$this->apiParas["apply_address"] = $applyAddress;
	}

	public function getApplyAddress()
	{
		return $this->applyAddress;
	}

	public function setApplyDate($applyDate)
	{
		$this->applyDate = $applyDate;
		$this->apiParas["apply_date"] = $applyDate;
	}

	public function getApplyDate()
	{
		return $this->applyDate;
	}

	public function setApplyMobile($applyMobile)
	{
		$this->applyMobile = $applyMobile;
		$this->apiParas["apply_mobile"] = $applyMobile;
	}

	public function getApplyMobile()
	{
		return $this->applyMobile;
	}

	public function setApplyName($applyName)
	{
		$this->applyName = $applyName;
		$this->apiParas["apply_name"] = $applyName;
	}

	public function getApplyName()
	{
		return $this->applyName;
	}

	public function setBuyerNick($buyerNick)
	{
		$this->buyerNick = $buyerNick;
		$this->apiParas["buyer_nick"] = $buyerNick;
	}

	public function getBuyerNick()
	{
		return $this->buyerNick;
	}

	public function setComments($comments)
	{
		$this->comments = $comments;
		$this->apiParas["comments"] = $comments;
	}

	public function getComments()
	{
		return $this->comments;
	}

	public function setExpectDate($expectDate)
	{
		$this->expectDate = $expectDate;
		$this->apiParas["expect_date"] = $expectDate;
	}

	public function getExpectDate()
	{
		return $this->expectDate;
	}

	public function setParentBizOrderId($parentBizOrderId)
	{
		$this->parentBizOrderId = $parentBizOrderId;
		$this->apiParas["parent_biz_order_id"] = $parentBizOrderId;
	}

	public function getParentBizOrderId()
	{
		return $this->parentBizOrderId;
	}

	public function setServiceOrderId($serviceOrderId)
	{
		$this->serviceOrderId = $serviceOrderId;
		$this->apiParas["service_order_id"] = $serviceOrderId;
	}

	public function getServiceOrderId()
	{
		return $this->serviceOrderId;
	}

	public function getApiMethodName()
	{
		return "tmall.servicecenter.workcard.add";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->applyDate,"applyDate");
		RequestCheckUtil::checkNotNull($this->buyerNick,"buyerNick");
		RequestCheckUtil::checkNotNull($this->expectDate,"expectDate");
		RequestCheckUtil::checkNotNull($this->parentBizOrderId,"parentBizOrderId");
		RequestCheckUtil::checkNotNull($this->serviceOrderId,"serviceOrderId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
