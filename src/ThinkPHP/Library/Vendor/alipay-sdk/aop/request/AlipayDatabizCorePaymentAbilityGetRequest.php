<?php
/**
 * ALIPAY API: alipay.databiz.core.payment.ability.get request
 *
 * @author auto create
 * @since 1.0, 2015-08-17 09:21:20
 */
class AlipayDatabizCorePaymentAbilityGetRequest
{
	/** 
	 * 外部商户应用名称
	 **/
	private $appInfo;
	
	/** 
	 * 移动设备唯一标示码，后续版本废弃该参数，手机号码作为唯一查询标示。
	 **/
	private $imei;
	
	/** 
	 * 手机号码，必选！
	 **/
	private $mobileNum;
	
	/** 
	 * 用户终端的UTDID（阿里用来标识一个终端设备的唯一性标识）
	 **/
	private $utdid;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setAppInfo($appInfo)
	{
		$this->appInfo = $appInfo;
		$this->apiParas["app_info"] = $appInfo;
	}

	public function getAppInfo()
	{
		return $this->appInfo;
	}

	public function setImei($imei)
	{
		$this->imei = $imei;
		$this->apiParas["imei"] = $imei;
	}

	public function getImei()
	{
		return $this->imei;
	}

	public function setMobileNum($mobileNum)
	{
		$this->mobileNum = $mobileNum;
		$this->apiParas["mobile_num"] = $mobileNum;
	}

	public function getMobileNum()
	{
		return $this->mobileNum;
	}

	public function setUtdid($utdid)
	{
		$this->utdid = $utdid;
		$this->apiParas["utdid"] = $utdid;
	}

	public function getUtdid()
	{
		return $this->utdid;
	}

	public function getApiMethodName()
	{
		return "alipay.databiz.core.payment.ability.get";
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
