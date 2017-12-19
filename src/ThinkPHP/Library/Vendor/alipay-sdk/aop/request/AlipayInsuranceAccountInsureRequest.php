<?php
/**
 * ALIPAY API: alipay.insurance.account.insure request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:29
 */
class AlipayInsuranceAccountInsureRequest
{
	/** 
	 * 支付宝账户ID
	 **/
	private $accountId;
	
	/** 
	 * 合作方来源
	 **/
	private $source;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setAccountId($accountId)
	{
		$this->accountId = $accountId;
		$this->apiParas["account_id"] = $accountId;
	}

	public function getAccountId()
	{
		return $this->accountId;
	}

	public function setSource($source)
	{
		$this->source = $source;
		$this->apiParas["source"] = $source;
	}

	public function getSource()
	{
		return $this->source;
	}

	public function getApiMethodName()
	{
		return "alipay.insurance.account.insure";
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
