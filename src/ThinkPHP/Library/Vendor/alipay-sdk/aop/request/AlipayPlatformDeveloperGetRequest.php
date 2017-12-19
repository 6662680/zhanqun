<?php
/**
 * ALIPAY API: alipay.platform.developer.get request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:01
 */
class AlipayPlatformDeveloperGetRequest
{
	/** 
	 * ISV支付宝ID
	 **/
	private $alipayUserId;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setAlipayUserId($alipayUserId)
	{
		$this->alipayUserId = $alipayUserId;
		$this->apiParas["alipay_user_id"] = $alipayUserId;
	}

	public function getAlipayUserId()
	{
		return $this->alipayUserId;
	}

	public function getApiMethodName()
	{
		return "alipay.platform.developer.get";
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
