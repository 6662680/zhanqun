<?php
final class DateUtils {
	
	/**
	 * 获取当前时间戳：yyyyMMddhhmmssSSS
	 *
	 * @return string
	 */
	public static function withMicrosecond() {
		list ( $usec, $sec ) = explode ( " ", microtime () );
		return date ( "YmdHis" ) . sprintf ( "%03d", intval ( $usec * 1000 ) );
	}
}