<?php
/**
 * ALIPAY API: alipay.evercall.contract.sign request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:40
 */
class AlipayEvercallContractSignRequest
{
	/** 
	 * 预警阀值，单位是元。小数点保留两位，精确到分
	 **/
	private $alertBalance;
	
	/** 
	 * 签约手机号
	 **/
	private $mobileNo;
	
	/** 
	 * 充值金额，单位是元。小数点保留两位，精确到分
	 **/
	private $rechargeAmount;
	
	/** 
	 * 手机充值代扣确认
	 **/
	private $rechargeConfirm;
	
	/** 
	 * 签约渠道(SMS：短信方式 CLIENT：客户端 WAP：wap SITE：主站 OPENPLAT:开放平台 OTHER：其他)
	 **/
	private $signChannel;
	
	/** 
	 * 运营统计：taobao,alipay,telecom
	 **/
	private $signFrom;
	
	/** 
	 * 支付宝账户号
	 **/
	private $userId;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setAlertBalance($alertBalance)
	{
		$this->alertBalance = $alertBalance;
		$this->apiParas["alert_balance"] = $alertBalance;
	}

	public function getAlertBalance()
	{
		return $this->alertBalance;
	}

	public function setMobileNo($mobileNo)
	{
		$this->mobileNo = $mobileNo;
		$this->apiParas["mobile_no"] = $mobileNo;
	}

	public function getMobileNo()
	{
		return $this->mobileNo;
	}

	public function setRechargeAmount($rechargeAmount)
	{
		$this->rechargeAmount = $rechargeAmount;
		$this->apiParas["recharge_amount"] = $rechargeAmount;
	}

	public function getRechargeAmount()
	{
		return $this->rechargeAmount;
	}

	public function setRechargeConfirm($rechargeConfirm)
	{
		$this->rechargeConfirm = $rechargeConfirm;
		$this->apiParas["recharge_confirm"] = $rechargeConfirm;
	}

	public function getRechargeConfirm()
	{
		return $this->rechargeConfirm;
	}

	public function setSignChannel($signChannel)
	{
		$this->signChannel = $signChannel;
		$this->apiParas["sign_channel"] = $signChannel;
	}

	public function getSignChannel()
	{
		return $this->signChannel;
	}

	public function setSignFrom($signFrom)
	{
		$this->signFrom = $signFrom;
		$this->apiParas["sign_from"] = $signFrom;
	}

	public function getSignFrom()
	{
		return $this->signFrom;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		$this->apiParas["user_id"] = $userId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getApiMethodName()
	{
		return "alipay.evercall.contract.sign";
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
