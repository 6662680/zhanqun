<?php
final class LogUtils {
	
	// 是否调试模式, 线上需要关闭, true|打开, false|关闭
	const DEBUG = false;
	
	/**
	 * 打印日志
	 * 
	 * @param unknown $msg        	
	 */
	public static function log($msg) {
		if (self::DEBUG === true) {
			printf ( "debug_log:%s - %s\n", date ( 'Y-m-d H:i:s', time () ), $msg );
		}
	}
	
	/**
	 * 打印error日志
	 * 
	 * @param unknown $msg        	
	 * @param string $category        	
	 */
	public static function logError($msg, $category = 'zhongansdk') {
		self::log ( $msg );
		if (class_exists ( 'Logger' ) && method_exists ( 'Logger', 'logError' )) {
			Logger::logError ( $msg, $category );
		}
	}
}