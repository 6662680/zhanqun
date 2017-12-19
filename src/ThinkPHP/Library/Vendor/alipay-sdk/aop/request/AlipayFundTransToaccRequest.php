<?php
/**
 * ALIPAY API: alipay.fund.trans.toacc request
 *
 * @author auto create
 * @since 1.0, 2014-12-04 13:13:28
 */
class AlipayFundTransToaccRequest
{
	/** 
	 * 转账金额
	 **/
	private $amount;
	
	/** 
	 * 扩展参数，json字符串格式
	 **/
	private $extParam;
	
	/** 
	 * 转账备注
	 **/
	private $memo;
	
	/** 
	 * 商户转账唯一订单号
	 **/
	private $outBizNo;
	
	/** 
	 * 收款方账户
	 **/
	private $payeeAccount;
	
	/** 
	 * 收款方真实姓名
	 **/
	private $payeeRealName;
	
	/** 
	 * 收款方显示姓名
	 **/
	private $payeeShowName;
	
	/** 
	 * 收款方账户类型
	 **/
	private $payeeType;
	
	/** 
	 * 付款方账户
	 **/
	private $payerAccount;
	
	/** 
	 * 付款方真实姓名
	 **/
	private $payerRealName;
	
	/** 
	 * 付款方显示姓名
	 **/
	private $payerShowName;
	
	/** 
	 * 付款方账户类型
	 **/
	private $payerType;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setAmount($amount)
	{
		$this->amount = $amount;
		$this->apiParas["amount"] = $amount;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function setExtParam($extParam)
	{
		$this->extParam = $extParam;
		$this->apiParas["ext_param"] = $extParam;
	}

	public function getExtParam()
	{
		return $this->extParam;
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

	public function setOutBizNo($outBizNo)
	{
		$this->outBizNo = $outBizNo;
		$this->apiParas["out_biz_no"] = $outBizNo;
	}

	public function getOutBizNo()
	{
		return $this->outBizNo;
	}

	public function setPayeeAccount($payeeAccount)
	{
		$this->payeeAccount = $payeeAccount;
		$this->apiParas["payee_account"] = $payeeAccount;
	}

	public function getPayeeAccount()
	{
		return $this->payeeAccount;
	}

	public function setPayeeRealName($payeeRealName)
	{
		$this->payeeRealName = $payeeRealName;
		$this->apiParas["payee_real_name"] = $payeeRealName;
	}

	public function getPayeeRealName()
	{
		return $this->payeeRealName;
	}

	public function setPayeeShowName($payeeShowName)
	{
		$this->payeeShowName = $payeeShowName;
		$this->apiParas["payee_show_name"] = $payeeShowName;
	}

	public function getPayeeShowName()
	{
		return $this->payeeShowName;
	}

	public function setPayeeType($payeeType)
	{
		$this->payeeType = $payeeType;
		$this->apiParas["payee_type"] = $payeeType;
	}

	public function getPayeeType()
	{
		return $this->payeeType;
	}

	public function setPayerAccount($payerAccount)
	{
		$this->payerAccount = $payerAccount;
		$this->apiParas["payer_account"] = $payerAccount;
	}

	public function getPayerAccount()
	{
		return $this->payerAccount;
	}

	public function setPayerRealName($payerRealName)
	{
		$this->payerRealName = $payerRealName;
		$this->apiParas["payer_real_name"] = $payerRealName;
	}

	public function getPayerRealName()
	{
		return $this->payerRealName;
	}

	public function setPayerShowName($payerShowName)
	{
		$this->payerShowName = $payerShowName;
		$this->apiParas["payer_show_name"] = $payerShowName;
	}

	public function getPayerShowName()
	{
		return $this->payerShowName;
	}

	public function setPayerType($payerType)
	{
		$this->payerType = $payerType;
		$this->apiParas["payer_type"] = $payerType;
	}

	public function getPayerType()
	{
		return $this->payerType;
	}

	public function getApiMethodName()
	{
		return "alipay.fund.trans.toacc";
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
