<?php
// +------------------------------------------------------------------------------------------
// | Author: TCG <tianchunguang@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: 优惠 Dates: 2017-02-06
// +------------------------------------------------------------------------------------------
namespace Cli\Controller;

use Think\Controller;

class PreferentialController extends Controller
{
    /**
     * 执行生成优惠券码
     */
    public function genPreferentialCode()
    {
        try {
            D('Admin/preferential')->genPreferentialCode();
        } catch (\Exception $e) {
            \Think\Log::record($e->getMessage(), 'ERR');
        }
    }
}