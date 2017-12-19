	
	众安保险开放平台开发者SDK用户说明
	版权：众安在线财产保险股份有限公司

────────────────────────────────────
 1、主要功能说明
────────────────────────────────────

开放平台：众安开放平台，详见http://open.zhongan.com/。
网关：众安开放平台网关，基于http协议向开发者提供接入服务。开发者可通过接入网关，调用众安提供的一系列保险业务的服务。
开发者：接入网关的商户、合作方。
ZhongAnOpenSDK：开发者接入网关，需要遵循开放平台自有的安全策略，包括对数据的加解、加签等一系列操作。SDK封装了这些操作，提供统一的方法调用，简化开发者的接入流程。

────────────────────────────────────
 2、接入流程说明
────────────────────────────────────

开发者需要通过openssl工具生成开发者的公钥、私钥，详见https://open.zhongan.com/open/resource/resourceCenter.htm。
	1.生成私钥：genrsa -out rsa_private_key.pem 1024，并回车
	2.生成公钥：输入命令rsa -in rsa_private_key.pem -pubout -out rsa_public_key.pem，并回车

开发者获取众安的公钥和网关地址，详见https://open.zhongan.com/open/resource/resourceCenter.htm。

开发者将开发者的公钥提交给众安（线下方式），众安会根据该公钥生成一个开发者的appKey，并交付给开发者。

众安业务提供方将接口文档和serviceName交付给开发者。

────────────────────────────────────
 3、调用实例
────────────────────────────────────

初始化ZhongAnApiClient实例，需要提供env，appKey，privateKey，serviceName参数。

设置接口参数，接口字段详见业务接口文档。

调用接口服务，获取response，返回的字段信息详见业务接口文档。

调用实例如下（可详见demo/CommonDemo.php）：
		require_once $_dir . 'common\\ZhongAnApiClient.php';
		$_client = new ZhongAnApiClient ( $_env, $_appKey, $_privateKey, $_serviceName );
		// $_env：环境参数，在iTest、uat、prd中取值
		// $_appKey：开发者的appKey。如何获取appKey,请详见“接入流程说明”
		// $_privateKey：开发者私钥。如何生成开发者私钥,请详见“接入流程说明”
		// $_serviceName：接口名称。如何获取serviceName,请详见“接入流程说明”
		
		// 业务参数
		$params = array (
			"userName" => "肖建强",
			"identityNo" => "362426198910198411" 
		);
		
		// 调用服务，获得response。业务返回值从response的bizContent里获取。
		$_response = $_client->call ( $params );


────────────────────────────────────
 4、注意点
────────────────────────────────────

开发者请区分众安各个环境的参数：公钥、网关地址。

开发者请避免在生产环境上进行测试操作，一旦生成数据，将无法修改及回退。


