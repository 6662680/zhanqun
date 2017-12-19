<?php
/**
 * ALIPAY API: alipay.platform.app.add request
 *
 * @author auto create
 * @since 1.0, 2014-06-12 17:16:02
 */
class AlipayPlatformAppAddRequest
{
	/** 
	 * ISV支付宝ID
	 **/
	private $alipayUserId;
	
	/** 
	 * 应用接收回调的地址
	 **/
	private $appCallbackUrl;
	
	/** 
	 * 应用是否hosting
	 **/
	private $appIsHosting;
	
	/** 
	 * 应用名称
	 **/
	private $appName;
	
	/** 
	 * 应用描述
	 **/
	private $description;
	
	/** 
	 * ISV的描述
	 **/
	private $isvDescription;
	
	/** 
	 * ISV邮箱
	 **/
	private $isvEmail;
	
	/** 
	 * ISV名称,服务商
	 **/
	private $isvName;
	
	/** 
	 * ISV所在平台账号
	 **/
	private $isvNick;
	
	/** 
	 * ISV手机号码
	 **/
	private $isvPhone;
	
	/** 
	 * 类型：1：个人；2：公司
	 **/
	private $isvType;
	
	/** 
	 * ISV网站主页
	 **/
	private $isvWebHost;
	
	/** 
	 * LOGO链接。80*80
	 **/
	private $logoUrl;
	
	/** 
	 * 应用的客服支持Email
	 **/
	private $supportEmail;
	
	/** 
	 * 应用的客服电话号码
	 **/
	private $supportPhoneNo;
	
	/** 
	 * 应用的旺旺客服ID
	 **/
	private $supportWangwangId;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setAlipayUserId($alipayUserId)
	{
		$this->alipayUserId = $alipayUserId;
		$this->apiParas["alipay_user_id"] = $alipayUserId;
	}

	public function getAlipayUserId()
	{
		return $this->alipayUserId;
	}

	public function setAppCallbackUrl($appCallbackUrl)
	{
		$this->appCallbackUrl = $appCallbackUrl;
		$this->apiParas["app_callback_url"] = $appCallbackUrl;
	}

	public function getAppCallbackUrl()
	{
		return $this->appCallbackUrl;
	}

	public function setAppIsHosting($appIsHosting)
	{
		$this->appIsHosting = $appIsHosting;
		$this->apiParas["app_is_hosting"] = $appIsHosting;
	}

	public function getAppIsHosting()
	{
		return $this->appIsHosting;
	}

	public function setAppName($appName)
	{
		$this->appName = $appName;
		$this->apiParas["app_name"] = $appName;
	}

	public function getAppName()
	{
		return $this->appName;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		$this->apiParas["description"] = $description;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function setIsvDescription($isvDescription)
	{
		$this->isvDescription = $isvDescription;
		$this->apiParas["isv_description"] = $isvDescription;
	}

	public function getIsvDescription()
	{
		return $this->isvDescription;
	}

	public function setIsvEmail($isvEmail)
	{
		$this->isvEmail = $isvEmail;
		$this->apiParas["isv_email"] = $isvEmail;
	}

	public function getIsvEmail()
	{
		return $this->isvEmail;
	}

	public function setIsvName($isvName)
	{
		$this->isvName = $isvName;
		$this->apiParas["isv_name"] = $isvName;
	}

	public function getIsvName()
	{
		return $this->isvName;
	}

	public function setIsvNick($isvNick)
	{
		$this->isvNick = $isvNick;
		$this->apiParas["isv_nick"] = $isvNick;
	}

	public function getIsvNick()
	{
		return $this->isvNick;
	}

	public function setIsvPhone($isvPhone)
	{
		$this->isvPhone = $isvPhone;
		$this->apiParas["isv_phone"] = $isvPhone;
	}

	public function getIsvPhone()
	{
		return $this->isvPhone;
	}

	public function setIsvType($isvType)
	{
		$this->isvType = $isvType;
		$this->apiParas["isv_type"] = $isvType;
	}

	public function getIsvType()
	{
		return $this->isvType;
	}

	public function setIsvWebHost($isvWebHost)
	{
		$this->isvWebHost = $isvWebHost;
		$this->apiParas["isv_web_host"] = $isvWebHost;
	}

	public function getIsvWebHost()
	{
		return $this->isvWebHost;
	}

	public function setLogoUrl($logoUrl)
	{
		$this->logoUrl = $logoUrl;
		$this->apiParas["logo_url"] = $logoUrl;
	}

	public function getLogoUrl()
	{
		return $this->logoUrl;
	}

	public function setSupportEmail($supportEmail)
	{
		$this->supportEmail = $supportEmail;
		$this->apiParas["support_email"] = $supportEmail;
	}

	public function getSupportEmail()
	{
		return $this->supportEmail;
	}

	public function setSupportPhoneNo($supportPhoneNo)
	{
		$this->supportPhoneNo = $supportPhoneNo;
		$this->apiParas["support_phone_no"] = $supportPhoneNo;
	}

	public function getSupportPhoneNo()
	{
		return $this->supportPhoneNo;
	}

	public function setSupportWangwangId($supportWangwangId)
	{
		$this->supportWangwangId = $supportWangwangId;
		$this->apiParas["support_wangwang_id"] = $supportWangwangId;
	}

	public function getSupportWangwangId()
	{
		return $this->supportWangwangId;
	}

	public function getApiMethodName()
	{
		return "alipay.platform.app.add";
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
