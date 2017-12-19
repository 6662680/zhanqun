<?php
/**
 * User: zhujianping
 * Date: 2017/5/8 0008
 * Time: 下午 16:20
 * API 认证行为
 */
namespace Api\Behavior;

class tokenBehavior
{
    private $secret_key='9dc5de36dc343fb5ae1e86863150cc82';
    private $sign=null;
    private $isAuth=true;
    private $authIp=['192.168.1.1516','192.168.1.211','121.41.24.216'];
    public function run(&$params)
    {
        if ($this->isAuth) {
            $this->auth();
        }
        return true;
    }

    /**
     * 对IOS验证签名,对webapp验证IP
     */
    private function auth()
    {



        if(in_array($_SERVER['REMOTE_ADDR'],$this->authIp)){
            return true;
        }
        if (!I('request.timestamp')) {
            $this->error(1001,'timestamp参数不存在');
        }
        if (!I('request.sign')) {
            $this->error(1001,'sign参数不存在');
        }
        if(time()-I('request.timestamp')>300){
            $this->error(1003,'当前请求过期');
        }
        $this->signature();
        if($this->sign!=I('request.sign')){
            $this->error(1002,'签名认证失败');
        }

    }

    /**
     * 生成签名
     */
    private function signature()
    {
        $params=I('param.');
        $format='';
        ksort($params);
        foreach($params as $k=>$v){
            if(in_array($k,['fieldstyle','sign'])){
                continue;
            }
            $format.=$k.$v;
        }

        $this->sign=strtoupper(md5($this->secret_key.base64_encode($format).strrev($this->secret_key)));
    }

    private function error($code=1001,$msg='接口调用失败')
    {
        $this->jsonFormat(['status'=>false,'code'=>$code,'msg'=>$msg]);
    }

    private function jsonFormat($data)
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }


}