<?php
/**
 * ALIPAY API: alipay.coupon.template.create request
 *
 * @author auto create
 * @since 1.0, 2015-04-23 17:54:31
 */
class AlipayCouponTemplateCreateRequest
{
	/** 
	 * 红包使用地址
	 **/
	private $activeUrl;
	
	/** 
	 * 平均红包面额（单位为元）
	 **/
	private $amount;
	
	/** 
	 * 红包名称
	 **/
	private $couponName;
	
	/** 
	 * 红包发放总金额（保证金总额，单位为元）
	 **/
	private $custGuaranteeAmount;
	
	/** 
	 * 领用规则ID（线下提前沟通）
	 **/
	private $drawBizRuleId;
	
	/** 
	 * 红包使用开始时间（绝对时间指定具体日期时间，相对时间为数字天数）
	 **/
	private $gmtCouActive;
	
	/** 
	 * 红包使用结束时间（绝对时间为具体日期时间，相对时间为数字天数）
	 **/
	private $gmtCouExpired;
	
	/** 
	 * 使用结束时间类型（"A"=绝对时间，"R"=相对时间）
	 **/
	private $gmtCouRel;
	
	/** 
	 * 领用结束时间
	 **/
	private $gmtDrawEnd;
	
	/** 
	 * 是否允许累加使用（"Y"=允许，"N"=不允许）
	 **/
	private $isAllowAddUp;
	
	/** 
	 * 预估发行红包个数
	 **/
	private $maxPublishNum;
	
	/** 
	 * 模板创建幂等控制业务号（每个幂等业务号多次调用的参数必需一致，一个幂等业务号多次调用传递不同的业务参数不保证幂等性）
	 **/
	private $outOrderNo;
	
	/** 
	 * 使用规则ID（线下提前沟通）
	 **/
	private $payBizRuleId;
	
	/** 
	 * 保证金账号（红包发放出资支付宝账号）
	 **/
	private $promiseAccount;
	
	/** 
	 * 红包活动地址
	 **/
	private $publishUrl;
	
	/** 
	 * 发行人账号（支付宝登录ID）
	 **/
	private $publisherEmail;
	
	/** 
	 * 发行人名称
	 **/
	private $publisherName;
	
	/** 
	 * 指定交易商户列表（最多支持5个，以分号分隔）
	 **/
	private $sellers;
	
	/** 
	 * 红包模板名称
	 **/
	private $templateName;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setActiveUrl($activeUrl)
	{
		$this->activeUrl = $activeUrl;
		$this->apiParas["active_url"] = $activeUrl;
	}

	public function getActiveUrl()
	{
		return $this->activeUrl;
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

	public function setCouponName($couponName)
	{
		$this->couponName = $couponName;
		$this->apiParas["coupon_name"] = $couponName;
	}

	public function getCouponName()
	{
		return $this->couponName;
	}

	public function setCustGuaranteeAmount($custGuaranteeAmount)
	{
		$this->custGuaranteeAmount = $custGuaranteeAmount;
		$this->apiParas["cust_guarantee_amount"] = $custGuaranteeAmount;
	}

	public function getCustGuaranteeAmount()
	{
		return $this->custGuaranteeAmount;
	}

	public function setDrawBizRuleId($drawBizRuleId)
	{
		$this->drawBizRuleId = $drawBizRuleId;
		$this->apiParas["draw_biz_rule_id"] = $drawBizRuleId;
	}

	public function getDrawBizRuleId()
	{
		return $this->drawBizRuleId;
	}

	public function setGmtCouActive($gmtCouActive)
	{
		$this->gmtCouActive = $gmtCouActive;
		$this->apiParas["gmt_cou_active"] = $gmtCouActive;
	}

	public function getGmtCouActive()
	{
		return $this->gmtCouActive;
	}

	public function setGmtCouExpired($gmtCouExpired)
	{
		$this->gmtCouExpired = $gmtCouExpired;
		$this->apiParas["gmt_cou_expired"] = $gmtCouExpired;
	}

	public function getGmtCouExpired()
	{
		return $this->gmtCouExpired;
	}

	public function setGmtCouRel($gmtCouRel)
	{
		$this->gmtCouRel = $gmtCouRel;
		$this->apiParas["gmt_cou_rel"] = $gmtCouRel;
	}

	public function getGmtCouRel()
	{
		return $this->gmtCouRel;
	}

	public function setGmtDrawEnd($gmtDrawEnd)
	{
		$this->gmtDrawEnd = $gmtDrawEnd;
		$this->apiParas["gmt_draw_end"] = $gmtDrawEnd;
	}

	public function getGmtDrawEnd()
	{
		return $this->gmtDrawEnd;
	}

	public function setIsAllowAddUp($isAllowAddUp)
	{
		$this->isAllowAddUp = $isAllowAddUp;
		$this->apiParas["is_allow_add_up"] = $isAllowAddUp;
	}

	public function getIsAllowAddUp()
	{
		return $this->isAllowAddUp;
	}

	public function setMaxPublishNum($maxPublishNum)
	{
		$this->maxPublishNum = $maxPublishNum;
		$this->apiParas["max_publish_num"] = $maxPublishNum;
	}

	public function getMaxPublishNum()
	{
		return $this->maxPublishNum;
	}

	public function setOutOrderNo($outOrderNo)
	{
		$this->outOrderNo = $outOrderNo;
		$this->apiParas["out_order_no"] = $outOrderNo;
	}

	public function getOutOrderNo()
	{
		return $this->outOrderNo;
	}

	public function setPayBizRuleId($payBizRuleId)
	{
		$this->payBizRuleId = $payBizRuleId;
		$this->apiParas["pay_biz_rule_id"] = $payBizRuleId;
	}

	public function getPayBizRuleId()
	{
		return $this->payBizRuleId;
	}

	public function setPromiseAccount($promiseAccount)
	{
		$this->promiseAccount = $promiseAccount;
		$this->apiParas["promise_account"] = $promiseAccount;
	}

	public function getPromiseAccount()
	{
		return $this->promiseAccount;
	}

	public function setPublishUrl($publishUrl)
	{
		$this->publishUrl = $publishUrl;
		$this->apiParas["publish_url"] = $publishUrl;
	}

	public function getPublishUrl()
	{
		return $this->publishUrl;
	}

	public function setPublisherEmail($publisherEmail)
	{
		$this->publisherEmail = $publisherEmail;
		$this->apiParas["publisher_email"] = $publisherEmail;
	}

	public function getPublisherEmail()
	{
		return $this->publisherEmail;
	}

	public function setPublisherName($publisherName)
	{
		$this->publisherName = $publisherName;
		$this->apiParas["publisher_name"] = $publisherName;
	}

	public function getPublisherName()
	{
		return $this->publisherName;
	}

	public function setSellers($sellers)
	{
		$this->sellers = $sellers;
		$this->apiParas["sellers"] = $sellers;
	}

	public function getSellers()
	{
		return $this->sellers;
	}

	public function setTemplateName($templateName)
	{
		$this->templateName = $templateName;
		$this->apiParas["template_name"] = $templateName;
	}

	public function getTemplateName()
	{
		return $this->templateName;
	}

	public function getApiMethodName()
	{
		return "alipay.coupon.template.create";
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
