<?php
/**
 * ALIPAY API: alipay.ebpp.subscribe.checkcode.import request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:47
 */
class AlipayEbppSubscribeCheckcodeImportRequest
{
	/** 
	 * 缴费户号
	 **/
	private $billKey;
	
	/** 
	 * 业务类型
	 **/
	private $bizType;
	
	/** 
	 * 出账机构短名称
	 **/
	private $chargeInst;
	
	/** 
	 * 订阅校验码
	 **/
	private $checkCode;
	
	/** 
	 * 扩展字段内容
	 **/
	private $extendField;
	
	/** 
	 * 子业务类型
	 **/
	private $subBizType;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setBillKey($billKey)
	{
		$this->billKey = $billKey;
		$this->apiParas["bill_key"] = $billKey;
	}

	public function getBillKey()
	{
		return $this->billKey;
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

	public function setChargeInst($chargeInst)
	{
		$this->chargeInst = $chargeInst;
		$this->apiParas["charge_inst"] = $chargeInst;
	}

	public function getChargeInst()
	{
		return $this->chargeInst;
	}

	public function setCheckCode($checkCode)
	{
		$this->checkCode = $checkCode;
		$this->apiParas["check_code"] = $checkCode;
	}

	public function getCheckCode()
	{
		return $this->checkCode;
	}

	public function setExtendField($extendField)
	{
		$this->extendField = $extendField;
		$this->apiParas["extend_field"] = $extendField;
	}

	public function getExtendField()
	{
		return $this->extendField;
	}

	public function setSubBizType($subBizType)
	{
		$this->subBizType = $subBizType;
		$this->apiParas["sub_biz_type"] = $subBizType;
	}

	public function getSubBizType()
	{
		return $this->subBizType;
	}

	public function getApiMethodName()
	{
		return "alipay.ebpp.subscribe.checkcode.import";
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
