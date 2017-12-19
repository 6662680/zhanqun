<?php
/**
 * ALIPAY API: alipay.open.app.query.test request
 *
 * @author auto create
 * @since 1.0, 2016-01-27 15:46:14
 */
class AlipayOpenAppQueryTestRequest
{
	/** 
	 * 接口测试
	 **/
	private $bizContent;
	
	/** 
	 * field_file_test
	 **/
	private $fieldFileTest;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setBizContent($bizContent)
	{
		$this->bizContent = $bizContent;
		$this->apiParas["biz_content"] = json_encode($bizContent);
	}

	public function getBizContent()
	{
		return $this->bizContent;
	}

	public function setFieldFileTest($fieldFileTest)
	{
		$this->fieldFileTest = $fieldFileTest;
		$this->apiParas["field_file_test"] = $fieldFileTest;
	}

	public function getFieldFileTest()
	{
		return $this->fieldFileTest;
	}

	public function getApiMethodName()
	{
		return "alipay.open.app.query.test";
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
