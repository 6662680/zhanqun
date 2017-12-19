<?php
/**
 * ALIPAY API: alipay.evercall.contract.sign.update request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:39
 */
class AlipayEvercallContractSignUpdateRequest
{
	/** 
	 * 签约状态
	 **/
	private $code;
	
	/** 
	 * 扩展属性
	 **/
	private $extendField;
	
	/** 
	 * 签约状态信息描述
	 **/
	private $message;
	
	/** 
	 * 签约手机号
	 **/
	private $mobileNo;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setCode($code)
	{
		$this->code = $code;
		$this->apiParas["code"] = $code;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function setExtendField($extendField)
	{
		$this->extendField = $extendField;
		$this->apiParas["extend_field"] = $extendField;
	}

	public function getExtendField()
	{
		return $this->extendField;
	}

	public function setMessage($message)
	{
		$this->message = $message;
		$this->apiParas["message"] = $message;
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function setMobileNo($mobileNo)
	{
		$this->mobileNo = $mobileNo;
		$this->apiParas["mobile_no"] = $mobileNo;
	}

	public function getMobileNo()
	{
		return $this->mobileNo;
	}

	public function getApiMethodName()
	{
		return "alipay.evercall.contract.sign.update";
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
