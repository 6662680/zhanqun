<?php
/**
 * ALIPAY API: alipay.transfer.bank.pay.apply request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:15:57
 */
class AlipayTransferBankPayApplyRequest
{
	/** 
	 * 支付宝账户类型对应的id。如email、手机等
	 **/
	private $alipayAccountId;
	
	/** 
	 * 支付宝账户类型
ALIPAY_LOGON_ID:支付宝账户登录名
ALIPAY_USER_ID:支付宝账户userId
	 **/
	private $alipayAccountType;
	
	/** 
	 * 金额（单位为分）
	 **/
	private $amount;
	
	/** 
	 * 银行订单号
	 **/
	private $bankOrderNo;
	
	/** 
	 * 业务订单号
	 **/
	private $bizOrderNo;
	
	/** 
	 * 业务类型(DY：为本人充值，DO:为他人充值)
	 **/
	private $bizType;
	
	/** 
	 * 转账备注信息,暂不支持对外显示
	 **/
	private $memo;
	
	/** 
	 * 付款方卡账户类型
CP对公
PI:对私
	 **/
	private $payerCardAccountType;
	
	/** 
	 * 机构用户卡id标示，填写卡id类型对应的值。如email、手机等
	 **/
	private $payerCardId;
	
	/** 
	 * 付款方卡id标示类型
BANK_ID：银行用户uid
BANK_CARD_NO:银行卡号
	 **/
	private $payerCardIdType;
	
	/** 
	 * 付款方卡类型
DC:借记卡
CC:贷记卡
	 **/
	private $payerCardType;
	
	/** 
	 * 付款方机构编号，由支付宝定义
	 **/
	private $payerInstId;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setAlipayAccountId($alipayAccountId)
	{
		$this->alipayAccountId = $alipayAccountId;
		$this->apiParas["alipay_account_id"] = $alipayAccountId;
	}

	public function getAlipayAccountId()
	{
		return $this->alipayAccountId;
	}

	public function setAlipayAccountType($alipayAccountType)
	{
		$this->alipayAccountType = $alipayAccountType;
		$this->apiParas["alipay_account_type"] = $alipayAccountType;
	}

	public function getAlipayAccountType()
	{
		return $this->alipayAccountType;
	}

	public function setAmount($amount)
	{
		$this->amount = $amount;
		$this->apiParas["amount"] = $amount;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function setBankOrderNo($bankOrderNo)
	{
		$this->bankOrderNo = $bankOrderNo;
		$this->apiParas["bank_order_no"] = $bankOrderNo;
	}

	public function getBankOrderNo()
	{
		return $this->bankOrderNo;
	}

	public function setBizOrderNo($bizOrderNo)
	{
		$this->bizOrderNo = $bizOrderNo;
		$this->apiParas["biz_order_no"] = $bizOrderNo;
	}

	public function getBizOrderNo()
	{
		return $this->bizOrderNo;
	}

	public function setBizType($bizType)
	{
		$this->bizType = $bizType;
		$this->apiParas["biz_type"] = $bizType;
	}

	public function getBizType()
	{
		return $this->bizType;
	}

	public function setMemo($memo)
	{
		$this->memo = $memo;
		$this->apiParas["memo"] = $memo;
	}

	public function getMemo()
	{
		return $this->memo;
	}

	public function setPayerCardAccountType($payerCardAccountType)
	{
		$this->payerCardAccountType = $payerCardAccountType;
		$this->apiParas["payer_card_account_type"] = $payerCardAccountType;
	}

	public function getPayerCardAccountType()
	{
		return $this->payerCardAccountType;
	}

	public function setPayerCardId($payerCardId)
	{
		$this->payerCardId = $payerCardId;
		$this->apiParas["payer_card_id"] = $payerCardId;
	}

	public function getPayerCardId()
	{
		return $this->payerCardId;
	}

	public function setPayerCardIdType($payerCardIdType)
	{
		$this->payerCardIdType = $payerCardIdType;
		$this->apiParas["payer_card_id_type"] = $payerCardIdType;
	}

	public function getPayerCardIdType()
	{
		return $this->payerCardIdType;
	}

	public function setPayerCardType($payerCardType)
	{
		$this->payerCardType = $payerCardType;
		$this->apiParas["payer_card_type"] = $payerCardType;
	}

	public function getPayerCardType()
	{
		return $this->payerCardType;
	}

	public function setPayerInstId($payerInstId)
	{
		$this->payerInstId = $payerInstId;
		$this->apiParas["payer_inst_id"] = $payerInstId;
	}

	public function getPayerInstId()
	{
		return $this->payerInstId;
	}

	public function getApiMethodName()
	{
		return "alipay.transfer.bank.pay.apply";
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
