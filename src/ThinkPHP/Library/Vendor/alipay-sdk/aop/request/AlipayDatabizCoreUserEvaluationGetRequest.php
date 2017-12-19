<?php
/**
 * ALIPAY API: alipay.databiz.core.user.evaluation.get request
 *
 * @author auto create
 * @since 1.0, 2014-10-20 11:12:12
 */
class AlipayDatabizCoreUserEvaluationGetRequest
{
	/** 
	 * 手机号码
	 **/
	private $phoneNo;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setPhoneNo($phoneNo)
	{
		$this->phoneNo = $phoneNo;
		$this->apiParas["phone_no"] = $phoneNo;
	}

	public function getPhoneNo()
	{
		return $this->phoneNo;
	}

	public function getApiMethodName()
	{
		return "alipay.databiz.core.user.evaluation.get";
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
