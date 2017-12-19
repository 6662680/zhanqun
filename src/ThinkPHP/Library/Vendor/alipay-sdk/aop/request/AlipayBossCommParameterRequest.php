<?php
/**
 * ALIPAY API: alipay.boss.comm.parameter request
 *
 * @author auto create
 * @since 1.0, 2015-09-22 11:12:32
 */
class AlipayBossCommParameterRequest
{
	/** 
	 * 银行卡明文卡号
	 **/
	private $cardNo;
	
	/** 
	 * 机构内标（instId）
	 **/
	private $instId;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setCardNo($cardNo)
	{
		$this->cardNo = $cardNo;
		$this->apiParas["card_no"] = $cardNo;
	}

	public function getCardNo()
	{
		return $this->cardNo;
	}

	public function setInstId($instId)
	{
		$this->instId = $instId;
		$this->apiParas["inst_id"] = $instId;
	}

	public function getInstId()
	{
		return $this->instId;
	}

	public function getApiMethodName()
	{
		return "alipay.boss.comm.parameter";
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
