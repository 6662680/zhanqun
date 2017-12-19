<?php
/**
 * ALIPAY API: alipay.ebpp.subscribe.upload request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:46
 */
class AlipayEbppSubscribeUploadRequest
{
	/** 
	 * 出账机构简称，例如杭州水务HZWATER
	 **/
	private $chargeInst;
	
	/** 
	 * 业务类型，例如缴费JF
	 **/
	private $orderType;
	
	/** 
	 * 子业务类型，例如电费ELECTRIC
	 **/
	private $subOrderType;
	
	/** 
	 * 回盘文件中的内容格式，例如9023|UN_SUBSCRIBE 为户号为9023，订阅状态为UN_SUBSCRIBE
	 **/
	private $subscribeDetail;

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

	public function setOrderType($orderType)
	{
		$this->orderType = $orderType;
		$this->apiParas["order_type"] = $orderType;
	}

	public function getOrderType()
	{
		return $this->orderType;
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

	public function setSubscribeDetail($subscribeDetail)
	{
		$this->subscribeDetail = $subscribeDetail;
		$this->apiParas["subscribe_detail"] = $subscribeDetail;
	}

	public function getSubscribeDetail()
	{
		return $this->subscribeDetail;
	}

	public function getApiMethodName()
	{
		return "alipay.ebpp.subscribe.upload";
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
