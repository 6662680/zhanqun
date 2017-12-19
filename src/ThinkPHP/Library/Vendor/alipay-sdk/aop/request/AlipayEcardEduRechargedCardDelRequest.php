<?php
/**
 * ALIPAY API: alipay.ecard.edu.recharged.card.del request
 *
 * @author auto create
 * @since 1.0, 2014-07-21 10:30:25
 */
class AlipayEcardEduRechargedCardDelRequest
{
	/** 
	 * 机构code
	 **/
	private $agentCode;
	
	/** 
	 * 支付宝userid
	 **/
	private $alipayUserId;
	
	/** 
	 * 校园一卡通卡号
	 **/
	private $cardNo;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setAgentCode($agentCode)
	{
		$this->agentCode = $agentCode;
		$this->apiParas["agent_code"] = $agentCode;
	}

	public function getAgentCode()
	{
		return $this->agentCode;
	}

	public function setAlipayUserId($alipayUserId)
	{
		$this->alipayUserId = $alipayUserId;
		$this->apiParas["alipay_user_id"] = $alipayUserId;
	}

	public function getAlipayUserId()
	{
		return $this->alipayUserId;
	}

	public function setCardNo($cardNo)
	{
		$this->cardNo = $cardNo;
		$this->apiParas["card_no"] = $cardNo;
	}

	public function getCardNo()
	{
		return $this->cardNo;
	}

	public function getApiMethodName()
	{
		return "alipay.ecard.edu.recharged.card.del";
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
