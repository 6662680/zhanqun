<?php
/**
 * ALIPAY API: alipay.trust.aruser.risk.search request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:15:56
 */
class AlipayTrustAruserRiskSearchRequest
{
	/** 
	 * 是否已经取得了用户的授权许可
	 **/
	private $authorized;
	
	/** 
	 * 完整身份证号
	 **/
	private $idCard;
	
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

	public function setIdCard($idCard)
	{
		$this->idCard = $idCard;
		$this->apiParas["id_card"] = $idCard;
	}

	public function getIdCard()
	{
		return $this->idCard;
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
		return "alipay.trust.aruser.risk.search";
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
