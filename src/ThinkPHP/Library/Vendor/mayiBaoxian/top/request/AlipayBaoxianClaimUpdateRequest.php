<?php
/**
 * TOP API: alipay.baoxian.claim.update request
 * 
 * @author auto create
 * @since 1.0, 2017.05.05
 */
class AlipayBaoxianClaimUpdateRequest
{
	/** 
	 * 业务数据
	 **/
	private $bizData;
	
	/** 
	 * 业务来源
	 **/
	private $bizSource;
	
	/** 
	 * 附件列表
	 **/
	private $claimAttachments;
	
	/** 
	 * 理赔金额(单位为分)
	 **/
	private $claimFee;
	
	/** 
	 * 理赔单号
	 **/
	private $claimNo;
	
	/** 
	 * 理赔外部业务单号
	 **/
	private $claimOutBizNo;
	
	/** 
	 * 外部业务单号
	 **/
	private $outBizNo;
	
	/** 
	 * 保单业务单号
	 **/
	private $policyBizNo;
	
	/** 
	 * 进度列表
	 **/
	private $progressList;
	
	/** 
	 * 标准产品ID
	 **/
	private $spNo;
	
	private $apiParas = array();
	
	public function setBizData($bizData)
	{
		$this->bizData = $bizData;
		$this->apiParas["biz_data"] = $bizData;
	}

	public function getBizData()
	{
		return $this->bizData;
	}

	public function setBizSource($bizSource)
	{
		$this->bizSource = $bizSource;
		$this->apiParas["biz_source"] = $bizSource;
	}

	public function getBizSource()
	{
		return $this->bizSource;
	}

	public function setClaimAttachments($claimAttachments)
	{
		$this->claimAttachments = $claimAttachments;
		$this->apiParas["claim_attachments"] = $claimAttachments;
	}

	public function getClaimAttachments()
	{
		return $this->claimAttachments;
	}

	public function setClaimFee($claimFee)
	{
		$this->claimFee = $claimFee;
		$this->apiParas["claim_fee"] = $claimFee;
	}

	public function getClaimFee()
	{
		return $this->claimFee;
	}

	public function setClaimNo($claimNo)
	{
		$this->claimNo = $claimNo;
		$this->apiParas["claim_no"] = $claimNo;
	}

	public function getClaimNo()
	{
		return $this->claimNo;
	}

	public function setClaimOutBizNo($claimOutBizNo)
	{
		$this->claimOutBizNo = $claimOutBizNo;
		$this->apiParas["claim_out_biz_no"] = $claimOutBizNo;
	}

	public function getClaimOutBizNo()
	{
		return $this->claimOutBizNo;
	}

	public function setOutBizNo($outBizNo)
	{
		$this->outBizNo = $outBizNo;
		$this->apiParas["out_biz_no"] = $outBizNo;
	}

	public function getOutBizNo()
	{
		return $this->outBizNo;
	}

	public function setPolicyBizNo($policyBizNo)
	{
		$this->policyBizNo = $policyBizNo;
		$this->apiParas["policy_biz_no"] = $policyBizNo;
	}

	public function getPolicyBizNo()
	{
		return $this->policyBizNo;
	}

	public function setProgressList($progressList)
	{
		$this->progressList = $progressList;
		$this->apiParas["progress_list"] = $progressList;
	}

	public function getProgressList()
	{
		return $this->progressList;
	}

	public function setSpNo($spNo)
	{
		$this->spNo = $spNo;
		$this->apiParas["sp_no"] = $spNo;
	}

	public function getSpNo()
	{
		return $this->spNo;
	}

	public function getApiMethodName()
	{
		return "alipay.baoxian.claim.update";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkMaxListSize($this->progressList,20,"progressList");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
