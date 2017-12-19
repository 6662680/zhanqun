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
class BaseModel
{
    /** 缓存时间 */
    public $cache_time = 60;

    /**
     * 获取栏目
     *
     * @return array 栏目数据
     */
    public function getMenu()
    {
        $map = array();
        $map['status'] = 1;
        $map['pid'] = 0;
        $menus = D('menu')->cache(true, $this->cache_time)->where($map)->field('id, title, pid, type, url')->select();
        return $menus;
    }

    /**
     * 统计用户数据
     *
     *  @return Boolean
     */
    public function statistics($orderNumber=NULL){
        $data = array('ip'=>'','start_time'=>'','magic'=>'','origin'=>'','dedark'=>'','keyword'=>'','area'=>'');

        foreach($data as $key => &$value){
            $value =I('post.'.$key);
        }

        if ($orderNumber) $data['order_number'] = D('order')->vOrder($orderNumber);
        if (!D('conversion')->add($data)) return false;
        return true;
    }
}