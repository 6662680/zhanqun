<?php

$config = array (	
	//应用ID,您的APPID。
	'app_id' => "2017030206007505",

	//商户私钥，您的原始格式RSA私钥
	'merchant_private_key' => "MIIEpAIBAAKCAQEA5trGyOVmmhgQY18ylVRyVwtM4UpWvF251PlYB4OeSmS9/hjiNuI4bWfRhhg6XDtQEYRr4fBTkcdo+feBstLnDX2og7OmKc4Zw74W4h2E/WttEsNSrMU7CmED30EK8yScDObLn3C+Lw5n3S3f72itzwkJH0Vz16zZHbd7X3mlCERfg69h6bTNB7cBSQx/+jmIZfXcgFE1qRIHQd1wYxz845MBnBlZggWFjtxp3Gligyu8gvmUfJhE+vTDpMRw7zy1k2MEOml4POGvQlgb2csL7Zr4nKM79NoZkViltWbTUxjKSLJCABSRpDJjX1ZFaSTo30MAY8WqjWM1RTAq7At5NQIDAQABAoIBAC/nD76aE2tAOQ5Vr6pa5or3VlGdWlgl3qy5vLF6dzsaom36fd5DnM/e7hQ0LM/8osWvw68TblPenALaf/g6WSVsgK4rdfxQvvnmFNuAoprvUqfFDf+5wWGK9gG6fmorm5H1xiTkgSJEi/rSTRhhkUdXqaPqhnonbLoE273ZAQywtnRGpV4PeHGoA5WWcV21FAiOOeYHoRSj5z9j3Vw1H/JAkCST0qkLVLERAz3B5tS/5LSEG2Drq0bMWW8mruUMyFn3OVGIB3B0v1lBO/TMj+7TKJ14IYdsmU8CXvgfvKgBCgSxT1EAGCdfv5iJS4yv2wsAHjvAcxs/DWko83uaEeECgYEA9/yI0Kz7U+Eubwym86heNMAj1LpTmVOefKuv7B9pDjjUMr+YOiD+KiuZPoJmhlrGRPTcjjeyX6jGJiVmFg+miYFKIZM8TmSmbX8a7oe6S7E3nFETVfVFxHFjeiwDfn/Lpjb/BEgOAlSjWuhMHq8PcVysDsVBOj9W7/avx5V2Fl0CgYEA7lCE1JRWtVAD1Q2UHzBb3j5fZZxb3WzerRF/aORFLrPFjwrQdxFmRvSKMKB9LFb0nzYl6W3I66f+OxcdLqifukdhNJCZS1gHwW7kc4SekGT94s5hltRCybvVgch5z/ucd/oRl2UYGgqkfnkX95by7Z7OHYCl7k5FVeMsgs2VkLkCgYEA1gxsK3KkHOqpIcFR+c7CQdX3F48cyaObkRDAcJdMHrJ+tq8ZvlLsD4pCY+o6hI2lxa91EGyS0m7jWdm+HBy2KsomKoTj8OZ/oNOtc2ZEL0FwNsTkY7Wp2r3kl3eWLIIyTe36gL+RGAHmXnlT+sgwFDFhcf5hJt21NLtBTox7uSECgYAvYabnNQ8AQYZhh6+Ze93oE/KXmzzQi2LCeiYgzrHKlUeEJxxHcBgrp1cKT/5TIH7GYNesz30RDXeIfBH7Qt9vA94Zu14fEsOH++pD5Ww5PTsVKv9QI6ebwFK02Q0PAvGbEQcKWLkwBEmsM5tGSKbIA7jrOLbE6J70cpRY9VL1UQKBgQC0+uLAb88iMIeb7cKxcuvIzEsNA6navtHc5ormiDnCf50DbuU8Om4KbkG6azLKWo1Ozgm+YJzzhd/DEoLJIOV6mUmTq0zn0QVPBFa1inH8LsHMim8VVGtuE085W27Th4+iJB4G8SPkbf2R6WnJG360upVt3zHQunzxTKknMy842A==",

	//异步通知地址
	/** 'notify_url' => "http://testadmin.shanxiuxia.com/api/Alipaywap/notify", */
	'notify_url' => "http://api.shanxiuxia.com/api/Alipaywap/notify",

	//同步跳转
	/** 'return_url' => "http://testadmin.shanxiuxia.com/api/Alipaywap/successPage", */
	'return_url' => "http://api.shanxiuxia.com/api/Alipaywap/successPage",

	//编码格式
	'charset' => "UTF-8",

	//签名方式
	'sign_type'=>"RSA2",

	//支付宝网关
	'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

	//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
	'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxPVIR8deVr66C8xOFZvdp5ByH6fNKfUMdQHjrI6q5RiXEe7oobk+CwxsZaBXRW8hy/L0Yo7QXWJM/BseS3RTEp1aZOrPCMrIP2zXc+LUobwx2+vx73Aanv162z5LkMNwCr1oozSamtqKzB5IEYW1nizVLXG5i48AVVBI40d77dNXsZHc6eRRrXmdq3AxeTh0fBCeHv04LcuNt/N9c5zka2Dwh6xQF/ZlcJQ4E2gwFfnaezUYzy4TzsGhO9c4eM2hTCgdcmPg8wuxwDg5T8bFTr+/oWXhPsd16RGbxlSqupLAKKsHoF+dw/kGiOLlWkPZxXbQV+cMhEtPEedKcRpNFwIDAQAB",
);