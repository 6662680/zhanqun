<?php
$_dir = dirname ( __DIR__ ) . '/';
require_once $_dir . 'utils/DateUtils.php';
require_once $_dir . 'utils/EnvUtils.php';
require_once $_dir . 'utils/SignatureUtils.php';
require_once $_dir . 'utils/HttpUtils.php';
final class ZhongAnApiClient {
	private $_env;
	private $_appKey;
	private $_privateKey;
	private $_url;
	private $_zaPublicKey;
	private $_charset;
	private $_signType;
	private $_version;
	private $_format;
	private $_timestamp;
	private $_serviceName;
	
	// 构造函数
	function __construct($env, $appKey, $privateKey, $serviceName, $charset = "UTF-8", $signType = "RSA", $version = "1.0.0", $format = "json") {
		$this->_env = $env;
		$this->_appKey = $appKey;
		$this->_privateKey = $privateKey;
		$this->_serviceName = $serviceName;
		$this->_charset = $charset;
		$this->_signType = $signType;
		$this->_version = $version;
		$this->_format = $format;
	}
	
	/**
	 * 调用服务
	 *
	 * @param $bizParams 业务参数        	
	 */
	public function call($bizParams) {
		$this->_init ();
		$_bizContent = SignatureUtils::encrypt ( $bizParams, $this->_zaPublicKey );
		$_allParams = array (
				"serviceName" => $this->_serviceName,
				"appKey" => $this->_appKey,
				"format" => $this->_format,
				"signType" => $this->_signType,
				"charset" => $this->_charset,
				"version" => $this->_version,
				"timestamp" => $this->_timestamp,
				"bizContent" => $_bizContent 
		);
		$_signRequest = SignatureUtils::sign ( $_allParams, $this->_privateKey );
		$_allParams ["sign"] = $_signRequest;
		$_result = HttpUtils::doCurlPost ( $this->_url, $_allParams, true );
		
		$_signResponse = $_result ["sign"];
		unset ( $_result ["sign"] );
		
		$_signCheckRst = SignatureUtils::checkSign ( $_result, $_signResponse, $this->_zaPublicKey );
		if ($_signCheckRst != 1) {
			throw new Exception ( "本地验签失败" );
		}
		
		$_decryptedData = SignatureUtils::decrypt ( $_result ["bizContent"], $this->_privateKey );
		$_result ["bizContent"] = isset ( $_result ["bizContent"] ) ? json_decode ( $_decryptedData, true ) : false;
		return $_result;
	}
	
	// 初始化
	private function _init() {
		$this->_timestamp = DateUtils::withMicrosecond ();
		$_envUtils = new EnvUtils ( $this->_env );
		$this->_url = $_envUtils->__get ( "_url" );
		$this->_zaPublicKey = $_envUtils->__get ( "_publicKey" );
	}
}