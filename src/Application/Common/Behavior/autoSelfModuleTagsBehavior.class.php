<?php

// +------------------------------------------------------------------------------------------ 
// | Author: qikailin <qklandy@gmail.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2017/7/6.
// +------------------------------------------------------------------------------------------
// | 功能描述:  自动加载自身模块下的tags行为配置
// | 使用说明:  在各模块的基类或自身的构造等方法中调用 \Think\Log::listen('auto_api_tags');
// |           自动加载自己模块下conf/tags.php行为配置, 请勿重复标签，除非你懂得运用，否则会合并多次调用
// +------------------------------------------------------------------------------------------
namespace Common\Behavior;
use Think\Hook;

class autoSelfModuleTagsBehavior
{
    public function run(&$param)
    {
        $path = realpath(APP_PATH.MODULE_NAME);
        if(is_file($path.'/Conf/tags.php')) {
            Hook::import(include $path.'/Conf/tags.php');
        }
    }
}