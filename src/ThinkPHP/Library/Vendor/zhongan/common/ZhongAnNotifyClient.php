<?php
$_dir = dirname ( __DIR__ ) . '\\';
require_once $_dir . 'utils\\DateUtils.php';
require_once $_dir . 'utils\\EnvUtils.php';
require_once $_dir . 'utils\\SignatureUtils.php';

final class ZhongAnNotifyClient {
	
	private $_privateKey;
	private $_zaPublicKey;
	
	
	// 构造函数
	function __construct($env, $privateKey) {
		$_envUtils = new EnvUtils ( $env );
		
		$this->_zaPublicKey = $_envUtils->__get ( "_publicKey" );
		$this->_privateKey = $privateKey;
		
	}


	/**
	 * 调用服务
	 *
	 * @param $bizParams 业务参数        	
	 */
	public function parseNotifyRequest($_result) {
		
		
		$_signResponse = $_result ["sign"];
		unset ( $_result ["sign"] );
		
		$_signCheckRst = SignatureUtils::checkSign ( $_result, $_signResponse, $this->_zaPublicKey );
		if ($_signCheckRst != 1) {
			throw new Exception ( "本地验签失败" );
		}
		$_decryptedData = SignatureUtils::decrypt ( $_result ["bizContent"], $this->_privateKey );
		return $_decryptedData;
	}
	
}