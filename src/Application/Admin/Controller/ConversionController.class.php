<?php
 
// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description:  Dates: 2016-08-05
// +------------------------------------------------------------------------------------------ 

namespace Admin\Controller;

use Admin\Controller;

class ConversionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(){
        $this->display();
    }

    /**
     * 留言列表
     *
     * @return void
     */
    public function rows()
    {
        // where 条件
        $map = $this->whereByPostParams();
        $model = M('Conversion');

        $rst['total'] = $model->where($map)->count();
        $rst['rows'] = $model->where($map)->limit($this->page())->order('id DESC')->select();

        $this->ajaxReturn($rst);
    }

    /**
     * 来源导出
     *
     * @return void
     */
    public function export()
    {

        // where 条件
        $map = $this->whereByPostParams();

        $model = M('Conversion');
        $fields = array('id', 'ip', 'keyword', 'partner', 'type', 'start_time', 'end_time', 'magic', 'dedark', 'origin', 'area', 'order_number');
        $list = $model->where($map)->field($fields)->order('id DESC')->select();

        // 第一列为字段名称
        $columns = array(array(
            'ID',
            'IP',
            '关键词',
            '合作伙伴',
            '下单类型',
            '着陆时间',
            '下单时间',
            '魔法关键词',
            '着陆第一张页面',
            '来路页面地址（空值为直接输入网址）',
            '来路IP的中文地区',
            '订单编号'
        ));

        // 时间显示格式化
        foreach ($list as &$value) {
            $value['start_time'] = date('Y-m-d H:i:s', $value['start_time']);
            if ($value['end_time'] > 0) {
                $value['end_time'] = date('Y-m-d H:i:s', $value['end_time']);
            }
        }

        $list = array_merge($columns, $list);

        $fileName = '来源统计-'.date("Y-m-d H:i:s", time());
        parent::exportData($fileName, $list);
    }

    /**
     * 根据 post 方式提交的参数得到查询条件
     *
     * @return array
     */
    private function whereByPostParams()
    {
        $map = array();
        $post = I('post.');

        /*下单类型*/
        if (isset($post['type']) && intval($post['type']) > -1) {
            $map['type'] = array('eq', intval($post['type']));
        }

        /*搜索引擎*/
        if (!empty($post['engine'])) {
            $map['partner'] = array('eq', $post['engine']);
        }

        /*关键词*/
        if ($post['keyword']) {
            $map['keyword'] = array('like', '%' . trim($post['keyword']). '%');
        }

        /*魔法关键词*/
        if ($post['magic']) {
            $map['magic'] = array('like', '%' . trim($post['magic']). '%');
        }

        /*着陆页面*/
        if ($post['dedark']) {
            $map['dedark'] = array('like', '%' . trim($post['dedark']). '%');
        }

        /*来路地区*/
        if ($post['area']) {
            $map['area'] = array('like', '%' . trim($post['area']). '%');
        }

        /*订单号*/
        if ($post['order_number']) {
            $map['order_number'] = array('like', '%' . trim($post['order_number']). '%');
        }

        $start_time = intval(strtotime(trim($post['start_time'])));
        $end_time = intval(strtotime(trim($post['end_time'])));

        if ($start_time <= 0 && $end_time > 0) {
            $map['start_time'] = array(
                array('gt', 0),
                array('lt' , $end_time + 86400)
            );
        } elseif ($start_time > 0 && $end_time <= 0) {
            $map['start_time'] = array('egt', $start_time);
        } elseif ($start_time > 0 && $end_time > 0 && $start_time <= $end_time) {
            $map['start_time'] = array(
                array('egt', $start_time),
                array('lt' , $end_time + 86400)
            );
        }

        return $map;
    }
}