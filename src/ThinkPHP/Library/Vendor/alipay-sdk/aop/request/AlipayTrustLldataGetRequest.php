<?php
/**
 * ALIPAY API: alipay.trust.lldata.get request
 *
 * @author auto create
 * @since 1.0, 2014-10-30 23:12:18
 */
class AlipayTrustLldataGetRequest
{
	/** 
	 * 是否强制先从本地查询
	 **/
	private $local;
	
	/** 
	 * 本地缓存数据有效时间。当所查询的数据在本地数据库中命中时，如果在有效期之内，则不再做远程查询。
	 **/
	private $qualifiedTime;
	
	/** 
	 * 用户列表JSON串，至少1个，最多200个。其中certNo为身份证，name为姓名
	 **/
	private $users;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
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

	public function setUsers($users)
	{
		$this->users = $users;
		$this->apiParas["users"] = $users;
	}

	public function getUsers()
	{
		return $this->users;
	}

	public function getApiMethodName()
	{
		return "alipay.trust.lldata.get";
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
