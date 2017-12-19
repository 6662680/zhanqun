<?php
/**
 * ALIPAY API: alipay.ebpp.owe.bill.get request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:51
 */
class AlipayEbppOweBillGetRequest
{
	/** 
	 * 欠费单的id
	 **/
	private $oweBillId;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setOweBillId($oweBillId)
	{
		$this->oweBillId = $oweBillId;
		$this->apiParas["owe_bill_id"] = $oweBillId;
	}

	public function getOweBillId()
	{
		return $this->oweBillId;
	}

	public function getApiMethodName()
	{
		return "alipay.ebpp.owe.bill.get";
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
