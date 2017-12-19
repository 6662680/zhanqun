<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 接口基类 Dates: 2015-09-25
// +------------------------------------------------------------------------------------------

namespace V2\Controller;

use Think\Controller;

class BaseController extends Controller
{
    public function __construct($appkey = null)
    {
        parent::__construct();

        //$this->authentication($appkey);
    }

    private function authentication($appkey = null)
    {

        if (is_null($appkey)) {
            $appkey = trim(I('param.appkey'));
        }

        /** 安全验证 $key = 'weishishanxiuxia'; */
        if ($appkey != '9dc5de36dc343fb5ae1e86863150cc82') {
            $result = array();
            $result['status'] = 0;
            $result['info'] = '权限认证错误！';

            $this->ajaxReturn($result);
        };
    }

    public function _callBack($result = NULL)
    {
        header("Access-Control-Allow-Origin:*");
        $this->ajaxReturn(array('code' => 200, 'data' => $result), 'json');
    }

    public function _error($code,$msg)
    {
        header("Access-Control-Allow-Origin:*");
        $this->ajaxReturn(array('code' => $code, 'msg' => $msg), 'json');
    }
    
    /**
     * 上传
     *
     * @return void
     */
    public function upload()
    {
        $upload = new \Think\Upload();
        $upload->maxSize = 10485760;
        $upload->exts = explode(',', 'jpg,gif,png,jpeg');
        $upload->rootPath = './upload/';
        $upload->saveName = array('uniqid','');
        $upload->autoSub = true;
        $upload->subName = array('date','Ymd');
        $info = $upload->upload();
    
        $rst = array();
    
        if (!$info) {
            $rst['success'] = false;
            $rst['errorMsg'] = $upload->getError();
        } else {
            $rst['success'] = true;
            $rst['info'] = $info;
        }
    
        return $rst;
    }
}
