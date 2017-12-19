<?php
final class SignatureUtils {
	/**
	 * 对参数进行加密
	 *
	 * @param $params 待加密参数        	
	 * @param $publicKey 对端的公钥        	
	 */
	public static function encrypt($params, $publicKey) {
		$_rawData = json_encode ( self::filterParam ( $params ) );
		
		$_encryptedList = array ();
		$_step = 117;
		
		for($_i = 0, $_len = strlen ( $_rawData ); $_i < $_len; $_i += $_step) {
			$_data = substr ( $_rawData, $_i, $_step );
			$_encrypted = '';
			
			openssl_public_encrypt ( $_data, $_encrypted, $publicKey );
			$_encryptedList [] = ($_encrypted);
		}
		$_data = base64_encode ( join ( '', $_encryptedList ) );
		return $_data;
	}
	
	/**
	 * 对参数进行加签
	 *
	 * @param $params 待加签参数        	
	 * @param $privateKey 自己的私钥        	
	 */
	public static function sign($params, $privateKey) {
		ksort ( $params );
		$_signStr = json_encode ( $params );
		$_signStr = stripslashes ( $_signStr );
		
		$_privateKeyId = openssl_get_privatekey ( $privateKey );
		openssl_sign ( $_signStr, $_data, $_privateKeyId );
		openssl_free_key ( $_privateKeyId );
		$_data = base64_encode ( $_data );
		
		openssl_free_key ( $_privateKeyId );
		
		return $_data;
	}
	
	/**
	 *
	 * @param unknown $params        	
	 * @param unknown $sign        	
	 * @param unknown $publicKey        	
	 */
	public static function checkSign($params, $sign, $publicKey) {
		$_params = self::filterParam ( $params );
		ksort ( $_params );
		
		$_publicKeyId = openssl_get_publickey ( $publicKey );
		
		$_data = json_encode ( $_params );
		$_data = stripslashes ( $_data );
		$_result = openssl_verify ( $_data, base64_decode ( $sign ), $_publicKeyId, "sha1WithRSAEncryption" );
		openssl_free_key ( $_publicKeyId );
		return $_result;
	}
	
	/**
	 * 对参数进行解密
	 *
	 * @param $encryptedData 待解密参数        	
	 * @param $privateKey 自己的私钥        	
	 */
	public static function decrypt($encryptedData, $privateKey) {
		$_encryptedData = base64_decode ( $encryptedData );
		
		$_decryptedList = array ();
		$_step = 128;
		if (strlen ( $privateKey ) > 1000) {
				$_step = 256;
		}
		for($_i = 0, $_len = strlen ( $_encryptedData ); $_i < $_len; $_i += $_step) {
			$_data = substr ( $_encryptedData, $_i, $_step );
			$_decrypted = '';
			openssl_private_decrypt ( $_data, $_decrypted, $privateKey );
			$_decryptedList [] = $_decrypted;
		}
		
		return join ( '', $_decryptedList );
	}
	
	/**
	 * 保证只传有值的参数
	 *
	 * @param unknown $param        	
	 */
	public static function filterParam($params) {
		$_result = array ();
		foreach ( $params as $_key => $_value ) {
			// 没有值的
			if (empty ( $_value ) && $_value != 0) {
				continue;
			}
			
			if (is_array ( $_value )) {
				$_result [$_key] = json_encode ( $_value );
			} else {
				$_result [$_key] = $_value ? $_value : '';
			}
		}
		return $_result;
	}
}