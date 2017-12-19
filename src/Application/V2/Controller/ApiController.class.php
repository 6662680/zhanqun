<?php
/**
 * Created by PhpStorm.
 * User: zhujianping
 * Date: 2017/5/12 0012
 * Time: 上午 11:41
 * Api控制器
 */

namespace V2\Controller;


use V2\Transformer\Transformer;
use Think\Controller\RestController;

class ApiController extends BaseController
{
    private $code = 200;
    public $requestData=null;
    private $msg='';

    public function __construct()
    {


        parent::__construct();
        if(IS_GET){
            call_user_func(array($this,'get'.ucwords(ACTION_NAME)));
        }elseif(IS_POST){
            call_user_func(array($this,'post'.ucwords(ACTION_NAME)));
        } elseif(IS_DELETE){
            call_user_func(array($this,'delete'.ucwords(ACTION_NAME)));
        }

    }


    public function __call($method,$args){
        $method=strtolower($_SERVER['REQUEST_METHOD']).ucwords(ACTION_NAME);
        if(!method_exists($this,$method)){
            E(L('_ERROR_ACTION_').':'.ACTION_NAME);
        }
    }

    /**
     * 返回code
     * @param $code
     * @return int
     */
    protected function getStatusCode($code)
    {
        return $this->code;
    }

    /**
     * 设置code
     * @param $code
     * @return object
     */
    protected function setStatusCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * 设置msg
     * @param $code
     * @return object
     */
    protected function setMsg($msg='')
    {
        $this->msg = $msg;
        return $this;
    }

    /**
     * 返回错误响应
     * @param $errorMsg 错误提示
     */
    protected function responseError($errorMsg,$code=null)
    {
        $code=$code?$code:$this->code;
        return $this->ajaxReturn(array('status'=>false,'code'=>$code,'msg'=>$errorMsg));
    }

    /**
     * @param $data 响应数据
     * @param Transformer $transformer 响应转化器
     */
    protected function responseSuccess($data=null,Transformer $transformer=null)
    {
        if(is_object($transformer)){
            $data=$transformer->makeTransform($data);
        }
        return $this->ajaxReturn(array(
            'status'=>true,
            'code'=>$this->code,
            'msg'=>$this->msg?$this->msg:'请求成功',
            'data'=>$data
        ));
    }









}