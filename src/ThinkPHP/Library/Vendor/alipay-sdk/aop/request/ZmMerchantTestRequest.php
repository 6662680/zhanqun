<?php
/**
 * ALIPAY API: zm.merchant.test request
 *
 * @author auto create
 * @since 1.0, 2015-09-08 13:20:29
 */
class ZmMerchantTestRequest
{
	/** 
	 * name
	 **/
	private $name;
	
	/** 
	 * risk detail
	 **/
	private $riskDetail;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setName($name)
	{
		$this->name = $name;
		$this->apiParas["name"] = $name;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setRiskDetail($riskDetail)
	{
		$this->riskDetail = $riskDetail;
		$this->apiParas["risk_detail"] = json_encode($riskDetail);
	}

	public function getRiskDetail()
	{
		return $this->riskDetail;
	}

	public function getApiMethodName()
	{
		return "zm.merchant.test";
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
