<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 数据迁移 Dates: 2016-10-26
// +------------------------------------------------------------------------------------------ 

namespace Cli\Controller;

class TransferController extends Controller
{
    private $rule_user = array(
        'id' => array(
            'column' => 'id',
        ),
        'username' => array(
            'column' => 'username',
        ),
        'realname' => array(
            'column' => 'realname',
        ),
        'telphone' => array(
            'column' => 'telphone',
        ),
        'password' => array(
            'column' => 'password',
        ),
        'status' => array(
            'column' => 'status',
        ),
        'is_root' => array(
            'column' => 'is_root',
        ),
        'create_time' => array(
            'column' => 'create_time',
        ),
        'last_login_time' => array(
            'column' => 'last_login_time',
        ),
        'last_login_ip' => array(
            'column' => 'last_login_ip',
        ),
        'remark' => array(
            'column' => 'remark',
        ),
    );

    /**
     * 执行数据迁移
     *
     * @return void
     */
    public function run($rule = "user")
    {
        $this->display();
    }
}