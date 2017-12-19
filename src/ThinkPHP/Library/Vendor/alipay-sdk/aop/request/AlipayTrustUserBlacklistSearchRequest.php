<?php
/**
 * ALIPAY API: alipay.trust.user.blacklist.search request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:15:50
 */
class AlipayTrustUserBlacklistSearchRequest
{
	/** 
	 * 是否已经取得了用户的授权许可
	 **/
	private $authorized;
	
	/** 
	 * 用户完整身份证号
	 **/
	private $idCard;
	
	/** 
	 * 根据加*规则生成的加*后身份证号号
	 **/
	private $maskIdCard;
	
	/** 
	 * 根据加*规则生成的加*姓名
	 **/
	private $maskName;
	
	/** 
	 * 用户完整姓名
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

	public function setMaskIdCard($maskIdCard)
	{
		$this->maskIdCard = $maskIdCard;
		$this->apiParas["mask_id_card"] = $maskIdCard;
	}

	public function getMaskIdCard()
	{
		return $this->maskIdCard;
	}

	public function setMaskName($maskName)
	{
		$this->maskName = $maskName;
		$this->apiParas["mask_name"] = $maskName;
	}

	public function getMaskName()
	{
		return $this->maskName;
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
		return "alipay.trust.user.blacklist.search";
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
