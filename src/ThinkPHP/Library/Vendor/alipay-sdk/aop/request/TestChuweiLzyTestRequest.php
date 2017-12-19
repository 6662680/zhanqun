<?php
/**
 * ALIPAY API: test.chuwei.lzy.test request
 *
 * @author auto create
 * @since 1.0, 2015-03-19 15:21:05
 */
class TestChuweiLzyTestRequest
{
	/** 
	 * dd
	 **/
	private $dd;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setDd($dd)
	{
		$this->dd = $dd;
		$this->apiParas["dd"] = json_encode($dd);
	}

	public function getDd()
	{
		return $this->dd;
	}

	public function getApiMethodName()
	{
		return "test.chuwei.lzy.test";
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
