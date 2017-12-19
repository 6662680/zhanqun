<?php
/**
 * ALIPAY API: alipay.evercall.contract.cancel request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:40
 */
class AlipayEvercallContractCancelRequest
{
	/** 
	 * 签约手机号
	 **/
	private $mobileNo;
	
	/** 
	 * 解约渠道(SMS：短信方式 CLIENT：客户端 WAP：wap SITE：主站 OPENPLAT:开放平台 OTHER：其他)
	 **/
	private $unsignChannel;
	
	/** 
	 * 运营统计：taobao,alipay,telecom
	 **/
	private $unsignFrom;
	
	/** 
	 * 支付宝账户号
	 **/
	private $userId;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setMobileNo($mobileNo)
	{
		$this->mobileNo = $mobileNo;
		$this->apiParas["mobile_no"] = $mobileNo;
	}

	public function getMobileNo()
	{
		return $this->mobileNo;
	}

	public function setUnsignChannel($unsignChannel)
	{
		$this->unsignChannel = $unsignChannel;
		$this->apiParas["unsign_channel"] = $unsignChannel;
	}

	public function getUnsignChannel()
	{
		return $this->unsignChannel;
	}

	public function setUnsignFrom($unsignFrom)
	{
		$this->unsignFrom = $unsignFrom;
		$this->apiParas["unsign_from"] = $unsignFrom;
	}

	public function getUnsignFrom()
	{
		return $this->unsignFrom;
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
		return "alipay.evercall.contract.cancel";
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
