<?php
// +------------------------------------------------------------------------------------------ 
// | Author: qikailin <qklandy@gmail.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2017/7/3
// +------------------------------------------------------------------------------------------
// | 描述:  未完善
// +------------------------------------------------------------------------------------------
namespace Vendors\Http;

class Input
{

    public $logname;
    public $isshwomsg;

    function __construct()
    {
        set_error_handler('MyError', E_ALL);
    }

    function MyError($errno, $errstr, $errfile, $errline)
    {
        echo "<b>Error number:</b> [$errno],error on line $errline in $errfile<br />";
        exit;
    }

    function wlog($logs)
    {
        if (empty($logname)) {
            $this->logname = $_SERVER["DOCUMENT_ROOT"] . "/log.htm";
        }
        $Ts = fopen($this->logname, "a+");
        fputs($Ts, $logs . "\r\n");
        fclose($Ts);
    }

    function showmsg($msg = '', $flag = false)
    {
        $this->isshwomsg = empty($this->isshwomsg) ? false : true;
        if ($this->isshwomsg) {
            echo '<br />--------------------------------------<br />';
            echo $msg;
            echo '<br />--------------------------------------<br />';
            if ($flag) exit;
        }
    }

    function get_filter()
    {
        $getfilter = "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        foreach ($_GET as $key => $value) {
            $this->StopAttack($key, $value, $getfilter);
        }
    }

    function post_filter()
    {
        $postfilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        foreach ($_POST as $key => $value) {
            $this->StopAttack($key, $value, $postfilter);
        }
    }

    function cookie_filter()
    {
        $cookiefilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        foreach ($_COOKIE as $key => $value) {
            $this->StopAttack($key, $value, $cookiefilter);
        }
    }

    //过滤参数
    function StopAttack($StrFiltKey, $StrFiltValue, $ArrFiltReq)
    {
        if (is_array($StrFiltValue)) {
            $StrFiltValue = implode($StrFiltValue);
        }
        if (preg_match("/" . $ArrFiltReq . "/is", $StrFiltValue) == 1) {
            $msg = "<br><br>操作IP: " . $_SERVER["REMOTE_ADDR"] . "<br>操作时间: " . strftime("%Y-%m-%d %H:%M:%S") . "<br>操作页面:" . $_SERVER["PHP_SELF"] . "<br>提交方式: " . $_SERVER["REQUEST_METHOD"] . "<br>提交参数: " . $StrFiltKey . "<br>提交数据: " . $StrFiltValue;
            $this->wlog($msg);
            $this->showmsg($msg);
            exit();
        }
    }

    function filter_value_for_sql($str)
    {
        $str = str_replace("and", "", $str);
        $str = str_replace("execute", "", $str);
        $str = str_replace("update", "", $str);
        $str = str_replace("count", "", $str);
        $str = str_replace("chr", "", $str);
        $str = str_replace("mid", "", $str);
        $str = str_replace("master", "", $str);
        $str = str_replace("truncate", "", $str);
        $str = str_replace("char", "", $str);
        $str = str_replace("declare", "", $str);
        $str = str_replace("select", "", $str);
        $str = str_replace("create", "", $str);
        $str = str_replace("delete", "", $str);
        $str = str_replace("insert", "", $str);
        $str = str_replace("'", "", $str);
        $str = str_replace('"', "", $str);
        $str = str_replace(" ", "", $str);
        $str = str_replace("or", "", $str);
        $str = str_replace("=", "", $str);
        $str = str_replace(" ", "", $str);
        return $str;
    }

}