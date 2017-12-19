<?php
final class HttpUtils {
	public static function doCurlPost($url, $params, $resultToJson = false) {
		$_curl = curl_init ();
		if (stripos ( $url, "https://" ) !== FALSE) {
			curl_setopt ( $_curl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $_curl, CURLOPT_SSL_VERIFYHOST, false );
		}
		
		if (is_string ( $params )) {
			$_strPOST = $params;
		} else {
			$_strPOST = http_build_query ( $params );
		}
		
		curl_setopt ( $_curl, CURLOPT_URL, $url );
		curl_setopt ( $_curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $_curl, CURLOPT_POST, true );
		curl_setopt ( $_curl, CURLOPT_POSTFIELDS, $_strPOST );
		curl_setopt ( $_curl, CURLOPT_TIMEOUT, 5 );
		
		$_content = curl_exec ( $_curl );
		$_status = curl_getinfo ( $_curl );
		curl_close ( $_curl );
		
		if (intval ( $_status ["http_code"] ) == 200) {
			return $resultToJson ? json_decode ( $_content, true ) : $_content;
		}
		
		return false;
	}
}