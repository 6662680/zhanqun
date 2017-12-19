<?php
date_default_timezone_set ( 'PRC' ); // 设置中国时区
runDemo ();

// 运行DEMO
function runDemo() {
	$_dir = dirname ( __DIR__ ) . '\\';
	require_once $_dir . 'common\\ZhongAnApiClient.php';
	
	// TODO 指定环境 iTest:测试环境; uat:预发环境; prd:生产环境
	$_env = "uat";
	
	// TODO 开发者的appKey，由众安提供
	$_appKey = "d7ba23de065aac3479e86dd0bcd51f90";
	// d7ba23de065aac3479e86dd0bcd51f90 uat
	// 3ee877531b114794eb18bbcff859c482 iTest
	
	// TODO 开发者的私钥，由开发者通过openssl工具生成，不需要PKCS8转码
	$_privateKey = "-----BEGIN RSA PRIVATE KEY-----
MIICXwIBAAKBgQD23llSmXxQlEZSRyQID9sJXIqX4XNjoeoU2GWlc+B2XqwBvqL1
Jg9bGuJl//Miw9JyrE9SnOBfxGaBIz80RqD7GDuc+a7M8OsZYELZJ5SuhwcdBozb
xMj63E0QxhGdeeSfDKDY9QatZ54dkPM57l4uNrLB9/fI2y3AgKreIeJ4ZQIDAQAB
AoGBALY21i1OluCPIPyX//NnaKAXS0Dhqp7uou2x8AzYY+Ra6pD7GiLibdEsHdF1
wwt1CH+VyZLLsh1dxN8qmftG6ogYaUxqajem8zbilWev0R0eViPbsEVssm3V7i6b
OygPFCMIsgD2EbP8zXOZ1DIZTX6rnbLrR62vLAofSR+/PA/dAkEA/15RIG3k8yEC
Fct0KB2U5XSwVEc4N75Dbdk5//ct0HREFHpwGf7bnn6wqo9o54RlJlp3JVTqMs+C
HymlJDZ1AwJBAPd6poLQAzFDG9k6OyVdIbBUoF7S5IH3TqN25nqbGqbhuBo+A8C6
5EIQMpQOB7rMbZJRz9DsdUZU2TPlLSfXXHcCQQDV5rvPjR18ZYaomN24CGdC98YH
Igy97HnwlkcV14ahl/G6sYAa1jZBgV8bzroRSv2q7ZXlSEZPvy8ASVLRjWffAkEA
vt7X4fhxHeN2bRoeV/j2bLs4XSoml56YBjdEF7fc3G0mwwalelYqilFX0RzpFUdq
EvoKYEafRLlYNFBDfYD6jQJBANS/jvYBZPcaQUR8Gs69jp+Wr6hOm6pk6BF0hz/h
Y6BbAVoysred7QzIOH6flqaESf8jM6Cxh9u/FdGD15/mPKY=
-----END RSA PRIVATE KEY-----";
	
	// TODO 指定服务名，由众安提供
	$_serviceName = "zhongan.user.person.addByIdentityNo";
	
	// TODO 业务参数，字段信息参照接口文档
	$_params = array (
			"userName" => "肖建强",
			"identityNo" => "362426198910198411" 
	);
	
	$_client = new ZhongAnApiClient ( $_env, $_appKey, $_privateKey, $_serviceName );
	
	$_response = $_client->call ( $_params );
	var_dump ( $_response ["bizContent"] );
}