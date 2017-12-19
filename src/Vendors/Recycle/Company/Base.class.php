<?php

// +------------------------------------------------------------------------------------------ 
// | Author: qikailin <qklandy@gmail.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2017/6/30
// +------------------------------------------------------------------------------------------
namespace Vendors\Recycle\Company;

abstract class Base {

    protected static $_instance = null;
    protected $appid = NULL;
    protected $secret = NULL;

    public static function getInstance()
    {
        if(isset($_instance)) {
            return self::$_instance;
        }

        return self::$_instance = new static();
    }

    public function setBaseConfig($config)
    {
        $this->appid  = $config['appid'];
        $this->secret = $config['secret'];
    }
}