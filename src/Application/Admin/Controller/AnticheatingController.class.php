<?php

namespace Admin\Controller;

use Admin\Controller;

class AnticheatingController extends BaseController
{
    public function index()
    {
        $this->assign('organizations', session('organizations'));
        $this->assign('engineers', $this->engineersArray());
        $this->display();
    }

    /**
     * 订单操作列表导出
     *
     * @return void
     */
    public function export()
    {
        $logModel = M('orderLog');
        $map = $this->logWhereByParams();
        $list = $logModel->join('AS ol LEFT JOIN `order` AS o ON o.id = ol.order_id')
            ->join('LEFT JOIN `engineer` AS e ON e.`id` = o.`engineer_id`')
            ->join('LEFT JOIN `organization` AS ogz ON ogz.`id` = e.`organization_id`')
            ->order('ol.`id` desc')
            ->field('ol.`id`,o.`number`,o.`phone_name`, ol.`action`, ol.`time`, o.`customer`,o.`cellphone`,o.`create_time`,o.`end_time`,
            o.`is_clearing`,ogz.`name` AS `organization_name`,e.`name` AS `engineer_name`')
            ->where($map)
            ->select();

        // 时间格式化处理
        foreach($list as &$value) {
            $value['time'] = ($value['time'] > 0) ? date("Y-m-d H:i:s", $value['time']) : '';
            $value['create_time'] = ($value['create_time'] > 0) ? date("Y-m-d H:i:s", $value['create_time']) : '';
            $value['end_time'] = ($value['end_time'] > 0) ? date("Y-m-d H:i:s", $value['end_time']) : '';
        }

        $titles = array('操作ID','订单编号','机型', '操作','操作时间','客户','手机', '下单时间', '结单时间', '是否结单', '组织', '工程师');
        array_unshift($list, $titles);

        $file = '订单操作记录' . date("Y-m-d H:i:s");
        $this->exportData($file, $list);
    }

    /**
     * 订单操作列表 ajax
     *
     * @return void
     */
    public function rows()
    {

        $logModel = M('orderLog');
        $map = $this->logWhereByParams();
        $count = $logModel->join('AS ol LEFT JOIN `order` AS o ON o.id = ol.order_id')
            ->join('LEFT JOIN `engineer` AS e ON e.`id` = o.`engineer_id`')
            ->join('LEFT JOIN `organization` AS ogz ON ogz.`id` = e.`organization_id`')
            ->where($map)
            ->count();

        $list = $logModel->join('AS ol LEFT JOIN `order` AS o ON o.id = ol.order_id')
            ->join('LEFT JOIN `engineer` AS e ON e.`id` = o.`engineer_id`')
            ->join('LEFT JOIN `organization` AS ogz ON ogz.`id` = e.`organization_id`')
            ->limit($this->page())
            ->order('ol.`id` desc')
            ->field('ol.`id`,o.`number`,o.`phone_name`, ol.`action`, ol.`time`, o.`customer`,o.`cellphone`,o.`create_time`,o.`end_time`,
            o.`is_clearing`,ogz.`name` AS `organization_name`,e.`name` AS `engineer_name`')
            ->where($map)
            ->limit($this->page())
            ->select();
        $rst['total'] = $count;
        $rst['rows'] = $list;
        $this->ajaxReturn($rst);
    }

    /**
     * 根据 post 数据过滤 where 查询条件
     *
     * @return array
     */
    private function logWhereByParams()
    {
        $map = array();
        $post = I('post.');

        // 下单时间
        $create_time_start = trim($post['create_time_start']);
        $create_time_end = trim($post['create_time_end']);

        if ($create_time_start && $create_time_end) {   // 有开始，有结束
            $map['o.`create_time`'] = array(
                array('egt', strtotime($create_time_start)),
                array('lt', strtotime($create_time_end . "+1 day"))
            );
        } elseif ($create_time_start && !$create_time_end) {    // 有开始，没结束
            $map['o.`create_time`'] = array('egt', strtotime($create_time_start));
        } elseif (!$create_time_start && $create_time_end) {    // 没开始，有结束
            $map['o.`create_time`'] = array('lt', strtotime($create_time_end . "+1 day"));
        }

        // 结单时间
        $clearing_time_start = trim($post['clearing_time_start']);
        $clearing_time_end = trim($post['clearing_time_end']);

        if ($clearing_time_start && $clearing_time_end) {   // 有开始，有结束
            $map['o.`end_time`'] = array(
                array('egt', strtotime($clearing_time_start)),
                array('lt', strtotime($clearing_time_end . "+1 day"))
            );
        } elseif ($clearing_time_start && !$clearing_time_end) {    // 有开始，没结束
            $map['o.`end_time`'] = array('egt', strtotime($clearing_time_start));
        } elseif (!$clearing_time_start && $clearing_time_end) {    // 没开始，有结束
            $map['o.`end_time`'] = array('lt', strtotime($clearing_time_end . "+1 day"));
        }

        // 操作时间
        $action_time_start = trim($post['action_time_start']);
        $action_time_end = trim($post['action_time_end']);

        if ($action_time_start && $action_time_end) {   // 有开始，有结束
            $map['ol.`time`'] = array(
                array('egt', strtotime($action_time_start)),
                array('lt', strtotime($action_time_end . "+1 day"))
            );
        } elseif ($action_time_start && !$action_time_end) {    // 有开始，没结束
            $map['ol.`time`'] = array('egt', strtotime($action_time_start));
        } elseif (!$action_time_start && $action_time_end) {    // 没开始，有结束
            $map['ol.`time`'] = array('lt', strtotime($action_time_end . "+1 day"));
        }

        // 组织
        if ($organization = intval(trim($post['organization']))) {
            $map['ogz.`id`'] = $organization;
        }

        // 工程师
        if ($engineer = intval(trim($post['engineer']))) {
            $map['e.`id`'] = $engineer;
        }

        // 操作
        if ($action = trim($post['action'])) {
            $map['ol.`action`'] = array('like', '%' . $action . '%');
        }

        return $map;
    }

    /**
     * 工程师列表
     *
     * @return mixed
     */
    private function engineersArray()
    {
        $list = M('engineer')->where(array('status' => array('gt', -1), 'organization_id' => array('in', array_keys(session('organizations')))))->field('id, name')->select();
        array_unshift($list,array('name'=>'全部','id'=>''));
        return $list;
    }

    /**
     * 统计列表
     *
     * @return void
     */
    public function statistics()
    {
        if (IS_POST) {
            $rst = $this->statisticsGroupArray();
            $this->ajaxReturn($rst);
        } else {
            $this->display();
        }
    }

    /**
     * 统计导出
     *
     * @return void
     */
    public function statisticsExportTotal() {
        $list = $this->statisticsGroupArray();

        $data = array(
            array('名称', '订单修改数量', '外屏=>内屏订单数量', '多故障=>少故障订单数量', '价格修改订单数量')
        );

        $organization = $list['organization'];
        foreach ($organization as $value) {
            $data[] = array($value['organization_name'], count($value['modify']), count($value['inToOut']), count($value['manyToLess']), count($value['price']));
        }

        $organization = $list['engineer'];
        foreach ($organization as $value) {
            $data[] = array($value['engineer_name'], count($value['modify']), count($value['inToOut']), count($value['manyToLess']), count($value['price']));
        }

        $file = "订单操作统计导出".date("Y-m-d H:i:s");
        $this->exportData($file, $data);
    }

    /**
     * 详细导出
     *
     * @return void
     */
    public function statisticsExportDetail() {
        $data = $this->statisticsListByParams();
        $file = "订单操作详细导出".date("Y-m-d H:i:s");
        $this->exportData($file, $data);
    }

    private function statisticsGroupArray() {

        $list = $this->statisticsListByParams();

        // 故障修改
        $modify = array();
        $modifyOrderID = array();

        // 外屏 => 内屏
        $inToOut = array();
        $inToOutOrderID = array();

        // 故障多 => 故障少
        $manyToLess = array();
        $manyToLessOrderID = array();

        // 修改价格
        $price = array();
        $priceOrderID = array();

        // 根据操作分类
        foreach ($list as $key => &$value) {

            $action = $value['action'];
            $orderID = $value['order_id'];

            // 操作时间
            if ($value['action_time'] > 0) {
                $value['action_time'] = date("Y-m-d H:i:s", $value['action_time']);
            }

            // 下单时间
            if ($value['create_time'] > 0) {
                $value['create_time'] = date("Y-m-d H:i:s", $value['create_time']);
            }

            // 结单时间
            if ($value['clearing_time'] > 0) {
                $value['clearing_time'] = date("Y-m-d H:i:s", $value['clearing_time']);
            }

            // 故障修改
            if ($this->breakdownIsModified($action) && (!in_array($orderID, $modifyOrderID))) {
                $modify[] = $value;
                $modifyOrderID[] = $orderID;
            }
            unset($modifyOrderID);

            // 外屏 => 内屏
            if ($this->breakdownIsInToOut($action) && (!in_array($orderID, $inToOutOrderID))) {
                $inToOut[] = $value;
                $inToOutOrderID[] = $orderID;
            }
            unset($inToOutOrderID);

            // 故障多 => 故障少
            if ($this->breakdownIsManyToLess($action) && (!in_array($orderID, $manyToLessOrderID))) {
                $manyToLess[] = $value;
                $manyToLessOrderID[] = $orderID;
            }
            unset($manyToLessOrderID);

            // 修改价格
            if ((strpos($action, '更改实际价格[{') !== false) && (!in_array($orderID, $priceOrderID))) {
                $price[] = $value;
                $priceOrderID[] = $orderID;
            }
            unset($priceOrderID);
        }

        // 按组织分组
        $orgas = M('organization')->where(array('status' => array('eq', 1)))->field('`id`, `name`')->select();
        $organization = array();
        foreach ($orgas as $value) {

            $orgArray = array('organization_id' => $value['id'], 'organization_name' => $value['name']);
            // 修改
            $modifyData = array();
            foreach ($modify as $k => $v) {
                if ($v['organization_id'] == $orgArray['organization_id']) {
                    $modifyData[] = $v;
                }
            }
            $orgArray['modify'] = $modifyData;
            unset($modifyData);

            // 外屏 => 内屏
            $inToOutData = array();
            foreach ($inToOut as $k => $v) {
                if ($v['organization_id'] == $orgArray['organization_id']) {
                    $inToOutData[] = $v;
                }
            }
            $orgArray['inToOut'] = $inToOutData;
            unset($inToOutData);

            // 多故障 => 少故障
            $manyToLessData = array();
            foreach ($manyToLess as $k => $v) {
                if ($v['organization_id'] == $orgArray['organization_id']) {
                    $manyToLessData[] = $v;
                }
            }
            $orgArray['manyToLess'] = $manyToLessData;
            unset($manyToLessData);

            // 修改价格
            $priceData = array();
            foreach ($price as $k => $v) {
                if ($v['organization_id'] == $orgArray['organization_id']) {
                    $priceData[] = $v;
                }
            }
            $orgArray['price'] = $priceData;
            unset($priceData);

            $organization[] = $orgArray;
            unset($orgArray);
        }

        // 按工程师分组
        $engis = M('engineer')->where(array('status' => array('gt', -1)))->field('`id`, `name`')->select();
        $engineer = array();
        foreach ($engis as $value) {

            $engiArray = array('engineer_id' => $value['id'], 'engineer_name' => $value['name']);

            // 修改
            $modifyData = array();
            foreach ($modify as $k => $v) {
                if ($v['engineer_id'] == $engiArray['engineer_id']) {
                    $modifyData[] = $v;
                }
            }
            $engiArray['modify'] = $modifyData;
            unset($modifyData);

            // 外屏 => 内屏
            $inToOutData = array();
            foreach ($inToOut as $k => $v) {
                if ($v['engineer_id'] == $engiArray['engineer_id']) {
                    $inToOutData[] = $v;
                }
            }
            $engiArray['inToOut'] = $inToOutData;
            unset($inToOutData);

            // 多故障 => 少故障
            $manyToLessData = array();
            foreach ($manyToLess as $k => $v) {
                if ($v['engineer_id'] == $engiArray['engineer_id']) {
                    $manyToLessData[] = $v;
                }
            }
            $engiArray['manyToLess'] = $manyToLessData;
            unset($manyToLessData);

            // 修改价格
            $priceData = array();
            foreach ($price as $k => $v) {
                if ($v['engineer_id'] == $engiArray['engineer_id']) {
                    $priceData[] = $v;
                }
            }
            $engiArray['price'] = $priceData;
            unset($priceData);

            $engineer[] = $engiArray;
            unset($engiArray);
        }
        $rst = array('organization' => $organization, 'engineer' => $engineer);
        return $rst;
    }

    private function statisticsListByParams()
    {
        $map = array();
        $post = I('post.');

        // 下单时间
        $create_time_start = trim($post['create_time_start']);
        $create_time_end = trim($post['create_time_end']);

        if ($create_time_start && $create_time_end) {   // 有开始，有结束
            $map['o.`create_time`'] = array(
                array('egt', strtotime($create_time_start)),
                array('lt', strtotime($create_time_end . "+1 day"))
            );
        } elseif ($create_time_start && !$create_time_end) {    // 有开始，没结束
            $map['o.`create_time`'] = array('egt', strtotime($create_time_start));
        } elseif (!$create_time_start && $create_time_end) {    // 没开始，有结束
            $map['o.`create_time`'] = array('lt', strtotime($create_time_end . "+1 day"));
        }

        // 付款时间
        $clearing_time_start = trim($post['clearing_time_start']);
        $clearing_time_end = trim($post['clearing_time_end']);

        if ($clearing_time_start && $clearing_time_end) {   // 有开始，有结束
            $map['o.`clearing_time`'] = array(
                array('egt', strtotime($clearing_time_start)),
                array('lt', strtotime($clearing_time_end . "+1 day"))
            );
        } elseif ($clearing_time_start && !$clearing_time_end) {    // 有开始，没结束
            $map['o.`clearing_time`'] = array('egt', strtotime($clearing_time_start));
        } elseif (!$clearing_time_start && $clearing_time_end) {    // 没开始，有结束
            $map['o.`clearing_time`'] = array('lt', strtotime($clearing_time_end . "+1 day"));
        }

        // 操作时间
        $action_time_start = trim($post['action_time_start']);
        $action_time_end = trim($post['action_time_end']);

        if ($action_time_start && $action_time_end) {   // 有开始，有结束
            $map['ol.`time`'] = array(
                array('egt', strtotime($action_time_start)),
                array('lt', strtotime($action_time_end . "+1 day"))
            );
        } elseif ($action_time_start && !$action_time_end) {    // 有开始，没结束
            $map['ol.`time`'] = array('egt', strtotime($action_time_start));
        } elseif (!$action_time_start && $action_time_end) {    // 没开始，有结束
            $map['ol.`time`'] = array('lt', strtotime($action_time_end . "+1 day"));
        }

        $map['ol.`action`'] = array('LIKE', array('%更改实际价格[{%', '%故障:[{%'), 'OR');

        $logModel = M('orderLog');
        $list = $logModel->join('AS ol LEFT JOIN `order` AS o ON o.id = ol.order_id')
            ->join('LEFT JOIN `engineer` AS e ON e.`id` = o.`engineer_id`')
            ->join('LEFT JOIN `organization` AS ogz ON ogz.`id` = e.`organization_id`')
            ->order('ol.`id` desc')
            ->field('ogz.`id` AS `organization_id` , ogz.`name` AS `organization_name`, e.`id` AS `engineer_id`, e.`name` AS `engineer_name`, o.`id` AS `order_id`, o.`number`, o.`create_time` AS `create_time`, o.`clearing_time` AS `clearing_time`, ol.`id` AS `order_log_id`, ol.`time` AS `action_time`, ol.`action`')
            ->where($map)
            ->select();

        return $list;
    }

    /**
     * 处理操作内容
     *
     * @param string $action 操作内容："操作人：wukangding--手动--编辑订单--状态：下单 故障:[{内屏显示异常} => {内屏显示异常,外屏碎(显示正常),其他,重装调试}]"
     * @return array $value = array('内屏显示异常', '内屏显示异常,外屏碎(显示正常),其他,重装调试');
     */
    private function handleAction($action = '')
    {
        $explode = explode('故障:[{', $action);
        $value = array();
        if ($explode[1]) {
            $value = explode('} => {', $explode[1]);
            $temp = explode('}]', $value[1]);
            $value[1] = $temp[0];
        }

        return $value;
    }

    /**
     * 判断操作是否是外屏改内屏
     *
     * @param string $action 操作内容："操作人：wukangding--手动--编辑订单--状态：下单 故障:[{内屏显示异常} => {内屏显示异常,外屏碎(显示正常),其他,重装调试}]"
     * @return bool
     */
    private function breakdownIsInToOut($action = '') {
        $handles = $this->handleAction($action);
        $result = false;

        // 拆分的数组
        if ((count($handles) > 1) && ($handles[0] !== $handles[1])) {
            $optionA = (strpos($handles[0], '外屏') !== false) ? true : false;
            $optionB = (strpos($handles[0], '内屏') !== false) ? true : false;
            $optionC = (strpos($handles[1], '外屏') !== false) ? true : false;
            $optionD = (strpos($handles[1], '内屏') !== false) ? true : false;
            $result = $optionA && $optionD && !$optionB && !$optionC;
        }

        return $result;
    }

    /**
     * 判断操作前后内容是否改变
     *
     * @param string $action 操作内容："操作人：wukangding--手动--编辑订单--状态：下单 故障:[{内屏显示异常} => {内屏显示异常,外屏碎(显示正常),其他,重装调试}]"
     * @return bool
     */
    private function breakdownIsModified($action = '') {
        $handles = $this->handleAction($action);
        $result = false;
        if (is_array($handles)) {
            $result = $handles[0] !== $handles[1];
        }
        return $result;
    }

    /**
     * 判断是否是多故障改为少故障
     *
     * @param string $action 操作内容："操作人：wukangding--手动--编辑订单--状态：下单 故障:[{内屏显示异常} => {内屏显示异常,外屏碎(显示正常),其他,重装调试}]"
     * @return bool
     */
    private function breakdownIsManyToLess($action = '') {
        $handles = $this->handleAction($action);
        $result = false;
        if (count($handles) > 1) {
            $numBefore = count(explode(',', $handles[0])); // 操作前故障数
            $numAfter = count(explode(',', $handles[1])); // 操作后故障数
            $result = $numBefore > $numAfter;
        }
        return $result;
    }
}