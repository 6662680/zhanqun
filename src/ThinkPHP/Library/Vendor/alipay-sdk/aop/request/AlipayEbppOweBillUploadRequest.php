<?php
/**
 * ALIPAY API: alipay.ebpp.owe.bill.upload request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:51
 */
class AlipayEbppOweBillUploadRequest
{
	/** 
	 * 支付宝给每个出账机构指定了一个对应的英文短名称来唯一表示该收费单位。
	 **/
	private $chargeInst;
	
	/** 
	 * 销账机构
	 **/
	private $chargeoffInst;
	
	/** 
	 * 文件摘要，算法SHA
	 **/
	private $digestOweBill;
	
	/** 
	 * 支付宝订单类型。公共事业缴纳JF,信用卡还款HK
	 **/
	private $orderType;
	
	/** 
	 * 文件内容 
支持的文件类型：zip,rar,csv,doc,docx,xls,xlsx
	 **/
	private $oweBill;
	
	/** 
	 * 子业务类型是业务类型的下一级概念，例如：WATER表示JF下面的水费，ELECTRIC表示JF下面的电费，GAS表示JF下面的燃气费。
	 **/
	private $subOrderType;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setChargeInst($chargeInst)
	{
		$this->chargeInst = $chargeInst;
		$this->apiParas["charge_inst"] = $chargeInst;
	}

	public function getChargeInst()
	{
		return $this->chargeInst;
	}

	public function setChargeoffInst($chargeoffInst)
	{
		$this->chargeoffInst = $chargeoffInst;
		$this->apiParas["chargeoff_inst"] = $chargeoffInst;
	}

	public function getChargeoffInst()
	{
		return $this->chargeoffInst;
	}

	public function setDigestOweBill($digestOweBill)
	{
		$this->digestOweBill = $digestOweBill;
		$this->apiParas["digest_owe_bill"] = $digestOweBill;
	}

	public function getDigestOweBill()
	{
		return $this->digestOweBill;
	}

	public function setOrderType($orderType)
	{
		$this->orderType = $orderType;
		$this->apiParas["order_type"] = $orderType;
	}

	public function getOrderType()
	{
		return $this->orderType;
	}

	public function setOweBill($oweBill)
	{
		$this->oweBill = $oweBill;
		$this->apiParas["owe_bill"] = $oweBill;
	}

	public function getOweBill()
	{
		return $this->oweBill;
	}

	public function setSubOrderType($subOrderType)
	{
		$this->subOrderType = $subOrderType;
		$this->apiParas["sub_order_type"] = $subOrderType;
	}

	public function getSubOrderType()
	{
		return $this->subOrderType;
	}

	public function getApiMethodName()
	{
		return "alipay.ebpp.owe.bill.upload";
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
