<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 树形类 Dates: 2016-01-27
// +------------------------------------------------------------------------------------------

namespace Org\Util;

class addressTree
{
 
    /** 待处理数组 */
    public $arr = array();
 
    /**
    * 构造函数，初始化类
    *
    * @param array $arr 2维数组
    * @return boolean
    */
    public function __construct($arr = array())
    {
       $this->arr = $arr;
       return is_array($arr);
    }

    /**
     * 获取树形
     *
     * @return array 多维树形数组
     */
    public function getTree()
    {
        krsort($this->arr);

        foreach ($this->arr as $key => &$value) {

            if (intval(substr($value['id'], 4, 2)) > 0) {
                $value['leaf'] = true;
            } else {
                $value['leaf'] = false;
            }
        }

        foreach ($this->arr as $key => &$value) {

            if (!isset($this->arr[$value['pid']]['children'])) {
                $this->arr[$value['pid']]['children'] = array();
            }

            array_unshift($this->arr[$value['pid']]['children'], $value);
        }

        return $this->arr[0]['children'];
    }
}
