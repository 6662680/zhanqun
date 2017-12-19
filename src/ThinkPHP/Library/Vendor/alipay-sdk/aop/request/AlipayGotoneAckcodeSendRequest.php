<?php
/**
 * ALIPAY API: alipay.gotone.ackcode.send request
 *
 * @author auto create
 * @since 1.0, 2016-01-14 17:47:01
 */
class AlipayGotoneAckcodeSendRequest
{
	/** 
	 * 格式：key=value 多个以&rdquo;|&rdquo;分割
	 **/
	private $arguments;
	
	/** 
	 * 区分相同的手机号、业务类型，但不同业务场景的手机校验码等情况校验。比如使用order_no
	 **/
	private $bizNo;
	
	/** 
	 * 发送手机校验码业务类型，为空默认DEFAULT_TYPE
	 **/
	private $bizType;
	
	/** 
	 * 接收校验码短信手机号
	 **/
	private $mobile;
	
	/** 
	 * 短信模板对应的serviceCode
	 **/
	private $serviceCode;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setArguments($arguments)
	{
		$this->arguments = $arguments;
		$this->apiParas["arguments"] = $arguments;
	}

	public function getArguments()
	{
		return $this->arguments;
	}

	public function setBizNo($bizNo)
	{
		$this->bizNo = $bizNo;
		$this->apiParas["biz_no"] = $bizNo;
	}

	public function getBizNo()
	{
		return $this->bizNo;
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

	public function setMobile($mobile)
	{
		$this->mobile = $mobile;
		$this->apiParas["mobile"] = $mobile;
	}

	public function getMobile()
	{
		return $this->mobile;
	}

	public function setServiceCode($serviceCode)
	{
		$this->serviceCode = $serviceCode;
		$this->apiParas["service_code"] = $serviceCode;
	}

	public function getServiceCode()
	{
		return $this->serviceCode;
	}

	public function getApiMethodName()
	{
		return "alipay.gotone.ackcode.send";
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
