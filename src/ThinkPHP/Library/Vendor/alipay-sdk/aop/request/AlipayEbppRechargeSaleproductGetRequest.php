<?php
/**
 * ALIPAY API: alipay.ebpp.recharge.saleproduct.get request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:48
 */
class AlipayEbppRechargeSaleproductGetRequest
{
	/** 
	 * 来源,例如：主站,淘宝,客户端
	 **/
	private $agent;
	
	/** 
	 * 必须以key value形式定义，转为json为格式：{"key1":"value1","key2":"value2","key3":"value3","key4":"value4"}
 后端会直接转换为MAP对象，转换异常会报参数格式错误
	 **/
	private $extendField;
	
	/** 
	 * 手机号码
	 **/
	private $mobileNo;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setAgent($agent)
	{
		$this->agent = $agent;
		$this->apiParas["agent"] = $agent;
	}

	public function getAgent()
	{
		return $this->agent;
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

	public function setMobileNo($mobileNo)
	{
		$this->mobileNo = $mobileNo;
		$this->apiParas["mobile_no"] = $mobileNo;
	}

	public function getMobileNo()
	{
		return $this->mobileNo;
	}

	public function getApiMethodName()
	{
		return "alipay.ebpp.recharge.saleproduct.get";
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
