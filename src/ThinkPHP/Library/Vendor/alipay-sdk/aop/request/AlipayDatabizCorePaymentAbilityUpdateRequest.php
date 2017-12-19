<?php
/**
 * ALIPAY API: alipay.databiz.core.payment.ability.update request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:17:00
 */
class AlipayDatabizCorePaymentAbilityUpdateRequest
{
	/** 
	 * 支付信息
	 **/
	private $payInfo;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setPayInfo($payInfo)
	{
		$this->payInfo = $payInfo;
		$this->apiParas["pay_info"] = $payInfo;
	}

	public function getPayInfo()
	{
		return $this->payInfo;
	}

	public function getApiMethodName()
	{
		return "alipay.databiz.core.payment.ability.update";
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
