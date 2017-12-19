<?php

 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 权限类 Dates: 2016-07-15
// +------------------------------------------------------------------------------------------

namespace Org\Tool;

use Think\Db;

class Permission
{
    /**
     * 权限认证
     *
     * @return void
     */
    static public function authenticate()
    {
        $isRoot = session('isRoot');
        $access = session('access');

        if ($isRoot) {
            return true;
        }

        $item = strtolower('/' . MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME);

        if (in_array($item, $access)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 授权
     *
     * @return void
     */
    static function authorization()
    {
        $userId = session('userId');
        $isRoot = session('isRoot');

        if ($isRoot) {
            $nodes = M('node')->select();
        } else {
            $sql = "select n.* from user_role ur 
            left join role r on ur.role_id=r.id 
            left join role_node rn on ur.role_id=rn.role_id 
            left join node n on rn.node_id=n.id 
            where r.status=1 and n.status=1 
            and ur.user_id={$userId};";
            $nodes = M()->query($sql);
        }

        /** 一级栏目 二级栏目 */
        $menu = array();
        /** 二级栏目 按钮 */
        $button = array();
        /** 权限列表 操作array */
        $access = array();

        foreach ($nodes as $key => $node) {

            if (in_array($node['category'], array(3, 4))) {
                $menu[$node['id']] = $node;
            }

            if (in_array($node['category'], array(3, 2))) {
                $button[$node['id']] = $node;
            }

            if (in_array($node['category'], array(3, 2, 1))) {
                $access[$node['id']] = $node['action'];
            }
        }

        self::createMenu($menu);
        self::createButton($button);
        self::createAccess($access);
    }

    /**
     * 生成栏目
     *
     * @return void
     */
    static public function createMenu($nodes)
    {
        $menu = array();

        ksort($nodes);

        foreach ($nodes as $key => $value) {

            if ($value['category'] == 4) {
                $menu[$value['id']] = $value;
            } else{

                if (!empty($value['group'])) {
                    $menu[$value['pid']]['children'][$value['group']][] = $value;
                } else {
                    $menu[$value['pid']]['children']['默认'][] = $value;
                }
            }
        }

        session('menu', $menu);
    }

    /**
     * 栏目按钮
     *
     * @return void
     */
    static public function createButton($node)
    {
        $button = array();

        foreach ($node as $key => $value) {

            if ($value['category'] == 2) {
                $button[strtolower($node[$value['pid']]['action'])][$value['alias']] = $value;
            } else {
                $button[strtolower($value['action'])] = $value;
            }
        }

        session('button', $button);
    }

    /**
     * 权限列表
     *
     * @return void
     */
    static public function createAccess($access)
    {
        session('access', $access);
    }

}