<?php

// +------------------------------------------------------------------------------------------ 
// | Author: qikailin <qklandy@gmail.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2017/6/30
// +------------------------------------------------------------------------------------------
namespace Vendors\Recycle;

use Think\Exception;

class Recycle {

    private static $_statics = [];
    private static $_nowCompany = NULL;

    public static function getCompanyObject($company, $config){

        $class = self::getClass($company);
        $companyObject = $class::getInstance();

        if(!self::$_statics[$company]) {
            self::$_statics[$company] = $companyObject;
        }

        self::Location($company);

        return self::$_statics[$company];
    }

    private static function getClass($company){

        return __NAMESPACE__ . "\\Company\\". ucfirst($company);

    }

    /**
     *
     * 定位当前的回收合作商对象
     * @param $company
     * @return mixed
     */
    public static function Location($company){

        return self::$_nowCompany = self::$_statics[$company];
    }

    public static function __callStatic($method, $args){

        if(!isset(self::$_nowCompany)) {
            throw new Exception("当前未定位合作商");
            return false;
        }

        if(!method_exists(self::$_nowCompany, $method)) {
            throw new Exception("当前定位". get_class(self::$_nowCompany) ."合作商方法不存在：" . $method);
            return false;
        }

        return self::$_nowCompany->$method($args);

    }
}