<?php
return array(

    /** 数据库配置信息 */
    'DB_TYPE'   => 'mysql',# 数据库类型

    /*线上服务器*/
/*
    'DB_HOST'   => '121.41.24.216', # 服务器地址/ 线上
    'DB_NAME'   => 'shanxiuxia3', # 数据库名
    'DB_USER'   => 'long01', # 用户名
    'DB_PWD'    => 'longdd', # 密码*/

    //'UPLOAD_PATH'=>'http://api.shanxiuxia.com',//程序URL



    /** 分享账号对应合作商 */

    'SharePartner' => array(
        'shenzhou' => 'shenzhou',
    ),

    /** 合作商对应后续处理模型 */
    'PartnerModel' => array(
        'shenzhou' => 'shenzhou',
    ),

    'baseUrl'=>'http://api.shanxiuxia.com',
    //保险状态
    'PIO_STATUS'=>[
        '-2' => '取消',
        '-1' => '取消',
        '0' => '未付款',
        '1' => '已付款',
        '2' => '在保中',
        '3' => '理赔中',
        '4' => '已过期',
        '5' => '服务完成',
    ],
    //保险颜色
    'PIO_COLOR'=>[
        '-2' => '#d2d2d2',
        '-1' => '#d2d2d2',
        '0' => '#d2d2d2',
        '1' => '#80c269',
        '2' => '#80c269',
        '3' => '#F37B46',
        '4' => '#d2d2d2',
        '5' => '#ffac1b',
    ],
    'ORDER_STATUS'=>[
        '-1' => '订单已取消',
        '1' => '已下单',
        '2' => '处理中',
        '3' => '工程师准备中',
        '4' => '工程师已出发',
        '5' => '待付款',
        '6' => '订单已完成',
    ],
    'ORDER_COLOR'=>[
        '-1' => '#d2d2d2',
        '1' => '#46a4ed',
        '2' => '#46a4ed',
        '3' => '#46a4ed',
        '4' => '#46a4ed',
        '5' => '#46a4ed',
        '6' => '#46a4ed',
    ],
    'URL_HTML_SUFFIX'=>''

);
