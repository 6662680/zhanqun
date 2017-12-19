<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Org\Util;

use Think\Db;

/**
 * 基于角色的数据库方式验证类
 */

class Rbac 
{
    //用于检测用户权限的方法,并保存到Session中
    public static function saveAccessList($authId = null) 
    {
        if (null === $authId) {
           $authId = $_SESSION[C('USER_AUTH_KEY')];
        }   

        // 如果使用普通权限模式，保存当前用户的访问权限列表
        // 对管理员开发所有权限
        if (C('USER_AUTH_TYPE') != 2 && !$_SESSION[C('ADMIN_AUTH_KEY')]) {
            $_SESSION['_ACCESS_LIST'] = self::getAccessList($authId);
        }
    }

    //权限认证的过滤器方法
    public static function AccessDecision() 
    {

        //存在认证识别号，则进行进一步的访问决策
        $accessKey = md5(APP_NAME . MODULE_NAME . 'GROUP' . CONTROLLER_NAME . ACTION_NAME);
        
        if (empty($_SESSION[C('ADMIN_AUTH_KEY')])) {

            if (C('USER_AUTH_TYPE') == 2) {
                $accessList = self::getAccessList($_SESSION[C('USER_AUTH_KEY')]);
            } else {

                if($_SESSION[$accessKey]) {
                    return true;
                }

                $accessList = $_SESSION['_ACCESS_LIST'];
            }

            //判断是否为组件化模式，如果是，验证其全模块名
            if (isset($accessList[strtoupper(APP_NAME)][strtoupper(MODULE_NAME)]['GROUP'][strtoupper(CONTROLLER_NAME)][strtoupper(ACTION_NAME)])) {
                $_SESSION[$accessKey] = true;
                return true;
            } else {
                $_SESSION[$accessKey] = false;
                return false;
            }
        } else {
            //管理员无需认证
            return true;
        }
    }

    /**
     *
     * 取得当前认证号的所有权限列表
     *
     * @param integer $authId 用户ID
     * @access public
     */
    public static function getAccessList($authId) 
    {
        // Db方式权限数据
        $db = Db::getInstance(C(''));

        /* 权限节点列表 apps -> modules -> groups -> controllers -> actions -> params */

        $sql = "SELECT node.id, node.pid, node.action, node.type, node.param FROM `node`
                LEFT JOIN `access` ON node.id=access.node_id
                LEFT JOIN `role` ON access.role_id=role.id
                LEFT JOIN `user` ON user.role_id=role.id
                WHERE node.status=1
                AND role.status=1
                AND user.id={$authId}";

        $resulst = $db->query($sql);

        $nodes = array();
        foreach ($resulst as $key => &$value) {
            $value['name'] = strtoupper($value['action']);
            $nodes[$value['id']] = $value;
        }

        krsort($nodes);

        $tree = array();
        foreach ($nodes as $key => &$value) {
            if (isset($nodes[$value['pid']])) {
                $nodes[$value['pid']]['child'][$value['id']] = $value;
                unset($nodes[$key]);
            } else {
                $tree = $value;
            }
        }

        $accessTree = self::createAccessTree($tree);
        return $accessTree;
    }

    /**
     * 生成树形数组
     *
     * @return array
     */
    private function createAccessTree($tree, $isRoot = true)
    {
        $accessTree = array();

        if (isset($tree['child'])) {
            foreach ($tree['child'] as &$value) {
                if ($isRoot) {
                    $accessTree[$tree['name']][$value['name']] = self::createAccessTree($value, false);
                } elseif($value['type'] == 3) {
                    $accessTree['GROUP'] = array_merge((array)$accessTree['GROUP'], (array)self::createAccessTree($value, false));
                } elseif($value['type'] == 1) {
                    /** 参数 */
                    $accessTree[$value['name']] = self::createAccessTree($value, false);
                }else {
                    $accessTree[$value['name']] = self::createAccessTree($value, false);
                }
            }
        } else {
            return true;
        }

        return $accessTree;
    }

    /**
     *
     * 取得当前认证号的所有节点
     *
     * @param integer $authId 用户ID
     * @access public
     */
    public static function getNodes($authId) 
    {
        // Db方式权限数据
        $db = Db::getInstance(C(''));

        /* 权限节点列表 apps -> modules -> groups -> controllers -> actions -> params */

        $sql = "SELECT n.id, n.pid, n.name, n.action, n.param, n.type, n.is_menu, n.menu_id, n.menu_name, n.menu_group, n.menu_index, n.menu_icon, n.sort FROM `node` as n
                LEFT JOIN `access` as a ON n.id = a.node_id
                LEFT JOIN `role` as r ON a.role_id = r.id
                LEFT JOIN `user` as u ON u.role_id = r.id
                WHERE n.status=1
                AND r.status=1
                AND u.id={$authId} order by n.sort";

        $resulst = $db->query($sql);

        $nodes = array();
        foreach ($resulst as $key => &$value) {
            $value['name'] = strtoupper($value['name']);

            $nodes[$value['id']] = $value;
        }

        krsort($nodes);

        $tree = array();
        foreach ($nodes as $key => &$value) {
            if (isset($nodes[$value['pid']])) {
                $nodes[$value['pid']]['child'][$value['id']] = $value;
                unset($nodes[$key]);
            } else {
                $tree = $value;
            }
        }

        return $tree;
    }

    /**
     * 生成栏目数据
     *
     * @return array
     */
    public static function getMenu($adminId)
    {
        $menus = array();
        $nodes = self::getNodes($adminId);
        $adminNodes = $nodes['child'][2];
        $sort = array();
        //print_r($nodes);die;
        // 筛选栏目
        foreach ($adminNodes['child'] as $key => $value) {
            if ($value['is_menu']) {
                $menu['id'] = $value['menu_id'];

                $menu['name'] = $value['menu_name'];
                $menu['menu_icon'] = $value['menu_icon'];
                $menu['menu'] = array();
                $groups = array();

                $sort[] = $value['sort'];
                $g_sort = array();
                foreach ($value['child'] as $k => $v) {
                    foreach ($v['child'] as $x => $y) {
                        if ($y['is_menu']) {
                            $groups[$y['menu_group']]['text'] = $y['menu_group'];
                            $groups[$y['menu_group']]['sort'][] = $y['sort'];

                            if ($v['action'] == 'Index' && $y['action'] == 'main') {
                                $groups[$y['menu_group']]['items'][] = array(
                                    'id' => $y['menu_id'], 
                                    'text' => $y['menu_name'], 
                                    'href' => U($adminNodes['action'] . '/' . $v['action'] . '/' . $y['action']) . $y['param'],
                                    'closeable' => false
                                );
                            } else {
                                $groups[$y['menu_group']]['items'][] = array(
                                    'id' => $y['menu_id'], 
                                    'text' => $y['menu_name'], 
                                    'href' => U($adminNodes['action'] . '/' . $v['action'] . '/' . $y['action']) . $y['param']
                                );
                            }
                        }
                    }
                }

                foreach ($value['child'] as $k => $v) {
                    foreach ($v['child'] as $x => $y) {
                        if ($y['is_menu']) {
                            $groups[$y['menu_group']]['gsort'] = min($groups[$y['menu_group']]['sort']);
                        }
                    }
                }

                foreach($groups as $key => $value){
                    $g_sort[$key] = $value['gsort'];
                }

                @array_multisort($g_sort, SORT_DESC, $groups); //按$g_sort降序排列


                foreach ($groups as $key => $value) {
                    @array_multisort($groups[$key]['items'], SORT_ASC, SORT_STRING, $value['sort'], SORT_NUMERIC, SORT_ASC);
                    unset($groups[$key]['sort']);
                }

                if (!empty($groups)) {
                    $menu['menu'] = array_values(array_reverse($groups));
                }

                if (empty($value['menu_index'])) {
                    $str = $menu['menu'][0]['items'][0]['id'];;
                    $menu['homePage'] = $str;
                } else {
                    $menu['homePage'] = $value['menu_index'];
                }

                $menus[] = $menu;
            }
        }
        @array_multisort($menus, SORT_ASC, SORT_STRING, $sort, SORT_NUMERIC, SORT_ASC);

        return $menus;
    }

}