<?php
/**
 * ALIPAY API: alipay.tools.file.upload request
 *
 * @author auto create
 * @since 1.0, 2016-01-27 20:47:01
 */
class AlipayToolsFileUploadRequest
{
	/** 
	 * 要上传的文件内容
	 **/
	private $file;
	
	/** 
	 * file1
	 **/
	private $file2;
	
	/** 
	 * file2
	 **/
	private $file3;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setFile($file)
	{
		$this->file = $file;
		$this->apiParas["file"] = $file;
	}

	public function getFile()
	{
		return $this->file;
	}

	public function setFile2($file2)
	{
		$this->file2 = $file2;
		$this->apiParas["file2"] = $file2;
	}

	public function getFile2()
	{
		return $this->file2;
	}

	public function setFile3($file3)
	{
		$this->file3 = $file3;
		$this->apiParas["file3"] = $file3;
	}

	public function getFile3()
	{
		return $this->file3;
	}

	public function getApiMethodName()
	{
		return "alipay.tools.file.upload";
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
