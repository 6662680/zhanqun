<?php
// +------------------------------------------------------------------------------------------
// | Author: TCG <tianchunguang@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description:  第三方订单入口模型: 2016/10/25 完善：qiakilin
// +------------------------------------------------------------------------------------------
namespace Api\Model;

use Think\Exception;

class ThirdPartyModel
{
    /**
     * 实例工厂
     */
    public function factory($partner, $function, $param)
    {
        if (!$partner || !$function) {
            return false;
        }
        
        $PartnerModel = C('PartnerModel');
        
        /** 合作商对应后续处理 */
        if (!isset($PartnerModel[$partner])) {
            return false;
        }
        
        $Model = D('Api/' . ucfirst($PartnerModel[$partner]));

        if(!$Model || !is_object($Model)) {
            throw new Exception("找不到对应合作商的模型:{$PartnerModel[$partner]}");
            return false;
        }

        if (!method_exists($Model, $function)) {
            throw new Exception("找不到对应合作商的处理方法:{$function}");
            return false;
        }
        
        return $Model->$function($param);
    }
}