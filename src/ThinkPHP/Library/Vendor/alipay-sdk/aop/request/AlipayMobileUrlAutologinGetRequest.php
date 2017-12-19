<?php
/**
 * ALIPAY API: alipay.mobile.url.autologin.get request
 *
 * @author auto create
 * @since 1.0, 2014-03-20 17:20:06
 */
class AlipayMobileUrlAutologinGetRequest
{
	/** 
	 * 淘宝跳转目的地址
	 **/
	private $redirectUrl;
	
	/** 
	 * 支付宝token
	 **/
	private $token;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setRedirectUrl($redirectUrl)
	{
		$this->redirectUrl = $redirectUrl;
		$this->apiParas["redirect_url"] = $redirectUrl;
	}

	public function getRedirectUrl()
	{
		return $this->redirectUrl;
	}

	public function setToken($token)
	{
		$this->token = $token;
		$this->apiParas["token"] = $token;
	}

	public function getToken()
	{
		return $this->token;
	}

	public function getApiMethodName()
	{
		return "alipay.mobile.url.autologin.get";
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
