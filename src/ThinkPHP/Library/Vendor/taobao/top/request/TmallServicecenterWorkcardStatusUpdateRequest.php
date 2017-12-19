<?php
/**
 * TOP API: tmall.servicecenter.workcard.status.update request
 * 
 * @author auto create
 * @since 1.0, 2015.10.09
 */
class TmallServicecenterWorkcardStatusUpdateRequest
{
	/** 
	 * 任务类工单，预约或者上门地址
	 **/
	private $address;
	
	/** 
	 * 买家id
	 **/
	private $buyerId;
	
	/** 
	 * 备注,256个字符以内
	 **/
	private $comments;
	
	/** 
	 * 任务执行，预约联系人
	 **/
	private $contactName;
	
	/** 
	 * 任务执行，预约联系人电话
	 **/
	private $contactPhone;
	
	/** 
	 * 服务生效时间 ：工单类型为合同工单时，必选！
	 **/
	private $effectDate;
	
	/** 
	 * 服务失效时间 ：工单类型为合同工单时，必选！
	 **/
	private $expireDate;
	
	/** 
	 * 目前仅支持5种状态的反馈：3=授理， 10=拒绝 ，4=执行 ，5=成功，11=失败。（所有状态列表： -1： 初始化 0： 生成 1： 生效 2： 申请 3： 受理 4： 执行 5： 成功 9： 结算 10： 拒绝 11： 失败 12 ： 撤销 13： 暂停 19： 终止）
	 **/
	private $status;
	
	/** 
	 * 工单类型： 2（合同） 或者 1(任务）
	 **/
	private $type;
	
	/** 
	 * 更新时间
	 **/
	private $updateDate;
	
	/** 
	 * api调用者
	 **/
	private $updater;
	
	/** 
	 * 工单id
	 **/
	private $workcardId;
	
	private $apiParas = array();
	
	public function setAddress($address)
	{
		$this->address = $address;
		$this->apiParas["address"] = $address;
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function setBuyerId($buyerId)
	{
		$this->buyerId = $buyerId;
		$this->apiParas["buyer_id"] = $buyerId;
	}

	public function getBuyerId()
	{
		return $this->buyerId;
	}

	public function setComments($comments)
	{
		$this->comments = $comments;
		$this->apiParas["comments"] = $comments;
	}

	public function getComments()
	{
		return $this->comments;
	}

	public function setContactName($contactName)
	{
		$this->contactName = $contactName;
		$this->apiParas["contact_name"] = $contactName;
	}

	public function getContactName()
	{
		return $this->contactName;
	}

	public function setContactPhone($contactPhone)
	{
		$this->contactPhone = $contactPhone;
		$this->apiParas["contact_phone"] = $contactPhone;
	}

	public function getContactPhone()
	{
		return $this->contactPhone;
	}

	public function setEffectDate($effectDate)
	{
		$this->effectDate = $effectDate;
		$this->apiParas["effect_date"] = $effectDate;
	}

	public function getEffectDate()
	{
		return $this->effectDate;
	}

	public function setExpireDate($expireDate)
	{
		$this->expireDate = $expireDate;
		$this->apiParas["expire_date"] = $expireDate;
	}

	public function getExpireDate()
	{
		return $this->expireDate;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		$this->apiParas["status"] = $status;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setType($type)
	{
		$this->type = $type;
		$this->apiParas["type"] = $type;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setUpdateDate($updateDate)
	{
		$this->updateDate = $updateDate;
		$this->apiParas["update_date"] = $updateDate;
	}

	public function getUpdateDate()
	{
		return $this->updateDate;
	}

	public function setUpdater($updater)
	{
		$this->updater = $updater;
		$this->apiParas["updater"] = $updater;
	}

	public function getUpdater()
	{
		return $this->updater;
	}

	public function setWorkcardId($workcardId)
	{
		$this->workcardId = $workcardId;
		$this->apiParas["workcard_id"] = $workcardId;
	}

	public function getWorkcardId()
	{
		return $this->workcardId;
	}

	public function getApiMethodName()
	{
		return "tmall.servicecenter.workcard.status.update";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkMaxLength($this->address,50,"address");
		RequestCheckUtil::checkMaxLength($this->comments,256,"comments");
		RequestCheckUtil::checkMaxLength($this->contactName,10,"contactName");
		RequestCheckUtil::checkMaxLength($this->contactPhone,20,"contactPhone");
		RequestCheckUtil::checkNotNull($this->status,"status");
		RequestCheckUtil::checkNotNull($this->type,"type");
		RequestCheckUtil::checkNotNull($this->updateDate,"updateDate");
		RequestCheckUtil::checkNotNull($this->updater,"updater");
		RequestCheckUtil::checkNotNull($this->workcardId,"workcardId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
