<?php
/**
 * ALIPAY API: alipay.open.auth.zhima.data.batch.feedback request
 *
 * @author auto create
 * @since 1.0, 2015-12-23 20:45:20
 */
class AlipayOpenAuthZhimaDataBatchFeedbackRequest
{
	/** 
	 * 业务扩展参数
	 **/
	private $bizExtParams;
	
	/** 
	 * 单条数据的数 据列,多个列 以逗号隔开
	 **/
	private $columns;
	
	/** 
	 * 文件内容
	 **/
	private $file;
	
	/** 
	 * 反馈文件的数据 编码
	 **/
	private $fileCharset;
	
	/** 
	 * 文件描述
	 **/
	private $fileDescription;
	
	/** 
	 * 反馈的数据类 型,目前只支 持 json_data
	 **/
	private $fileType;
	
	/** 
	 * 主键列 主键 列使用反馈字 段进行组合, 也可以使用反 馈的某个单字 段(确保主键 稳定,而且可 以很好的区分 不同的数据)。 例如 order_no#pay_ month 或 者 order_no#bill_ month 组合, 对于一个order_no 只会 有一条数据的 情况,直接使 用 order_no 作 为主键列
	 **/
	private $primaryKeyColumns;
	
	/** 
	 * 本次反馈的数 据条数
	 **/
	private $records;
	
	/** 
	 * 芝麻系统中配 置的值,由芝 麻信用提供, 需要匹配,测 试反馈和正式 反馈使用不同 的 type_id
	 **/
	private $typeId;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setBizExtParams($bizExtParams)
	{
		$this->bizExtParams = $bizExtParams;
		$this->apiParas["biz_ext_params"] = $bizExtParams;
	}

	public function getBizExtParams()
	{
		return $this->bizExtParams;
	}

	public function setColumns($columns)
	{
		$this->columns = $columns;
		$this->apiParas["columns"] = $columns;
	}

	public function getColumns()
	{
		return $this->columns;
	}

	public function setFile($file)
	{
		$this->file = $file;
		$this->apiParas["file"] = $file;
	}

	public function getFile()
	{
		return $this->file;
	}

	public function setFileCharset($fileCharset)
	{
		$this->fileCharset = $fileCharset;
		$this->apiParas["file_charset"] = $fileCharset;
	}

	public function getFileCharset()
	{
		return $this->fileCharset;
	}

	public function setFileDescription($fileDescription)
	{
		$this->fileDescription = $fileDescription;
		$this->apiParas["file_description"] = $fileDescription;
	}

	public function getFileDescription()
	{
		return $this->fileDescription;
	}

	public function setFileType($fileType)
	{
		$this->fileType = $fileType;
		$this->apiParas["file_type"] = $fileType;
	}

	public function getFileType()
	{
		return $this->fileType;
	}

	public function setPrimaryKeyColumns($primaryKeyColumns)
	{
		$this->primaryKeyColumns = $primaryKeyColumns;
		$this->apiParas["primary_key_columns"] = $primaryKeyColumns;
	}

	public function getPrimaryKeyColumns()
	{
		return $this->primaryKeyColumns;
	}

	public function setRecords($records)
	{
		$this->records = $records;
		$this->apiParas["records"] = $records;
	}

	public function getRecords()
	{
		return $this->records;
	}

	public function setTypeId($typeId)
	{
		$this->typeId = $typeId;
		$this->apiParas["type_id"] = $typeId;
	}

	public function getTypeId()
	{
		return $this->typeId;
	}

	public function getApiMethodName()
	{
		return "alipay.open.auth.zhima.data.batch.feedback";
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
