<?php
/**
 * ALIPAY API: alipay.user.login.info request
 *
 * @author auto create
 * @since 1.0, 2014-08-21 17:15:01
 */
class AlipayUserLoginInfoRequest
{
	/** 
	 * 指定界面按钮的文案和操作类型
	 **/
	private $buttonAction;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $apiVersion="1.0";
	private $notifyUrl;

	
	public function setButtonAction($buttonAction)
	{
		$this->buttonAction = $buttonAction;
		$this->apiParas["button_action"] = $buttonAction;
	}

	public function getButtonAction()
	{
		return $this->buttonAction;
	}

	public function getApiMethodName()
	{
		return "alipay.user.login.info";
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
