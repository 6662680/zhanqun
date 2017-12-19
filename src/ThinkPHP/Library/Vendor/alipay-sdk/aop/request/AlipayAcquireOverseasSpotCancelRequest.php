<?php
/**
 * ALIPAY API: alipay.acquire.overseas.spot.cancel request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:17:03
 */
class AlipayAcquireOverseasSpotCancelRequest
{
	/** 
	 * The original partner transaction id given in the payment request
	 **/
	private $partnerTransId;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setPartnerTransId($partnerTransId)
	{
		$this->partnerTransId = $partnerTransId;
		$this->apiParas["partner_trans_id"] = $partnerTransId;
	}

	public function getPartnerTransId()
	{
		return $this->partnerTransId;
	}

	public function getApiMethodName()
	{
		return "alipay.acquire.overseas.spot.cancel";
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
