<?php

 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 权限类 Dates: 2016-07-15
// +------------------------------------------------------------------------------------------

namespace Org\Tool;

class Tree{  

    protected static $config = array(
        /* 主键 */
        'primary_key' => 'id',
        /* 父键 */
        'parent_key' => 'pid',
        /* 叶子节点属性 */
        'leaf_key' => 'leaf',
        /* 孩子节点属性 */
        'children_key' => 'children',
    );
    
    /* 结果集 */
    protected static $result = array();
    
    /* 层次暂存 */
    protected static $level = array();

    /**
     * @name 生成树形结构
     * @param array 二维数组
     * @return mixed 多维数组
     */
    public static function makeTree($data, $options = array())
    {
        $dataset = self::buildData($data, $options);
        $r = self::makeTreeCore(0, $dataset, 'normal');
        return $r;
    }
    
    /* 生成线性结构, 便于HTML输出, 参数同上 */
    public static function makeTreeForHtml($data, $options = array())
    {
        $dataset = self::buildData($data, $options);
        $r = self::makeTreeCore(0, $dataset, 'linear');
        return $r;  
    }
    
    /* 格式化数据, 私有方法 */
    private static function buildData($data,$options)
    {
        $config = array_merge(self::$config, $options);
        self::$config = $config;
        extract($config);

        $r = array();
        foreach($data as $item){
            $id = $item[$primary_key];
            $parent_id = $item[$parent_key];
            $r[$parent_id][$id] = $item;
        }
        
        return $r;
    }
    
    /* 生成树核心, 私有方法  */
    private static function makeTreeCore($index, $data, $type='linear')
    {
        extract(self::$config);

        foreach($data[$index] as $id => $item) {

            if ($type == 'normal') {

                if (isset($data[$id])) {
                    $item[$children_key] = self::makeTreeCore($id, $data, $type);
                } else {
                    $item[$leaf_key] = true; 
                    unset($item['state']);
                }

                $r[] = $item;
            } else if ($type == 'linear') {
                $parent_id = $item[$parent_key];
                self::$level[$id] = $index == 0 ? 0 : self::$level[$parent_id] + 1;
                $item['level'] = self::$level[$id];
                self::$result[] = $item;

                if (isset($data[$id])) {
                    self::makeTreeCore($id,$data,$type);
                }
                
                $r = self::$result;
            }
        }

        return $r;
    }
}