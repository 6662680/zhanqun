<?php
/**
 * ALIPAY API: alipay.trust.gsdata.get request
 *
 * @author auto create
 * @since 1.0, 2014-10-28 23:28:42
 */
class AlipayTrustGsdataGetRequest
{
	/** 
	 * 企业名称全称
	 **/
	private $entName;
	
	/** 
	 * 自然人证件号码
	 **/
	private $idCard;
	
	/** 
	 * 是否强制先从本地查询
	 **/
	private $local;
	
	/** 
	 * 本地缓存数据有效时间。当所查询的数据在本地数据库中命中时，如果在有效期之内，则不再做远程查询。
	 **/
	private $qualifiedTime;
	
	/** 
	 * 企业执照号码
	 **/
	private $regNo;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setEntName($entName)
	{
		$this->entName = $entName;
		$this->apiParas["ent_name"] = $entName;
	}

	public function getEntName()
	{
		return $this->entName;
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

	public function setLocal($local)
	{
		$this->local = $local;
		$this->apiParas["local"] = $local;
	}

	public function getLocal()
	{
		return $this->local;
	}

	public function setQualifiedTime($qualifiedTime)
	{
		$this->qualifiedTime = $qualifiedTime;
		$this->apiParas["qualified_time"] = $qualifiedTime;
	}

	public function getQualifiedTime()
	{
		return $this->qualifiedTime;
	}

	public function setRegNo($regNo)
	{
		$this->regNo = $regNo;
		$this->apiParas["reg_no"] = $regNo;
	}

	public function getRegNo()
	{
		return $this->regNo;
	}

	public function getApiMethodName()
	{
		return "alipay.trust.gsdata.get";
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
