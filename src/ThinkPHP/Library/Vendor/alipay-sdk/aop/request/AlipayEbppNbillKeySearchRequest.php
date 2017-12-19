<?php
/**
 * ALIPAY API: alipay.ebpp.nbill.key.search request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:52
 */
class AlipayEbppNbillKeySearchRequest
{
	/** 
	 * 取消纸质账单枚举code
	 **/
	private $cancelpaperCode;
	
	/** 
	 * 支付宝给每个出账机构指定了一个对应的英文短名称来唯一表示该收费单位
	 **/
	private $chargeInst;
	
	/** 
	 * 结束时间：格式为yyyy-MM-dd HH:mm:ss
	 **/
	private $endTime;
	
	/** 
	 * 返回户号集合
	 **/
	private $fields;
	
	/** 
	 * 该属性已废弃使用，删除不了属性，也修改不了。
	 **/
	private $isCancelpaper;
	
	/** 
	 * 该属性已废弃使用，删除不了属性，也修改不了。
	 **/
	private $isSubscribed;
	
	/** 
	 * 支付宝订单类型。公共事业缴纳JF,信用卡还款HK

	1
	 **/
	private $orderType;
	
	/** 
	 * 查询纸质账单结束时间
	 **/
	private $pendTime;
	
	/** 
	 * 纸质账单取消开始时间：格式为yyyy-MM-dd HH:mm:ss
	 **/
	private $pstartTime;
	
	/** 
	 * 开始时间，时间必须是今天范围之内。格式为yyyy-MM-dd HH:mm:ss
	 **/
	private $startTime;
	
	/** 
	 * 子业务类型是业务类型的下一级概念，例如：WATER表示JF下面的水费
	 **/
	private $subOrderType;
	
	/** 
	 * 订阅状态枚举code
	 **/
	private $subscribedCode;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setCancelpaperCode($cancelpaperCode)
	{
		$this->cancelpaperCode = $cancelpaperCode;
		$this->apiParas["cancelpaper_code"] = $cancelpaperCode;
	}

	public function getCancelpaperCode()
	{
		return $this->cancelpaperCode;
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

	public function setEndTime($endTime)
	{
		$this->endTime = $endTime;
		$this->apiParas["end_time"] = $endTime;
	}

	public function getEndTime()
	{
		return $this->endTime;
	}

	public function setFields($fields)
	{
		$this->fields = $fields;
		$this->apiParas["fields"] = $fields;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function setIsCancelpaper($isCancelpaper)
	{
		$this->isCancelpaper = $isCancelpaper;
		$this->apiParas["is_cancelpaper"] = $isCancelpaper;
	}

	public function getIsCancelpaper()
	{
		return $this->isCancelpaper;
	}

	public function setIsSubscribed($isSubscribed)
	{
		$this->isSubscribed = $isSubscribed;
		$this->apiParas["is_subscribed"] = $isSubscribed;
	}

	public function getIsSubscribed()
	{
		return $this->isSubscribed;
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

	public function setPendTime($pendTime)
	{
		$this->pendTime = $pendTime;
		$this->apiParas["pend_time"] = $pendTime;
	}

	public function getPendTime()
	{
		return $this->pendTime;
	}

	public function setPstartTime($pstartTime)
	{
		$this->pstartTime = $pstartTime;
		$this->apiParas["pstart_time"] = $pstartTime;
	}

	public function getPstartTime()
	{
		return $this->pstartTime;
	}

	public function setStartTime($startTime)
	{
		$this->startTime = $startTime;
		$this->apiParas["start_time"] = $startTime;
	}

	public function getStartTime()
	{
		return $this->startTime;
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

	public function setSubscribedCode($subscribedCode)
	{
		$this->subscribedCode = $subscribedCode;
		$this->apiParas["subscribed_code"] = $subscribedCode;
	}

	public function getSubscribedCode()
	{
		return $this->subscribedCode;
	}

	public function getApiMethodName()
	{
		return "alipay.ebpp.nbill.key.search";
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
