<?php
/**
 * TOP API: tmall.servicecenter.workcard.status.update request
 * 
 * @author auto create
 * @since 1.0, 2017.06.02
 */
class TmallServicecenterWorkcardStatusUpdateRequest
{
	/** 
	 * 任务类工单，预约或者上门地址
	 **/
	private $address;
	
	/** 
	 * 说明
	 **/
	private $afterServiceMemo;
	
	/** 
	 * 属性定义。例如无忧退货服务，K-V对定义，每对KV用“;”分割，“:”号左边是key右边是value，value如果有多个则以“,”分割。 reasons   :  原因，可能有多个 succeedCount     :    取件成功个数 failedCount    :    取件失败个数 cancelCount      :     取件取消个数 totalCount       :      总取件个数，totalCount= succeedCount + failedCount + cancelCount
	 **/
	private $attribute;
	
	/** 
	 * 说明
	 **/
	private $beforeServiceMemo;
	
	/** 
	 * 买家id
	 **/
	private $buyerId;
	
	/** 
	 * 备注,256个字符以内
	 **/
	private $comments;
	
	/** 
	 * 服务完成时间
	 **/
	private $completeDate;
	
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
	 * 是否上门
	 **/
	private $isVisit;
	
	/** 
	 * 手机号码
	 **/
	private $phoneImei;
	
	/** 
	 * 服务商网点内部编码
	 **/
	private $serviceCenterCode;
	
	/** 
	 * 服务商网点名字
	 **/
	private $serviceCenterName;
	
	/** 
	 * 服务预约时间
	 **/
	private $serviceDate;
	
	/** 
	 * 单元是分
	 **/
	private $serviceFee;
	
	/** 
	 * 服务凭证上传的图片URL链接，多个以;隔开
	 **/
	private $serviceVoucherPics;
	
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

	public function setAfterServiceMemo($afterServiceMemo)
	{
		$this->afterServiceMemo = $afterServiceMemo;
		$this->apiParas["after_service_memo"] = $afterServiceMemo;
	}

	public function getAfterServiceMemo()
	{
		return $this->afterServiceMemo;
	}

	public function setAttribute($attribute)
	{
		$this->attribute = $attribute;
		$this->apiParas["attribute"] = $attribute;
	}

	public function getAttribute()
	{
		return $this->attribute;
	}

	public function setBeforeServiceMemo($beforeServiceMemo)
	{
		$this->beforeServiceMemo = $beforeServiceMemo;
		$this->apiParas["before_service_memo"] = $beforeServiceMemo;
	}

	public function getBeforeServiceMemo()
	{
		return $this->beforeServiceMemo;
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

	public function setCompleteDate($completeDate)
	{
		$this->completeDate = $completeDate;
		$this->apiParas["complete_date"] = $completeDate;
	}

	public function getCompleteDate()
	{
		return $this->completeDate;
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

	public function setIsVisit($isVisit)
	{
		$this->isVisit = $isVisit;
		$this->apiParas["is_visit"] = $isVisit;
	}

	public function getIsVisit()
	{
		return $this->isVisit;
	}

	public function setPhoneImei($phoneImei)
	{
		$this->phoneImei = $phoneImei;
		$this->apiParas["phone_imei"] = $phoneImei;
	}

	public function getPhoneImei()
	{
		return $this->phoneImei;
	}

	public function setServiceCenterCode($serviceCenterCode)
	{
		$this->serviceCenterCode = $serviceCenterCode;
		$this->apiParas["service_center_code"] = $serviceCenterCode;
	}

	public function getServiceCenterCode()
	{
		return $this->serviceCenterCode;
	}

	public function setServiceCenterName($serviceCenterName)
	{
		$this->serviceCenterName = $serviceCenterName;
		$this->apiParas["service_center_name"] = $serviceCenterName;
	}

	public function getServiceCenterName()
	{
		return $this->serviceCenterName;
	}

	public function setServiceDate($serviceDate)
	{
		$this->serviceDate = $serviceDate;
		$this->apiParas["service_date"] = $serviceDate;
	}

	public function getServiceDate()
	{
		return $this->serviceDate;
	}

	public function setServiceFee($serviceFee)
	{
		$this->serviceFee = $serviceFee;
		$this->apiParas["service_fee"] = $serviceFee;
	}

	public function getServiceFee()
	{
		return $this->serviceFee;
	}

	public function setServiceVoucherPics($serviceVoucherPics)
	{
		$this->serviceVoucherPics = $serviceVoucherPics;
		$this->apiParas["service_voucher_pics"] = $serviceVoucherPics;
	}

	public function getServiceVoucherPics()
	{
		return $this->serviceVoucherPics;
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
		RequestCheckUtil::checkMaxLength($this->attribute,1024,"attribute");
		RequestCheckUtil::checkMaxLength($this->comments,256,"comments");
		RequestCheckUtil::checkMaxLength($this->contactName,50,"contactName");
		RequestCheckUtil::checkMaxLength($this->contactPhone,20,"contactPhone");
		RequestCheckUtil::checkMaxLength($this->serviceCenterCode,50,"serviceCenterCode");
		RequestCheckUtil::checkMaxLength($this->serviceCenterName,50,"serviceCenterName");
		RequestCheckUtil::checkMaxLength($this->serviceVoucherPics,1024,"serviceVoucherPics");
		RequestCheckUtil::checkNotNull($this->updateDate,"updateDate");
		RequestCheckUtil::checkNotNull($this->updater,"updater");
		RequestCheckUtil::checkNotNull($this->workcardId,"workcardId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
