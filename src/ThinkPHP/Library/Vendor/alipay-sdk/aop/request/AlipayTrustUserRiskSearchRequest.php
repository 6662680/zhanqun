<?php
/**
 * ALIPAY API: alipay.trust.user.risk.search request
 *
 * @author auto create
 * @since 1.0, 2014-03-05 15:42:34
 */
class AlipayTrustUserRiskSearchRequest
{
	/** 
	 * 是否获得被查用户授权标识
	 **/
	private $authorized;
	
	/** 
	 * (完整身份证号+完整姓名)的md5值
	 **/
	private $idCardNameMd5;
	
	/** 
	 * 用户的完整姓名
	 **/
	private $name;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setAuthorized($authorized)
	{
		$this->authorized = $authorized;
		$this->apiParas["authorized"] = $authorized;
	}

	public function getAuthorized()
	{
		return $this->authorized;
	}

	public function setIdCardNameMd5($idCardNameMd5)
	{
		$this->idCardNameMd5 = $idCardNameMd5;
		$this->apiParas["id_card_name_md5"] = $idCardNameMd5;
	}

	public function getIdCardNameMd5()
	{
		return $this->idCardNameMd5;
	}

	public function setName($name)
	{
		$this->name = $name;
		$this->apiParas["name"] = $name;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getApiMethodName()
	{
		return "alipay.trust.user.risk.search";
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
