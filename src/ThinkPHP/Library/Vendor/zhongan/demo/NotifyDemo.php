<?php
date_default_timezone_set ( 'PRC' ); // 设置中国时区
runDemo ();

// 运行DEMO
function runDemo() {
	$_dir = dirname ( __DIR__ ) . '\\';
	require_once $_dir . 'common\\ZhongAnNotifyClient.php';
	
	// TODO 指定环境 iTest:测试环境; uat:预发环境; prd:生产环境
	$_env = "dev";
	
	// TODO 开发者的appKey，由众安提供
	$_appKey = "d7ba23de065aac3479e86dd0bcd51f90";
	// d7ba23de065aac3479e86dd0bcd51f90 uat
	// 3ee877531b114794eb18bbcff859c482 iTest
	
	// TODO 开发者的私钥，由开发者通过openssl工具生成，不需要PKCS8转码
	$_privateKey = "-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAySacrAUb7qZp8uCQNU4qlYVX/n6TIozIqmkud3CBF33Jf1jA
dggXZaukGOeVjSnHBGV5DZirMokbk1nYdC7yyotcR/Ex2tZIO2fT2YXcFwFW1ydQ
gwXTTzTHWmNb8FeWQo/Bl+3Pbe+s4XcohbsXsAkxTOLCI1CrmIp5Y4lcjQmUukP0
aTHUBTrZPwG/Ha21xtFS4nxiYgL8oRFJD3cvDW/CGKwas4IiY0tnYRZkWjOZohut
ISs8HqRhaqj6YF6f1u0u1Z6ER2ypZsMx6yK1f/sDIDXcH+HPSsTxQepWq3G6rTS2
1yckHP/AA1w3o7e/qOf9ObNbeyqpeoEACFHbtwIDAQABAoIBADY1n7eEUPjKBHee
KX8UJ8nP+9zsQ77l7hZu7kTmHwQztCALzSKCH7wYu8qybs1yWt5Gco9Fe63M4Y+e
gQUeufP4BtCkoej2ItVPr/pJZv0UMVEemUYWRdJsyOUFLfvhvu8FSwkk3+gi1ZaV
xGf1+fZJI+4ylltqHDSp7Pj0ResEFRsknVfYRfMMKUY65G5XOyi17XaqSvaptPdF
8WvnqiEe1X10qlKfR2cLr6f6+VWiswGkdMkDpdsj4xAOt8PiZHXHLkG5nG2yajD7
BPo1Neir2BoAoDyERpOhZyVz0JGYLiKtAmzQyCi5pv/MdbVMtGQ4LmR9+b4QJTuj
AtQUWcECgYEA+hI7d6C6N7Ur80kkAsX3LSc+pRJrLg3zdxMY2qqDn98b9wbV47mQ
TNTWF3GsLQY8sMX1dxzRQWq7QNQwEvz6H/9Y82JwTK63wWhz7TnBKUuH6KN8Zmbi
y8Ru9ZA7nE5yayXa5dH8ymbdfRHrjqVSvvS/7wpHJxTXAob9wbB2YGcCgYEAzet3
EpcpsRQblzif+uHvvF6BoYq6w+VfKMa3oZc7MKqHwuQA1NwQIIu3r7gQ8MoX47lX
bYJcBl+zsrEslDXvlSV6JE3CB16DkaUBseelNLB/CSAJaFAxunID2MAMCucWy6V0
H1JJf89T7FyPvkZljtnzFYJ4YGd1fgWvYv+KWDECgYEAv9rNFPKS67prhbNPlXEc
OeAqB6sh36uEZo6YOusnILijU9pCrvvm4YtI1aO4XLK0TUsTD9dkE7Q3BX3FhEvh
+jqFm/SiA7Ln99KTXiU5NN1l3+3NuyKkTYDfQDbwBPOKhPn9/uQj9YUF2Lau6jmc
SjSsLFDOVgV/D8lRVhB0QXECgYBWWxUIetRFKsWXWWvd7rp3KLr8YA2K8bpCg3On
FKEB+8ILfUrL+a3ZuD5ENtED6fyyx5telXi6Q5A8tAiZ7zSWO61JZEKmjIBop+Za
EWc6/XmI/iJz2I3CPuZWE9P5DoiExtI4AG5KZ0wup3KwvR0CF2zV7G1HIJwpSYEP
51LBQQKBgQCgD9o1ndgHVMKPa9yNd54eW856YIDrzhIoCiyPNwXa5VmNZBuvtH+W
eN1JDKg8PMzZouFDzOLnmfoQi2STCe7BVHOa1JapDw2CtLcWaZmAzS2iCfvjJ/cr
FfjJQITimcCMrhSO0+loBy87YmSFJKWg5KRiP/mSO7PuFx31rRlIng==
-----END RSA PRIVATE KEY-----";
	
	
	// TODO 业务参数，字段信息参照接口文档
	$_params = array (
			   "bizContent" => "Xokt3z1C4CGv7yF1krk0DfjNbm8dCEM/9NQGTg38daz2gZynSdANZLXptm8q1gK81q7GjjqRCIW3RLoPrD0jMtJiUn5V66lYPqDM3OML3u9FaW8zS/ZMr767cVJHsKS17TMgyb9H6Gl0cEVZ0w7HhKFUUYJwgRPiBUHMNwhgzrf19jNtsFjekpCEHOXzO6MvVHEWTeAX2/PpwnAJM0MND4bHyih2D4/HI0ijqyBys6nXh804PUwlR2mYgzaM9cX+gtIDAKJAX3ILFsnc8qWMgMWK8IFUgHMILjBcZmurG2dgDP104eLrONpySCSfQVZi6hDSqUdOxCLXZG7FkoHKyA==",
    "charset" => "UTF-8",
    "format" => "json",
    "serviceName" => "promotePolicyNotify",
    "sign" => "AqjYKLV8Lv0jX4ZEUueWYkGPGhhAcjdJ56VjyBjh3RTO1Tpx5802ggsgG6CMSWHPZ3gikkuT7iZTZo02+8/Mo1+FZSQByb8wC+64Zs68EI7K/wvXHKm7dO7il0t+pueVttQwRrPNpVNI5HF4kmAsquLK4Daj6b6iJJXERcgHHsg=",
    "signType" => "RSA",
    "timestamp" => "20160706202716442"
	);
	
	$_client = new ZhongAnNotifyClient ( $_env,  $_privateKey );
	
	$_response = $_client->parseNotifyRequest ( $_params );
	echo $_response;
}