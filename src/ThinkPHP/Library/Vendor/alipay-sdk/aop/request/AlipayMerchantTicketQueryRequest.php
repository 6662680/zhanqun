<?php
/**
 * ALIPAY API: alipay.merchant.ticket.query request
 *
 * @author auto create
 * @since 1.0, 2016-01-14 17:50:29
 */
class AlipayMerchantTicketQueryRequest
{
	/** 
	 * 券有效期起始日期 ，yyyy-MM-dd HH:mm:ss格式
	 **/
	private $gmtActive;
	
	/** 
	 * 券有效期截止日期，yyyy-MM-dd HH:mm:ss格式
	 **/
	private $gmtExpired;
	
	/** 
	 * 发券商户partnerId
	 **/
	private $partnerId;
	
	/** 
	 * 券排序方式，目前支持两种方式 ：按创建日期倒序、按过期时间倒序
     * 目前支持的排序方式为：
CREATETIME_DESC_SORT：按创建时间倒序
EXPIREDTIME_DESC_SORT：按失效时间倒序,
	 **/
	private $sort;
	
	/** 
	 * 券状态列表，支持列表，逗号分割，取值：
VALID:可使用
WRITED_OFF:已核销
EXPIRED:已过期
CLOSED:已关闭
WAIT_APPLY：待领取
	 **/
	private $statusList;
	
	/** 
	 * 查询优惠劵类型，取值：
0：商户优惠券
1：商户红包
2：商户兑换券
	 **/
	private $ticketBizType;
	
	/** 
	 * 券码列表，可选，支持列表，逗号分割
	 **/
	private $ticketNoList;
	
	/** 
	 * 个人用户Id
	 **/
	private $userId;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setGmtActive($gmtActive)
	{
		$this->gmtActive = $gmtActive;
		$this->apiParas["gmt_active"] = $gmtActive;
	}

	public function getGmtActive()
	{
		return $this->gmtActive;
	}

	public function setGmtExpired($gmtExpired)
	{
		$this->gmtExpired = $gmtExpired;
		$this->apiParas["gmt_expired"] = $gmtExpired;
	}

	public function getGmtExpired()
	{
		return $this->gmtExpired;
	}

	public function setPartnerId($partnerId)
	{
		$this->partnerId = $partnerId;
		$this->apiParas["partner_id"] = $partnerId;
	}

	public function getPartnerId()
	{
		return $this->partnerId;
	}

	public function setSort($sort)
	{
		$this->sort = $sort;
		$this->apiParas["sort"] = $sort;
	}

	public function getSort()
	{
		return $this->sort;
	}

	public function setStatusList($statusList)
	{
		$this->statusList = $statusList;
		$this->apiParas["status_list"] = $statusList;
	}

	public function getStatusList()
	{
		return $this->statusList;
	}

	public function setTicketBizType($ticketBizType)
	{
		$this->ticketBizType = $ticketBizType;
		$this->apiParas["ticket_biz_type"] = $ticketBizType;
	}

	public function getTicketBizType()
	{
		return $this->ticketBizType;
	}

	public function setTicketNoList($ticketNoList)
	{
		$this->ticketNoList = $ticketNoList;
		$this->apiParas["ticket_no_list"] = $ticketNoList;
	}

	public function getTicketNoList()
	{
		return $this->ticketNoList;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		$this->apiParas["user_id"] = $userId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getApiMethodName()
	{
		return "alipay.merchant.ticket.query";
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
