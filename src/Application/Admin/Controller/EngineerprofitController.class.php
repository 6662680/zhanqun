<?php

namespace Admin\Controller;

class EngineerprofitController extends BaseController
{
    public function index()
    {
        $this->assign('engineers', $this->engineerList());
        $this->display();
    }

    public function rows()
    {
        $rst = array();

        $map = $this->whereByParams();

        $divideModel = M('engineerDivide');

        $count = $divideModel->join('AS ed LEFT JOIN `engineer` AS e ON e.id = ed.engineer_id')
            ->join('LEFT JOIN `order` AS o ON o.id = ed.order_id')
            ->where($map)
            ->count();

        $rst['total'] = $count;

        $list = $divideModel->join('AS ed LEFT JOIN `engineer` AS e ON e.id = ed.engineer_id')
            ->join('LEFT JOIN `order` AS o ON o.id = ed.order_id')
            ->where($map)
            ->limit($this->page())
            ->field('ed.`id`, ed.`engineer_id`, e.`name`, ed.`order_id`,ed.`order_number`, e.`work_number`, e.`cellphone`, ed.`earning`, ed.`is_clear`, o.`clearing_time`')
            ->order('ed.`id` desc')
            ->select();

        $this->handleList($list);
        $rst['rows'] = $list;

        $this->ajaxReturn($rst);
    }

    /**
     * 导出
     *
     * @return void
     */
    public function export()
    {
        $map = $this->whereByParams();

        $divideModel = M('engineerDivide');

        $list = $divideModel->join('AS ed LEFT JOIN `engineer` AS e ON e.id = ed.engineer_id')
            ->join('LEFT JOIN `order` AS o ON o.id = ed.order_id')
            ->where($map)
            ->field('ed.`order_number`, e.`name`, e.`work_number`, e.`cellphone`, ed.`earning`, ed.`is_clear`, o.`clearing_time`')
            ->order('ed.`id` desc')
            ->select();

        $this->handleList($list);

        $titles = array('订单编号', '工程师', '工号', '手机', '实际收益', '是否已发放', '付款时间');
        array_unshift($list, $titles);

        $file = '工程师收益-' . date("Y-m-d H:i:s");

        $this->exportData($file, $list);
    }

    /**
     * 收益数据处理
     *
     * @param $list
     * @return void
     */
    private function handleList(&$list) {

        foreach ($list as &$val) {

            if (intval($val['clearing_time']) > 0) {
                $val['clearing_time'] = date("Y-m-d H:i:s", $val['clearing_time']);
            }

            $val['is_clear'] = $val['is_clear'] ? '是' : '否';
        }
    }

    /**
     * 根据请求条件过滤
     *
     * @return array
     */
    private function whereByParams()
    {
        $map = array();
        $post = I('post.');

        // 结算时间
        $clean_time_start = trim($post['clean_time_start']);
        $clean_time_end = trim($post['clean_time_end']);

        if ($clean_time_start && $clean_time_end) {   // 有开始，有结束
            $map['o.`clearing_time`'] = array(
                array('egt', strtotime($clean_time_start)),
                array('lt', strtotime($clean_time_end . "+1 day"))
            );
        } elseif ($clean_time_start && !$clean_time_end) {    // 有开始，没结束
            $map['o.`clearing_time`'] = array('egt', strtotime($clean_time_start));
        } elseif (!$clean_time_start && $clean_time_end) {    // 没开始，有结束
            $map['o.`clearing_time`'] = array('lt', strtotime($clean_time_end . "+1 day"));
        }

        // 订单编号
        if ($order_number = trim($post['order_number'])) {
            $map['ed.`order_number`'] = array('LIKE', '%' . $order_number . '%');
        }

        // 工程师ID
        if ($engineer_id = intval(trim($post['engineer']))) {
            $map['ed.`engineer_id`'] = array('eq', $engineer_id);
        }

        return $map;
    }

    /**
     * 工程师列表
     *
     * @return mixed
     */
    private function engineerList() {
        $model = M('engineer');
        $list = $model->field(array('id', 'name'))->select();
        array_unshift($list, array('id' => 0, 'name' => '全部'));
        return $list;
    }
}