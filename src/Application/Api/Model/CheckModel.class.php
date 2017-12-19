<?php

// +------------------------------------------------------------------------------------------ 
// | Author: liyang <664577655@qq.com>
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 数据验证模型 Dates: 2016-07-29
// +------------------------------------------------------------------------------------------

namespace Api\Model;
//use Think\Model;
class CheckModel
{
    /*
     * 判断某个数组的某个下标存在且不为false
     * @param $array
     * @param $key
     */
    static public function is_exit($array,$key){
        if(!isset($array[$key]))return false;
        return true;
    }
    /*
     * 判断某个数组的某个KEY存在且不为false
     * @param $array
     * @param $key
     */
    static public function is_key($array,$keys){

        $lock = true;
        foreach($keys as $key=>$value){
             if(!isset($array[$value])){
                 $lock = false;
                 return $lock;
             }
        }

        return $lock;
    }
    /*
     * 各种类型的验证
     * @param check type
     * @param data
     */
    static public function  regexp($rule,$key){
        switch ($rule) {
            case "id" : // ID 字母、数字、下划线组成 6-20
                return preg_match ( "/^(\w){4,20}$/", $key );
            case "password" : // 密码
                return preg_match ( "/^(\S){6,20}$/", $key );
            case "int" : // 数字
                return preg_match ( "/^\d{8,32}$/", $key );
            case "tradepassword" : // 交易密码
                return preg_match ( "/^(\S){6}$/", $key );
            case "md5password" : // 密码
                return preg_match ( "/^(\S){32}$/", $key );
            case "zhcn" : // 中文
                return preg_match ( "/[\u4e00-\u9fa5]/", $key );
            case "tel" : // 国内座机电话号
                return preg_match ( "/\d{3}-\d{8}|\d{4}-\d{7,8}/", $key );
            case "mobile"://手机号
                return preg_match('/^[1][3578][0-9]{9}$/',$key);
            case "qq" : // QQ号
                return preg_match ( "/^[1-9][0-9]{4,}$/", $key );
            case "numberInteger" : // 整形数字
                return preg_match ( "/^[-+]?[1-9]\d*\.?[0]*$/", $key );
            case "numberFloat" : // 浮点型数字
                return preg_match ( "/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/", $key );
            case "email" : // email
                return preg_match ( "/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/", $key );
            case "cid" : // 18位身份证号
                return preg_match ( "/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X|x)$/", $key );
            case "zipcode" : // 国内邮编
                return preg_match ( "/^[1-9]\d{5}(?!\d)$/", $key );
            case "url" : // 网址
                return preg_match ( "/^(ht|f)tp(s?)\:\/\/[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*(:(0-9)*)*(\/?)([a-zA-Z0-9\-\.\?\,\'\/\\\+&amp;%\$#_]*)?$/", $key );
            case "htmlHexCode" : // html颜色代码，如：#fff0
                return preg_match ( "//^#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?$/", $key );
            case "IP" : // ip地址
                return preg_match ( "/^(\d|[01]?\d\d|2[0-4]\d|25[0-5])\.(\d|[01]?\d\d|2[0-4] \d|25[0-5])\.(\d|[01]?\d\d|2[0-4]\d|25[0-5])\.(\d|[01]?\d\d|2[0-4] \d|25[0-5])$/", $key );
            case "macAddress" : // 主机mac地址
                return preg_match ( "/^([0-9a-fA-F]{2}:){5}[0-9a-fA-F]{2}$/", $key );
            case "name" : // 姓名验证
                return preg_match ( "/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{2,16}$/u", $key);
            case "address" : // 用户居住地址
                return preg_match ( "/^[\x{4e00}-\x{9fa5}A-Za-z0-9\s]{2,32}$/u", $key);
        }
        return false;
    }
}