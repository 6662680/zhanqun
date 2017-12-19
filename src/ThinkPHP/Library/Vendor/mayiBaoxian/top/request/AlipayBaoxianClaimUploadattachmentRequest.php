<?php
/**
 * TOP API: alipay.baoxian.claim.uploadattachment request
 * 
 * @author auto create
 * @since 1.0, 2017.05.05
 */
class AlipayBaoxianClaimUploadattachmentRequest
{
	/** 
	 * 文件字节数组
	 **/
	private $attachmentByte;
	
	/** 
	 * 文件名,必须带后缀名。例如：test.png,test.doc,test.pdf
	 **/
	private $attachmentKey;
	
	/** 
	 * 是否base格式的字节数组
	 **/
	private $base64Bytes;
	
	/** 
	 * 业务来源
	 **/
	private $bizSource;
	
	/** 
	 * 外部业务号，唯一
	 **/
	private $outBizNo;
	
	/** 
	 * 保单外部业务单号
	 **/
	private $policyBizNo;
	
	/** 
	 * 标准产品ID
	 **/
	private $spNo;
	
	/** 
	 * 上传者用户标识
	 **/
	private $uploadUser;
	
	private $apiParas = array();
	
	public function setAttachmentByte($attachmentByte)
	{
		$this->attachmentByte = $attachmentByte;
		$this->apiParas["attachment_byte"] = $attachmentByte;
	}

	public function getAttachmentByte()
	{
		return $this->attachmentByte;
	}

	public function setAttachmentKey($attachmentKey)
	{
		$this->attachmentKey = $attachmentKey;
		$this->apiParas["attachment_key"] = $attachmentKey;
	}

	public function getAttachmentKey()
	{
		return $this->attachmentKey;
	}

	public function setBase64Bytes($base64Bytes)
	{
		$this->base64Bytes = $base64Bytes;
		$this->apiParas["base64_bytes"] = $base64Bytes;
	}

	public function getBase64Bytes()
	{
		return $this->base64Bytes;
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

	public function setSpNo($spNo)
	{
		$this->spNo = $spNo;
		$this->apiParas["sp_no"] = $spNo;
	}

	public function getSpNo()
	{
		return $this->spNo;
	}

	public function setUploadUser($uploadUser)
	{
		$this->uploadUser = $uploadUser;
		$this->apiParas["upload_user"] = $uploadUser;
	}

	public function getUploadUser()
	{
		return $this->uploadUser;
	}

	public function getApiMethodName()
	{
		return "alipay.baoxian.claim.uploadattachment";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->attachmentByte,"attachmentByte");
		RequestCheckUtil::checkNotNull($this->attachmentKey,"attachmentKey");
		RequestCheckUtil::checkNotNull($this->outBizNo,"outBizNo");
		RequestCheckUtil::checkNotNull($this->uploadUser,"uploadUser");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
