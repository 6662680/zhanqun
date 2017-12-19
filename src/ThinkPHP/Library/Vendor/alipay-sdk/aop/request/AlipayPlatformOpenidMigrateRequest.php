<?php
/**
 * ALIPAY API: alipay.platform.openid.migrate request
 *
 * @author auto create
 * @since 1.0, 2015-04-22 11:01:03
 */
class AlipayPlatformOpenidMigrateRequest
{
	/** 
	 * 老OpenId，多个以英文逗号,分隔；
最大30个；
	 **/
	private $openids;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setOpenids($openids)
	{
		$this->openids = $openids;
		$this->apiParas["openids"] = $openids;
	}

	public function getOpenids()
	{
		return $this->openids;
	}

	public function getApiMethodName()
	{
		return "alipay.platform.openid.migrate";
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
