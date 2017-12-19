<?php
/**
 * ALIPAY API: alipay.merchant.ticket.apply request
 *
 * @author auto create
 * @since 1.0, 2016-01-14 17:47:22
 */
class AlipayMerchantTicketApplyRequest
{
	/** 
	 * 业务上下文
	 **/
	private $bizContext;
	
	/** 
	 * 业务发生时间，外围传入，可以作为T+1核对，如果不填写，则该时间为业务生成时间
	 **/
	private $bizDate;
	
	/** 
	 * 业务号，用于控制幂等。
	 **/
	private $bizNo;
	
	/** 
	 * 扩展字段，json格式
	 **/
	private $extInfo;
	
	/** 
	 * 操作人id
	 **/
	private $optId;
	
	/** 
	 * 发券商户parnterId
	 **/
	private $partnerId;
	
	/** 
	 * 券模板编号
	 **/
	private $templateNo;
	
	/** 
	 * 个人用户Id
	 **/
	private $userId;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setBizContext($bizContext)
	{
		$this->bizContext = $bizContext;
		$this->apiParas["biz_context"] = $bizContext;
	}

	public function getBizContext()
	{
		return $this->bizContext;
	}

	public function setBizDate($bizDate)
	{
		$this->bizDate = $bizDate;
		$this->apiParas["biz_date"] = $bizDate;
	}

	public function getBizDate()
	{
		return $this->bizDate;
	}

	public function setBizNo($bizNo)
	{
		$this->bizNo = $bizNo;
		$this->apiParas["biz_no"] = $bizNo;
	}

	public function getBizNo()
	{
		return $this->bizNo;
	}

	public function setExtInfo($extInfo)
	{
		$this->extInfo = $extInfo;
		$this->apiParas["ext_info"] = $extInfo;
	}

	public function getExtInfo()
	{
		return $this->extInfo;
	}

	public function setOptId($optId)
	{
		$this->optId = $optId;
		$this->apiParas["opt_id"] = $optId;
	}

	public function getOptId()
	{
		return $this->optId;
	}

	public function setPartnerId($partnerId)
	{
		$this->partnerId = $partnerId;
		$this->apiParas["partner_id"] = $partnerId;
	}

	public function getPartnerId()
	{
		return $this->partnerId;
	}

	public function setTemplateNo($templateNo)
	{
		$this->templateNo = $templateNo;
		$this->apiParas["template_no"] = $templateNo;
	}

	public function getTemplateNo()
	{
		return $this->templateNo;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		$this->apiParas["user_id"] = $userId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getApiMethodName()
	{
		return "alipay.merchant.ticket.apply";
	}

	public function setNotifyUrl($notifyUrl)
	{
		$this->notifyUrl=$notifyUrl;
	}

	public function getNotifyUrl()
	{
		return $this->notifyUrl;
	}

	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getTerminalType()
	{
		return $this->terminalType;
	}

	public function setTerminalType($terminalType)
	{
		$this->terminalType = $terminalType;
	}

	public function getTerminalInfo()
	{
		return $this->terminalInfo;
	}

	public function setTerminalInfo($terminalInfo)
	{
		$this->terminalInfo = $terminalInfo;
	}

	public function getProdCode()
	{
		return $this->prodCode;
	}

	public function setProdCode($prodCode)
	{
		$this->prodCode = $prodCode;
	}

	public function setApiVersion($apiVersion)
	{
		$this->apiVersion=$apiVersion;
	}

	public function getApiVersion()
	{
		return $this->apiVersion;
	}

}
