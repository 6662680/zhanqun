<?php

// +------------------------------------------------------------------------------------------
// | Author: TCG <tianchunguang@weadoc.com>
// +------------------------------------------------------------------------------------------
// | There is no true,no evil,no light,there is only power.
// +------------------------------------------------------------------------------------------
// | Description: 订单控制器 Dates: 2016-10-14
// +------------------------------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Controller;

class StatisticsController extends BaseController
{
    private $startDec = 0;
    
    public function __construct()
    {
        parent::__construct();
        $this->startDec = strtotime(date('2016-01-01'));
        set_time_limit(0);
    }
    
    /**
     * 订单统计
     *
     * @return void
     */
    public function orders()
    {
        $address = array();
        $count = array();
        $list = array();
        $day = I('get.day/d');
        $map = array();
        $data = array();
        
        $address['9999'] = '全国';
        
        $addresses = M('organization')->join('o left join address adr on o.city = adr.id')->where(array('type' => 1))->field('o.city, adr.name')->select();
        
        foreach ($addresses as $city) {
            $address[$city['city']] = $city['name'];
        }
        
        $address['0'] = '其他';
        
        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }
        
        if (IS_POST) {
        
            if (!empty(I('post.startTime'))) {
                $startTime = strtotime(I('post.startTime'));
            } else {
                $startTime = strtotime('today');
            }
        
            if (!empty(I('post.endTime'))) {
                $endTime = strtotime(I('post.endTime'));
            } else {
                $endTime = time();
            }
        
            $map['create_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else if ($day == 2) { /** 昨日 */
            $map['create_time'] = array(array('egt', strtotime('yesterday')), array('lt', strtotime('today')), 'AND');
        } else { /** 今日 */
            $map['create_time'] = array('egt', strtotime('today'));
        }
        
        //下单量
        $order_count = M('order')->where($map)->field('IF(city, city, 0) as city, count(*) as tp_count')
                ->group('city')->select();
        
        foreach ($order_count as $item) {
            $list[$item['city']] = $item['tp_count'];
        }
        unset($order_count);
        
        foreach ($address as $key => $value) {
            $tp_count = $list[$key];
        
            if ($key == 9999) {
                $tp_count = array_sum($list);
            }
            $count[$key] = (int)$tp_count;
        }
        
        $data[0]['name'] = '下单量';
        $data[0]['data'] = array_values($count);
        $orders = $count;
        
        //结单
        $map = array('status' => 5);
        
        if (IS_POST) {
        
            if (!empty(I('post.startTime'))) {
                $startTime = strtotime(I('post.startTime'));
            } else {
                $startTime = strtotime('today');
            }
        
            if (!empty(I('post.endTime'))) {
                $endTime = strtotime(I('post.endTime'));
            } else {
                $endTime = time();
            }
        
            $map['end_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else if ($day == 2) { /** 昨日 */
            $map['end_time'] = array(array('egt', strtotime('yesterday')), array('lt', strtotime('today')), 'AND');
        } else { /** 今日 */
            $map['end_time'] = array('egt', strtotime('today'));
        }
        
        $order_count = M('order')->where($map)->field('IF(city, city, 0) as city, count(*) as tp_count')
                        ->group('city')->select();
        
        $list = array();
        
        foreach ($order_count as $item) {
            $list[$item['city']] = $item['tp_count'];
        }
        unset($order_count);
        
        foreach ($address as $key => $value) {
            $tp_count = $list[$key];
        
            if ($key == 9999) {
                $tp_count = array_sum($list);
            }
            $count[$key] = (int)$tp_count;
        }
        
        $data[1]['name'] = '结单量';
        $data[1]['data'] = array_values($count);
        
        //入库
        $map = array('status' => 6);
        
        if (IS_POST) {
        
            if (!empty(I('post.startTime'))) {
                $startTime = strtotime(I('post.startTime'));
            } else {
                $startTime = strtotime('today');
            }
        
            if (!empty(I('post.endTime'))) {
                $endTime = strtotime(I('post.endTime'));
            } else {
                $endTime = time();
            }
        
            $map['clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else if ($day == 2) { /** 昨日 */
            $map['clearing_time'] = array(array('egt', strtotime('yesterday')), array('lt', strtotime('today')), 'AND');
        } else { /** 今日 */
            $map['clearing_time'] = array('egt', strtotime('today'));
        }
        
        $order_count = M('order')->where($map)->field('IF(city, city, 0) as city, count(*) as tp_count')
                        ->group('city')->select();
        $list = array();
        
        foreach ($order_count as $item) {
            $list[$item['city']] = $item['tp_count'];
        }
        unset($order_count);
        
        foreach ($address as $key => $value) {
            $tp_count = $list[$key];
        
            if ($key == 9999) {
                $tp_count = array_sum($list);
            }
            $count[$key] = (int)$tp_count;
        }
        
        $data[2]['name'] = '入库量';
        $data[2]['data'] = array_values($count);
        
        //今日订单取消量
        $map = array('status' => -1);
        
        if (IS_POST) {
        
            if (!empty(I('post.startTime'))) {
                $startTime = strtotime(I('post.startTime'));
            } else {
                $startTime = strtotime('today');
            }
        
            if (!empty(I('post.endTime'))) {
                $endTime = strtotime(I('post.endTime'));
            } else {
                $endTime = time();
            }
        
            $map['close_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
            $map['create_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else if ($day == 2) { /** 昨日 */
            $map['close_time'] = array(array('egt', strtotime('yesterday')), array('lt', strtotime('today')), 'AND');
            $map['create_time'] = array(array('egt', strtotime('yesterday')), array('lt', strtotime('today')), 'AND');
        } else { /** 今日 */
            $map['close_time'] = array('egt', strtotime('today'));
            $map['create_time'] = array('egt', strtotime('today'));
        }
        
        $order_count = M('order')->where($map)->field('IF(city, city, 0) as city, count(*) as tp_count')
                        ->group('city')->select();
        $list = array();
        
        foreach ($order_count as $item) {
            $list[$item['city']] = $item['tp_count'];
        }
        unset($order_count);
        
        foreach ($address as $key => $value) {
            $tp_count = $list[$key];
        
            if ($key == 9999) {
                $tp_count = array_sum($list);
            }
            $count[$key] = (int)$tp_count;
        }
        
        $data[3]['name'] = '取消量';
        $data[3]['data'] = array_values($count);
        
        //取消率
        $cancelcount = array();
        
        foreach ($count as $k => $val) {
            
            if (isset($orders[$k]) && $orders[$k] > 0) {
                $cancelcount[$k] = round($val/$orders[$k]*100);
            } else {
                $cancelcount[$k] = 0;
            }
        }
        unset($orders);
        
        $data[4]['name'] = '取消率(%)';
        $data[4]['data'] = array_values($cancelcount);
        
        $this->assign('count', $data);
        $this->assign('address', array_values($address));
        $this->display();
    }
    
    /**
     * 完结订单数据
     *
     * @return void
     */
    public function endOrders()
    {
        $address = array();
        $count = array();
        $list = array();
        $day = I('get.day/d');
        $map = array();
        $startDec = $this->startDec;
        
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
        }
        
        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }
        $map['status'] = 6;
        
        if (IS_POST) {
        
            if (!empty(I('post.startTime'))) {
                $startTime = strtotime(I('post.startTime'));
                $startTime = $startDec > $startTime ? $startDec : $startTime;
            } else {
                $startTime = strtotime('today');
            }
        
            if (!empty(I('post.endTime'))) {
                $endTime = strtotime(I('post.endTime'));
            } else {
                $endTime = time();
            }
        
            $map['clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else if ($day == 2) { /** 昨日 */
            $map['clearing_time'] = array(array('egt', strtotime('yesterday')), array('lt', strtotime('today')), 'AND');
        } else { /** 今日 */
            $map['clearing_time'] = array('egt', strtotime('today'));
        }
        
        $order_count = M('order')->where($map)->field('city, count(*) as tp_count')
                ->group('city')->select();
        
        foreach ($order_count as $item) {
            $list[$item['city']] = $item['tp_count'];
        }
        unset($order_count);
        
        foreach ($address as $key => $value) {
            $tp_count = $list[$key];
        
            if ($key == 9999) {
                $tp_count = array_sum($list);
            }
            $count[$key] = (int)$tp_count;
        }
        
        $this->assign('count', $count);
        $this->assign('address', $address);
        $this->display();
    }
    
    /**
     * 收入统计
     *
     * @return void
     */
    public function income()
    {
        $address = array();
        $count = array();
        $map = array();
        $list = array();
        $day = I('get.day/d');
        
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
        }
        
        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }
        $map['status'] = 6;
    
        if (IS_POST) {
    
            if (!empty(I('post.startTime'))) {
                $startTime = strtotime(I('post.startTime'));
            } else {
                $startTime = strtotime('today');
            }
    
            if (!empty(I('post.endTime'))) {
                $endTime = strtotime(I('post.endTime'));
            } else {
                $endTime = time();
            }
            
            $map['clearing_time'] = array(array('egt', $startTime), array('lt', $endTime), 'AND');
        } else if ($day == 2) { /** 昨日 */
            $map['clearing_time'] = array(array('egt', strtotime('yesterday')), array('lt', strtotime('today')), 'AND');
        } else {  /** 今日 */
            $map['clearing_time'] = array('egt', strtotime('today'));
        }
        
        $order_price = M('order')->where($map)->field('city, sum(actual_price) as actual_price')
                ->group('city')->select();
        
        foreach ($order_price as $item) {
            $list[$item['city']] = $item['actual_price'];
        }
        unset($order_price);
        
        foreach ($address as $key => $value) {
            $actual_price = $list[$key];
        
            if ($key == 9999) {
                $actual_price = array_sum($list);
            }
            $count[$key] = (int)$actual_price;
        }
    
        $this->assign('count', $count);
        $this->assign('address', $address);
        $this->display();
    }

    /**
     * 统计一个时间段内的单量趋势
     *
     * @return void
     */
    public function amount()
    {
        $startDec = $this->startDec;
        $dayCreateCount = array();
        $dayFinishCount = array();
        $rate_array = array();
        $startTime = strtotime('last Monday'); //默认本周一
        $endTime = strtotime('today');//默认今日 
        
        if (IS_POST) {
            
            if (!empty(I('post.startTime'))) {
                $startTime = strtotime(I('post.startTime'));
                $startTime = $startDec > $startTime ? $startDec : $startTime;
            }

            if (!empty(I('post.endTime'))) {
                $endTime = strtotime(I('post.endTime'));
            }
        }

        for ($time = $startTime; $time <= $endTime; $time += 86400) {
            $day_end = $time + 86400;
            $key = date('Y-m-d', $time);
        
            // 下单量
            $map = array();
            $map['create_time'] = array(array('egt', $time), array('lt', $day_end), 'AND');
            $dayCreateCount[$key] = (int)M('order')->where($map)->count();
        
            // 入库量
            $map = array();
            $map['create_time'] = array('egt', $startDec);
            $map['clearing_time'] = array(array('egt', $time), array('lt', $day_end), 'AND');
            $dayFinishCount[$key] = (int)M('order')->where($map)->count();
            
            $rate_array[$key] = round($dayFinishCount[$key] / $dayCreateCount[$key], 2) * 100;
        }

        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $this->assign('day_rate', $rate_array);
        $this->assign('dayCreateCount', $dayCreateCount);
        $this->assign('dayFinishCount', $dayFinishCount);
        $this->display();
    }


    /**
     * 实时地图
     *
     * @return void
     */
    public function map()
    {
        $data = array();
        $config = array(
                array('name' => '杭州市', 'data-x' => '120.125359', 'data-y' => '30.276803'),
                array('name' => '上海市', 'data-x' => '121.46719', 'data-y' => '31.232218'),
                array('name' => '广州市', 'data-x' => '113.267995', 'data-y' => '23.123438'),
                array('name' => '深圳市', 'data-x' => '114.087317', 'data-y' => '22.543839'),
                array('name' => '北京市', 'data-x' => '116.309927', 'data-y' => '39.98363'),
                array('name' => '成都市', 'data-x' => '104.06476', 'data-y' => '30.5702'),
                array('name' => '南京市', 'data-x' => '118.79647', 'data-y' => '32.05838'),
                array('name' => '武汉市', 'data-x' => '114.30525', 'data-y' => '30.59276'),
                array('name' => '重庆市', 'data-x' => '106.55073', 'data-y' => '29.56471'),
                array('name' => '苏州市', 'data-x' => '120.58319', 'data-y' => '31.29834'),
                array('name' => '无锡市', 'data-x' => '120.29685974', 'data-y' => '31.56039962'),
                array('name' => '郑州市', 'data-x' => '113.66077423', 'data-y' => '34.74753678'),

                );

        foreach($config as $key => $value) {

            foreach (session('addresses') as $v) {

                if ($v['cityname'] == $value['name']) {

                    $data[$key]['data-x'] = $value['data-x'];
                    $data[$key]['data-y'] = $value['data-y'];
                    $data[$key]['name'] = $value['name'];

                }
            }
        }

        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 流量监控
     *
     * @return void
     */
    public function conversion()
    {
        $count = M('conversion')->count();
        $orders = M('conversion')->where ('type != 0')->count();
        $Page = new \Think\Page($count, 55);
        $show = $Page->show();

        $start = $Page->firstRow;
        $end = $Page->firstRow + $Page->listRows;
        $sql = "select * from conversion where id >= (select id from conversion limit {$start}, 1) order by id desc limit {$end}";

        $list = M()->query($sql);

        $this->assign('orders',$orders);
        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display();
    }

    /**
     * 今日订单状态
     *
     * @return void
     */
    public function todayOrder()
    {
        $todaylist = array();
        
        $row = array(
            'name'       => '',
            'unmanage'   => 0,
            'finished'   => 0,
            'unfinished' => 0,
            'cancel'     => 0,
            'order'      => 0,
        );
        
        $address = array('9999' => '全国');
        
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
        }
        $address[0] = '其他';
        
        $map = array();
        $map['create_time'] = array(array('gt', strtotime('today')), array('lt', time()), 'AND');
        
        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }
        
        $list = M('order')->where($map)->field('IF(city, city, 0) as city, status')->select();
        
        foreach ($list as $key => $value) {
        
            if (isset($address['9999'])) {
                $row['name'] = $address['9999'];
                $todaylist['9999'] = isset($todaylist['9999']) ? $todaylist['9999'] : $row;
                $todaylist['9999']['order']++;
        
                if ($value['status'] == '1') {
                    $todaylist['9999']['unmanage']++;
                } elseif ($value['status'] == '6') {
                    $todaylist['9999']['finished']++;
                } elseif ($value['status'] == '-1') {
                    $todaylist['9999']['cancel']++;
                }
        
                $todaylist['9999']['unfinished'] = $todaylist['9999']['order'] - $todaylist['9999']['finished'];
            }
        
            if (!isset($address[$value['city']])) {
                continue;
            }
        
            if (!isset($todaylist[$value['city']])) {
                $row['name'] = $address[$value['city']];
                $todaylist[$value['city']] = $row;
            }
        
            $todaylist[$value['city']]['order']++;
        
            if ($value['status'] == '1') {
                $todaylist[$value['city']]['unmanage']++;
        
            } elseif ($value['status'] == '6') {
                $todaylist[$value['city']]['finished']++;
        
            } elseif ($value['status'] == '-1') {
                $todaylist[$value['city']]['cancel']++;
            }
        
            $todaylist[$value['city']]['unfinished'] =  $todaylist[$value['city']]['order'] - $todaylist[$value['city']]['finished'];
        }
        
        $this->assign('data', $todaylist);
        $this->assign('address', $address);
        $this->display();
    }
    
    /**
     * 订单情况统计报表
     *
     * @return void
     */
    public function orderDynamicExport()
    {
        $address = array();
        $map = array();
        $data = array();
        $row = array(
            'beforeUnfinished' => array('amount' => 0, 'price' => 0),
            'order' => array('amount' => 0, 'price' => 0),
            'finished' => array('amount' => 0, 'price' => 0),
            'finishRate' => array('rate' => 0),
            'cancel' => array('amount' => 0, 'price' => 0),
            'cancelRate' => array('rate' => 0),
            'unfinished' => array('amount' => 0, 'price' => 0),
        );
        
        $data[0] = array('name' => '总计', 'data' => $row);
        
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            $data[$city['city']] = array('name' => $city['cityname'], 'data' => $row);
        }
        $data[1] = array('name' => '邮寄', 'data' => $row);

        $post = I('post.');
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
        
        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }
        
        $startDec = $this->startDec;
        
        //前日未完成
        $map['create_time'] = array(array('egt', $startDec), array('lt', $startTime), 'AND');
        $map['clearing_time'] = array(array('egt', $startTime), array('eq', 0), 'OR');
        $map['status'] = array('neq', -1);
        $list = M('order')->where($map)->field('city, category, count(*) as count, sum(actual_price) as price')->group('city, category')->select();
        
        foreach ($list as $item) {
            //邮寄
            if ($item['category'] == 2) {
                $data[1]['data']['beforeUnfinished']['amount'] += $item['count'];
                $data[1]['data']['beforeUnfinished']['price'] += $item['price'];
            }
            
            if (isset($data['9999'])) {
                $data['9999']['data']['beforeUnfinished']['amount'] += $item['count'];
                $data['9999']['data']['beforeUnfinished']['price'] += $item['price'];
            }
            
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $data[0]['data']['beforeUnfinished']['amount'] += $item['count'];
            $data[0]['data']['beforeUnfinished']['price'] += $item['price'];
            $data[$item['city']]['data']['beforeUnfinished']['amount'] += $item['count'];
            $data[$item['city']]['data']['beforeUnfinished']['price'] += $item['price'];
        }
        
        //当日下单
        unset($map['clearing_time']);
        unset($map['status']);
        $map['create_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $list = M('order')->where($map)->field('city, category, actual_price')->select();
        
        foreach ($list as $item) {
            //邮寄
            if ($item['category'] == 2) {
                $data[1]['data']['order']['amount']++;
                $data[1]['data']['order']['price'] += $item['actual_price'];
            }
            
            if (isset($data['9999'])) {
                $data['9999']['data']['order']['amount']++;
                $data['9999']['data']['order']['price'] += $item['actual_price'];
            }
            
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $data[0]['data']['order']['amount']++;
            $data[0]['data']['order']['price'] += $item['actual_price'];
            $data[$item['city']]['data']['order']['amount']++;
            $data[$item['city']]['data']['order']['price'] += $item['actual_price'];
        }
        
        //当日未完成
        $map['create_time'] = array(array('egt', $startDec), array('elt', $endTime), 'AND');
        $map['clearing_time'] = array(array('gt', $endTime), array('eq', 0), 'OR');
        $map['status'] = array('neq', -1);
        $list = M('order')->where($map)->field('city, category, count(*) as count, sum(actual_price) as price')->group('city, category')->select();
        
        foreach ($list as $item) {
            //邮寄
            if ($item['category'] == 2) {
                $data[1]['data']['unfinished']['amount'] += $item['count'];
                $data[1]['data']['unfinished']['price'] += $item['price'];
            }
        
            if (isset($data['9999'])) {
                $data['9999']['data']['unfinished']['amount'] += $item['count'];
                $data['9999']['data']['unfinished']['price'] += $item['price'];
            }
        
            if (!isset($address[$item['city']])) {
                continue;
            }
        
            $data[0]['data']['unfinished']['amount'] += $item['count'];
            $data[0]['data']['unfinished']['price'] += $item['price'];
            $data[$item['city']]['data']['unfinished']['amount'] += $item['count'];
            $data[$item['city']]['data']['unfinished']['price'] += $item['price'];
        }
        
        //当日完成
        $map['create_time'] = array('egt', $startDec);
        $map['clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['status'] = array('neq', -1);
        $list = M('order')->where($map)->field('city, category, count(*) as count, sum(actual_price) as price')->group('city, category')->select();
        
        foreach ($list as $item) {
            //邮寄
            if ($item['category'] == 2) {
                $data[1]['data']['finished']['amount'] += $item['count'];
                $data[1]['data']['finished']['price'] += $item['price'];
            }
        
            if (isset($data['9999'])) {
                $data['9999']['data']['finished']['amount'] += $item['count'];
                $data['9999']['data']['finished']['price'] += $item['price'];
            }
        
            if (!isset($address[$item['city']])) {
                continue;
            }
        
            $data[0]['data']['finished']['amount'] += $item['count'];
            $data[0]['data']['finished']['price'] += $item['price'];
            $data[$item['city']]['data']['finished']['amount'] += $item['count'];
            $data[$item['city']]['data']['finished']['price'] += $item['price'];
        }
        
        //当日取消
        unset($map['clearing_time']);
        $map['close_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['create_time'] = array('elt', $endTime);
        $map['status'] = -1;
        $list = M('order')->where($map)->field('city, category, count(*) as count, sum(actual_price) as price')->group('city, category')->select();
        
        foreach ($list as $item) {
            //邮寄
            if ($item['category'] == 2) {
                $data[1]['data']['cancel']['amount'] += $item['count'];
                $data[1]['data']['cancel']['price'] += $item['price'];
            }
        
            if (isset($data['9999'])) {
                $data['9999']['data']['cancel']['amount'] += $item['count'];
                $data['9999']['data']['cancel']['price'] += $item['price'];
            }
        
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $data[0]['data']['cancel']['amount'] += $item['count'];
            $data[0]['data']['cancel']['price'] += $item['price'];
            $data[$item['city']]['data']['cancel']['amount'] += $item['count'];
            $data[$item['city']]['data']['cancel']['price'] += $item['price'];
        }
        
        $this->assign('startTime', date('Y-m-d', $startTime));
        $this->assign('endTime', date('Y-m-d', $endTime));
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 订单完成情况统计报表
     *
     * @return void
     */
    public function orderFinishExport()
    {
        $address = array();
        $map = array();
        $data = array();
        $startDec = $this->startDec;
        $row = array();
        
        $row[0] = array('amount' => 0, 'price' => 0);
        
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            $row[$city['city']] = array('amount' => 0, 'price' => 0);
        }
        $row[1] = array('amount' => 0, 'price' => 0);
        
        $post = I('post.');
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
        
        for ($i = $startTime; $i <= $endTime; $i += 86400) {
            $k = date('Y-m-d', $i);
            
            $data[$k] = $row;
        }
        
        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }
        $map['clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['create_time'] = array(array('egt', $startDec), array('elt', $endTime), 'AND');
        $map['status'] = array('neq', -1);
        
        $list = M('order')-> where($map)->field("city, category, actual_price, clearing_time")->order('clearing_time asc')->select();
        
        foreach ($list as $item) {
            $k = date('Y-m-d', $item['clearing_time']);
            
            //邮寄
            if ($item['category'] == 2) {
                $data[$k][1]['data'][$k]['amount']++;
                $data[$k][1]['data'][$k]['price'] += $item['actual_price'];
            }
            
            if (isset($address['9999'])) {
                $data[$k]['9999']['amount']++;
                $data[$k]['9999']['price'] += $item['actual_price'];
            }
            
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $data[$k][0]['amount']++;
            $data[$k][0]['price'] += $item['actual_price'];
            $data[$k][$item['city']]['amount']++;
            $data[$k][$item['city']]['price'] += $item['actual_price'];
        }
        
        $this->assign('startTime', date('Y-m-d', $startTime));
        $this->assign('endTime', date('Y-m-d', $endTime));
        $this->assign('address', $address);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 收款统计日报表
     *
     * @return void
     */
    public function orderCashExport()
    {
        $address = array();
        $map = array();
        $data = array();
        $startDec = $this->startDec;
        
        $row = array(
            'revenue' => array('amount' => 0, 'price' => 0),
            'alipay' => array('amount' => 0, 'price' => 0),
            'weixin' => array('amount' => 0, 'price' => 0),
            'bank' => array('amount' => 0, 'price' => 0),
            'cash' => array('amount' => 0, 'price' => 0),
            'total' => array('amount' => 0, 'price' => 0),
            'noPay' => array('amount' => 0, 'price' => ''),
            'yufu' => array('amount' => 0, 'price' => '0'),
        );
        
        $data[0] = array('name' => '总计', 'data' => $row);
                
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            $data[$city['city']] = array('name' => $city['cityname'], 'data' => $row);
        }
        $data[1] = array('name' => '邮寄', 'data' => $row);

        $post = I('post.');
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
        
        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }
        
        $map['clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['create_time'] = array('egt', $startDec);
        $map['status'] = array('neq', -1);
        
        $list = M('order')->where($map)->field('city, category, payment_method, count(*) as count, sum(actual_price) as price, pay_type, is_clearing')
                ->group('city, category, payment_method')->select();

        foreach ($list as $item) {
            //邮寄
            if ($item['category'] == 2) {
                $data[1]['data']['revenue']['amount'] += $item['count'];
                $data[1]['data']['revenue']['price'] += $item['price'];
                $data[1]['data']['total']['amount'] += $item['count'];
                $data[1]['data']['total']['price'] += $item['price'];
            }
            
            //全国
            if (isset($data['9999'])) {
                $data['9999']['data']['revenue']['amount'] += $item['count'];
                $data['9999']['data']['revenue']['price'] += $item['price'];
                $data['9999']['data']['total']['amount'] += $item['count'];
                $data['9999']['data']['total']['price'] += $item['price'];
            }
            
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $data[0]['data']['revenue']['amount'] += $item['count'];
            $data[0]['data']['revenue']['price'] += $item['price'];
            $data[0]['data']['total']['amount'] += $item['count'];
            $data[0]['data']['total']['price'] += $item['price'];
            
            $data[$item['city']]['data']['total']['amount'] += $item['count'];
            $data[$item['city']]['data']['total']['price'] += $item['price'];
            $data[$item['city']]['data']['revenue']['amount'] += $item['count'];
            $data[$item['city']]['data']['revenue']['price'] += $item['price'];
            
            if ($item['payment_method'] == 1) { //支付宝
                $data[0]['data']['alipay']['amount'] += $item['count'];
                $data[0]['data']['alipay']['price'] += $item['price'];
                $data[$item['city']]['data']['alipay']['amount'] += $item['count'];
                $data[$item['city']]['data']['alipay']['price'] += $item['price'];
                
                if ($item['category'] == 2) {
                    $data[1]['data']['alipay']['amount'] += $item['count'];
                    $data[1]['data']['alipay']['price'] += $item['price'];
                }


            } else if ($item['payment_method'] == 2) { //微信
                $data[0]['data']['weixin']['amount'] += $item['count'];
                $data[0]['data']['weixin']['price'] += $item['price'];
                $data[$item['city']]['data']['weixin']['amount'] += $item['count'];
                $data[$item['city']]['data']['weixin']['price'] += $item['price'];
                
                if ($item['category'] == 2) {
                    $data[1]['data']['weixin']['amount'] += $item['count'];
                    $data[1]['data']['weixin']['price'] += $item['price'];
                }


            } else if ($item['payment_method'] == 3) { //网银
                $data[0]['data']['bank']['amount'] += $item['count'];
                $data[0]['data']['bank']['price'] += $item['price'];
                $data[$item['city']]['data']['bank']['amount'] += $item['count'];
                $data[$item['city']]['data']['bank']['price'] += $item['price'];
                
                if ($item['category'] == 2) {
                    $data[1]['data']['bank']['amount'] += $item['count'];
                    $data[1]['data']['bank']['price'] += $item['price'];
                }


            } else if ($item['payment_method'] == 4) { //现金
                $data[0]['data']['cash']['amount'] += $item['count'];
                $data[0]['data']['cash']['price'] += $item['price'];
                $data[$item['city']]['data']['cash']['amount'] += $item['count'];
                $data[$item['city']]['data']['cash']['price'] += $item['price'];
                
                if ($item['category'] == 2) {
                    $data[1]['data']['cash']['amount'] += $item['count'];
                    $data[1]['data']['cash']['price'] += $item['price'];
                }


            }
        }
        
        //当日已结单未收款
        unset($map['clearing_time']);
        $map['end_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['create_time'] = array('egt', $startDec);
        $map['status'] = 5;
        
        $list = M('order')->where($map)->field('city, category, count(*) as count, pay_type, is_clearing')
                ->group('city, category')->select();

        foreach ($list as $item) {
            //邮寄
            if ($item['category'] == 2) {
                $data[1]['data']['revenue']['amount'] += $item['count'];
                $data[1]['data']['noPay']['amount'] += $item['count'];
            }
        
            //全国
            if (isset($data['9999'])) {
                $data['9999']['data']['revenue']['amount'] += $item['count'];
                $data['9999']['data']['noPay']['amount'] += $item['count'];
            }
        
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $data[0]['data']['revenue']['amount'] += $item['count'];
            $data[0]['data']['noPay']['amount'] += $item['count'];
            $data[$item['city']]['data']['revenue']['amount'] += $item['count'];
            $data[$item['city']]['data']['noPay']['amount'] += $item['count'];
        }

        //当日预付款
        $map = array();
        $map['create_time'] = array('egt', $startTime);
        $map['pay_type'] = 2;
        $map['status'] = 6;
        $list = M('order')->where($map)->field('city, count(*) as count, pay_type, is_clearing, sum(paid_amount) as price')
            ->group('city')->select();

        foreach ($list as $item) {

            if (!isset($address[$item['city']])) {
                continue;
            }
            $data[$item['city']]['data']['yufu']['amount'] += $item['count'];
            $data[$item['city']]['data']['yufu']['price'] += $item['price'];
        }
        
        $this->assign('startTime', date('Y-m-d', $startTime));
        $this->assign('endTime', date('Y-m-d', $endTime));
        $this->assign('data', $data);

        $this->assign('address', $address);

        $this->display();
    }

    /**
     * 毛利统计日报表
     *
     * @return void
     */
    public function orderGross()
    {
        $address = array();
        $map = array();
        $data = array();
        $startDec = $this->startDec;
        $row = array(
            'revenue' => array('amount' => 0, 'price' => 0),
            'fittings' => array('price' => 0),
            'divided' => array('price' => 0),
            'waste' => array('price' => 0),
            'gross' => array('price' => 0),
            'rate' => 0,
        );
        
        $data[0] = array('name' => '总计', 'data' => $row);
        
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            $data[$city['city']] = array('name' => $city['cityname'], 'data' => $row);
        }
        $data[1] = array('name' => '邮寄', 'data' => array('revenue' => array('amount' => 0, 'price' => 0)));
        
        $post = I('post.');
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
        
        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }
        
        $map['clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['status'] = array('neq', -1);
        
        $list = M('order')->where($map)->field('city, category, count(*) as count, sum(actual_price) as price')->group('city, category')->select();
        
        foreach ($list as $item) {
            //邮寄
            if ($item['category'] == 2) {
                $data[1]['data']['revenue']['amount'] += $item['count'];
                $data[1]['data']['revenue']['price'] += $item['price'];
            }
            
            //全国
            if (isset($data['9999'])) {
                $data['9999']['data']['revenue']['amount'] += $item['count'];
                $data['9999']['data']['revenue']['price'] += $item['price'];
            }
            
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $data[0]['data']['revenue']['amount'] += $item['count'];
            $data[0]['data']['revenue']['price'] += $item['price'];
            $data[$item['city']]['data']['revenue']['amount'] += $item['count'];
            $data[$item['city']]['data']['revenue']['price'] += $item['price'];
        }
        
        //当日配件成本
        unset($map['status']);
        $map['o.status'] = array('neq', -1);
        $list = M('order')->join('o left join stock s on s.order_id = o.id')
                ->where($map)->field('o.city, sum(s.price) as price')->group('o.city')->select();

        foreach ($list as $item) {
            //全国
            if (isset($data['9999'])) {
                $data['9999']['data']['fittings']['price'] += $item['price'];
            }
            
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $data[0]['data']['fittings']['price'] += $item['price'];
            $data[$item['city']]['data']['fittings']['price'] += $item['price'];
        }
        
        //当日工程师预计收益
        unset($map['ei.type']);
        $list = M('order')->join('o left join engineer_divide ed on ed.order_id = o.id')
                ->where($map)->field('o.city, sum(ed.earning) as price')->group('o.city')->select();
        
        foreach ($list as $item) {
            //全国
            if (isset($data['9999'])) {
                $data['9999']['data']['divided']['price'] += $item['price'];
            }
            
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $data[0]['data']['divided']['price'] += $item['price'];
            $data[$item['city']]['data']['divided']['price'] += $item['price'];
        }
        
        //当日废料收入
        unset($map['clearing_time']);
        unset($map['o.status']);
        $map['wr.status'] = array('gt', -2);
        $map['time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $list = M('waste_refund')->join('wr left join organization org on wr.organization_id = org.id')
                ->where($map)->field('org.city, wr.wastes')->select();
        
        foreach ($list as $item) {
            $wastes = json_decode($item['wastes'], true);
            
            if (!$wastes) {
                continue;
            }
            
            $price = 0;
            $waste_id = array();
            $waste_amount = array();
            
            foreach ($wastes as $val) {
               $waste_amount[$val['waste_id']] = $val['amount'];
               $waste_id[] = $val['waste_id'];
            }
            
            $waste_prices = M('waste')->where(array('id' => array('in', $waste_id)))->getField('id, price');
            
            foreach ($waste_prices as $id => $waste_price) {
                $price += $waste_price * $waste_amount[$id];
            }
            
            //全国
            if (isset($data['9999'])) {
                $data['9999']['data']['waste']['price'] += $price;
            }
            
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $data[0]['data']['waste']['price'] += $price;
            $data[$item['city']]['data']['waste']['price'] += $price;
        }
        
        $this->assign('startTime', date('Y-m-d', $startTime));
        $this->assign('endTime', date('Y-m-d', $endTime));
        $this->assign('address', $address);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 毛利统计日报表导出明细
     *
     * @return void
     */
    public function orderGrossDetailExport()
    {
        $address = array();
        $map = array();
        //$startDec = $this->startDec;
        $startDec = strtotime(date('2016-12-01'));
        $exorders = array();
        
        $columns = array(
            '订单ID'  => '订单ID',
            '订单编号'   => '订单编号',
            '实际收款'  => '实际收款',
            '机型'   => '机型',
            '故障'   => '故障',
            '消耗物料'  => '消耗物料',
            '物料成本'  => '物料成本',
            '产生废料'  => '产生废料',
            '废料收入'  => '废料收入',
            '工程师'  => '工程师',
            '工程师分成'  => '工程师分成',
            '毛利'  => '毛利',
            '下单时间'   => '下单时间',
            '入库时间'  => '入库时间',
            '城市'    => '城市',
        );
        
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            
            $exorders[$city['cityname']][] = $columns;
        }

        // 邮寄
        $exorders['邮寄'][] = $columns;
        
        $post = I('post.');
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
        
        if (!isset($address['9999'])) {
            $map['o.city'] = array('in', array_keys($address));
        }
        
        $map['o.clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['o.status'] = array('neq', -1);
        
        $list = M('order')->join('o left join engineer e on o.engineer_id = e.id')
                //收益
                ->join('left join engineer_divide ed on o.id = ed.order_id')
                //物料
                ->join('left join stock s on s.order_id = o.id')
                ->join('left join fitting f on s.fitting_id = f.id')
                //->join('left join `address` on address.id=o.city')
                ->where($map)
                ->group('o.id')
                ->getField("o.id, o.category, o.city, o.number, o.actual_price, o.create_time,
                            o.clearing_time, o.phone_name, e.name, ed.earning, o.malfunction_description,
                            sum(s.price) as fittings_price,
                            group_concat(concat(f.title, '(', f.number, ')')) as fittings
                        ");

        foreach ($list as &$v) {
            $v['address_name'] = M('address')->where(array('id' => $v['city']))->getField('name');
        }

        //故障
        $order_malfunctions = array();
        
        if ($list) {
            $where = array('op.order_id' => array('in', array_keys($list)));
            $order_malfunctions = M('order_phomal')->join('op left join phone_malfunction pm on op.phomal_id = pm.id')
                                ->where($where)->group('op.order_id')
                                ->getField('op.order_id, group_concat(malfunction) as malfunctions');
        }



        $waste_list = array();
        
        if ($list) {
            
            //当日废料收入
            $map = array();
            if (!isset($address['9999'])) {
                $map['org.city'] = array('in', array_keys($address));
            }
            
            $map['wr.status'] = array('gt', -2);
            $map['time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
            
            $waste_refund = M('waste_refund')->join('wr left join organization org on wr.organization_id = org.id')
                    ->where($map)->field('wr.order_id, org.city, wr.wastes')->select();
            
            foreach ($waste_refund as $item) {
                
                if (!isset($address[$item['city']])) {
                    continue;
                }
                
                $wastes = json_decode($item['wastes'], true);
            
                if (!$wastes) {
                    continue;
                }
            
                $price = 0;
                $waste = array();
                $waste_id = array();
                $waste_amount = array();
                
                foreach ($wastes as $val) {
                    $waste_amount[$val['waste_id']] = $val['amount'];
                    $waste_id[] = $val['waste_id'];
                    $waste[] = $val['name'] .'*'.$val['amount'];
                }
                
                $waste_prices = M('waste')->where(array('id' => array('in', $waste_id)))->getField('id, price');
                
                foreach ($waste_prices as $id => $waste_price) {
                    $price += $waste_price * $waste_amount[$id];
                }
            
                $waste_list[$item['city']][$item['order_id']]['waste'] = implode(',', $waste);
                $waste_list[$item['city']][$item['order_id']]['price'] = $price;
            }
        }

        foreach ($list as $key => $item) {
            
            $waste = !empty($waste_list[$item['city']][$item['id']]['waste']) ? $waste_list[$item['city']][$item['id']]['waste'] : '';
            $price = !empty($waste_list[$item['city']][$item['id']]['price']) ? $waste_list[$item['city']][$item['id']]['price'] : '0';
            
            $item['malfunctions'] = $order_malfunctions[$item['id']];
            $item['fittings'] = implode(',', array_unique(explode(',', $item['fittings'])));
            
            $row = array(
                '订单ID'  => $item['id'],
                '订单编号'   => $item['number'],
                '实际收款'  => $item['actual_price'],
                '机型'  => $item['phone_name'],
                '故障'  => $item['malfunctions'] ? $item['malfunctions'] : $item['malfunction_description'],
                '消耗物料'  => $item['fittings'],
                '物料成本'  => $item['fittings_price'],
                '产生废料'  => $waste,
                '废料收入'  => $price,
                '工程师'  => $item['name'],
                '工程师分成'  => $item['earning'],
                '毛利'  => $item['actual_price'] - $item['fittings_price'] - $item['earning'] + $price,
                '下单时间'   => date('Y-m-d H:i:s', $item['create_time']),
                '入库时间'  => date('Y-m-d H:i:s', $item['clearing_time']),
                '城市'  => $item['address_name'],
            );

            if (isset($address['9999'])) {
                $exorders[$address[$item['city']]][] = $row;
            }
            
            $exorders[$address[$item['city']]][] = $row;


            //邮寄
            if ($item['category'] == 2) {
                $exorders['邮寄'][] = $row;
            }
        }

        $filename = '收款统计日报表(明细)-('.date('Y-m-d', $startTime) . '至' . date('Y-m-d', $endTime) .')';
        $this->exportData($filename, $exorders, true);
    }

    /**
     * 毛利统计日报表导出汇总
     *
     * @return void
     */
    public function orderGrossExport()
    {
        $address = array();
        $map = array();
        $startDec = $this->startDec;
        $exorders = array();
        $exorders[0] = array('', '当日营业收入', '', '当日配件成本', '当日工程师收益', '当日废料收入', '当日毛利', '当日毛利率');
        $exorders[1] = array('', '单量', '金额', '金额', '金额', '金额', '金额', '金额');
        $exorders[2] = array('总计', '0', '0', '0', '0', '0', '0', '0');
        
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
        
            $exorders[$city['city']] = array($city['cityname'], '0', '0', '0', '0', '0', '0', '0');
        }
        
        $post = I('post.');
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
        
        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }
        
        $map['clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['status'] = array('neq', -1);
        
        $list = M('order')->where($map)->field('city, count(*) as count, sum(actual_price) as price')->group('city')->select();

        foreach ($list as $item) {
            //全国
            if (isset($exorders['9999'])) {
                $exorders['9999'][1] += $item['count'];
                $exorders['9999'][2] += $item['price'];
            }
        
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $exorders[2][1] += $item['count'];
            $exorders[2][2] += $item['price'];
            $exorders[$item['city']][1] += $item['count'];
            $exorders[$item['city']][2] += $item['price'];
        }
        
        //当日配件成本
        unset($map['status']);
        $map['o.status'] = array('neq', -1);
        $list = M('order')->join('o left join stock s on s.order_id = o.id')
                ->where($map)->field('o.city, sum(s.price) as price')->group('o.city')->select();

        foreach ($list as $item) {
            //全国
            if (isset($exorders['9999'])) {
                $exorders['9999'][3] += $item['price'];
            }
        
            if (!isset($address[$item['city']])) {
                continue;
            }
        
            $exorders[2][3] += $item['price'];
            $exorders[$item['city']][3] += $item['price'];
        }
        
        //当日工程师预计收益
        $list = M('order')->join('o left join engineer_divide ed on ed.order_id = o.id')
                ->where($map)->field('o.city, sum(ed.earning) as price')->group('o.city')->select();
        
        foreach ($list as $item) {
            //全国
            if (isset($exorders['9999'])) {
                $exorders['9999'][4] += $item['price'];
            }
        
            if (!isset($address[$item['city']])) {
                continue;
            }
        
            $exorders[2][4] += $item['price'];
            $exorders[$item['city']][4] += $item['price'];
        }
        
        //当日废料收入
        unset($map['clearing_time']);
        unset($map['o.status']);
        $map['wr.status'] = array('gt', -2);
        $map['time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $list = M('waste_refund')->join('wr left join organization org on wr.organization_id = org.id')
                ->where($map)->field('org.city, wr.wastes')->select();
        
        foreach ($list as $item) {
            $wastes = json_decode($item['wastes'], true);
        
            if (!$wastes) {
                continue;
            }
        
            $price = 0;
            $waste_id = array();
            $waste_amount = array();
            
            foreach ($wastes as $val) {
                $waste_amount[$val['waste_id']] = $val['amount'];
                $waste_id[] = $val['waste_id'];
            }
            
            $waste_prices = M('waste')->where(array('id' => array('in', $waste_id)))->getField('id, price');
            
            foreach ($waste_prices as $id => $waste_price) {
                $price += $waste_price * $waste_amount[$id];
            }
        
            //全国
            if (isset($address['9999'])) {
                $exorders['9999'][5] += $price;
            }
        
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $exorders[2][5] += $price;
            $exorders[$item['city']][5] += $price;
        }
        
        foreach ($exorders as $k => &$v) {
        
            if ($k <= 1) continue;
            
            $v[1] = $v[1] ? $v[1] : '0';
            $v[2] = $v[2] ? $v[2] : '0'; 
            $v[3] = $v[3] ? $v[3] : '0';
            $v[4] = $v[4] ? $v[4] : '0';
            $v[5] = $v[5] ? $v[5] : '0';
        
            if ($v[2] > 0) {
                $v[6] = $v[2] - $v[3] - $v[4] + $v[5];
                $v[7] = round($v[6] / $v[2] * 100, 2) . '%';
            }
        }
        
        $filename = '收款统计日报表(汇总)-('.date('Y-m-d', $startTime) . '至' . date('Y-m-d', $endTime) .')';
        $this->exportData($filename, $exorders);
    }

    /**
     * 维修时效统计报表
     *
     * @return void
     */
    public function orderMaintainTime()
    {
        $address = array();
        $map = array();
        $data = array();
        $startDec = $this->startDec;
        
        $data[] = array('name' => '汇总', 'avg_time' => 0, 'avg_count' => 0, 'max_time' => 0, 'min_time' => 0);
        
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            
            $data[$city['city']] = array('name' => $city['cityname'], 'avg_time' => 0, 'avg_count' => 0, 'max_time' => 0, 'min_time' => 0);
        }
        
        $post = I('post.');
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
        
        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }
        
        $map['create_time'] = array('egt', $startDec);
        $map['clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['status'] = 6;
        
        $list = M('order')->where($map)->field('city, count(*), (sum(clearing_time - create_time) / count(*)) as avg_time')->group('city')->select();
        
        foreach ($list as $item) {
            //全国
            if (isset($address['9999'])) {
                $data['9999']['avg_time'] += $item['avg_time'];
                $data['9999']['avg_count']++;
            }
            
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $data[0]['avg_time'] += $item['avg_time'];
            $data[0]['avg_count']++;
            
            $data[$item['city']]['avg_time'] = round($item['avg_time'] / 60, 2);
        }
        
        $list = M('order')->where($map)->field('city, max(clearing_time - create_time) as max_time')->group('city')->select();
        
        foreach ($list as $item) {
            $item['max_time'] = round($item['max_time'] / 60, 2);
             
            //全国
            if (isset($address['9999'])) {
                $data['9999']['max_time'] = $item['max_time'];
            }
        
            if (!isset($address[$item['city']])) {
                continue;
            }
        
            $data[$item['city']]['max_time'] = $item['max_time'];
            $data[$item['city']]['max_time'] = $item['max_time'];
        }
        
        $list = M('order')->where($map)->field('city, min(clearing_time - create_time) as min_time')->group('city')->select();
        
        foreach ($list as $item) {
            $item['min_time'] = round($item['min_time'] / 60, 2);
            
            //全国
            if (isset($address['9999'])) {
                $data['9999']['min_time'] = $item['min_time'];
            }
        
            if (!isset($address[$item['city']])) {
                continue;
            }
        
            $data[$item['city']]['min_time'] = $item['min_time'];
            $data[$item['city']]['min_time'] = $item['min_time'];
        }

        $this->assign('startTime', date('Y-m-d', $startTime));
        $this->assign('endTime', date('Y-m-d', $endTime));
        $this->assign('address', $address);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 外屏统计报表
     *
     * @return void
     */
    public function screenMalfunction()
    {
        $this->assign('address', $this->address);

        $get = I('param.');
        $startTime = empty($get['os_start_time']) ? strtotime(date('Y-m-d')) : strtotime($get['os_start_time']);
        $endTime = empty($get['os_end_time']) ? $startTime + 24*60*60-1 : strtotime($get['os_end_time']) + 24*60*60-1;

        $all_data = array();

        $sql = "select p.id, p.alias, wal.wastes from `order` as o
                left join order_phone_malfunction opm on o.id = opm.order_id
                left join phone_malfunction pm on opm.phone_malfunction_id = pm.id
                left join malfunction m on pm.malfunction_id = m.id
                left join phone p on o.phone_id = p.id
                left join waste_apply_log wal on o.id = wal.order_id
                where o.clearing_time >= {$startTime} and o.clearing_time <= {$endTime} and o.status <> -1 and m.name = '外屏碎(显示正常)' order by o.phone_name";

        $query = M()->query($sql);

        $waste_list = M('waste')->getField('id, price_recycle');

        $new_query = array();
        foreach ($query as $one_order) {
            $info = array();

            $all_price = 0;
            $wastes = json_decode($one_order['wastes'], true);

            $screen_name = '';
            foreach ($wastes as $waste) {
                if (strstr($waste['name'], '屏幕')) {
                    $all_price += $waste_list[$waste['id']];
                    $screen_name = $waste['name'];
                } else {
                    continue;
                }
            }
            if (empty($screen_name)) {
                continue;
            }
            $info['alias'] = $one_order['alias'];
            $info['wastes_price'] = $all_price;
            $info['waste'] = $screen_name;

            $new_query[] = $info;
        }

        $middle_result = array();

        foreach ($new_query as $record) {
            $key = $record['alias'].'+'.$record['waste'];
            $middle_result[$key][] = $record;
        }


        $final_fittings = array();
        $total_amount = 0;
        $total_price = 0;
        foreach ($middle_result as $f_key => $af_record) {
            $info = array();
            $info['phone_name'] = $af_record[0]['alias'];
            $info['waste'] = $af_record[0]['waste'];
            $info['amount'] = count($af_record);
            $total_amount += $info['amount'];
            $w_price = 0;
            foreach ($af_record as $one) {
                $w_price += $one['wastes_price'];
            }
            $total_price += $w_price;
            $info['wastes_price'] = $w_price;
            $final_fittings[$f_key] = $info;
        }

        //$final_fittings包含所有下单的机型物料
        $specific_total = array();
        foreach ($this->address as $key_a => $value) {
            $sql = "select p.id, p.alias, wal.wastes from `order` as o
                    left join customer as c on o.customer_id=c.id
                    left join order_phone_malfunction opm on o.id = opm.order_id
                    left join phone_malfunction pm on opm.phone_malfunction_id = pm.id
                    left join malfunction m on pm.malfunction_id = m.id
                    left join waste_apply_log wal on o.id = wal.order_id
                    left join phone p on o.phone_id = p.id
                    where c.address like '%{$value}%' and o.clearing_time >= {$startTime} and o.clearing_time <= {$endTime} and o.status <> -1 and m.name = '外屏碎(显示正常)' 
                    order by o.phone_name";

            $query = M('order')->query($sql);
            $new_query = array();

            foreach ($query as $one_area_order) {
                $info = array();
                $all_price = 0;
                $wastes = json_decode($one_area_order['wastes'], true);

                $screen_name = '';
                foreach ($wastes as $waste) {
                    if (strstr($waste['name'], '屏幕')) {
                        $all_price += $waste_list[$waste['id']];
                        $screen_name = $waste['name'];
                    } else {
                        continue;
                    }
                }
                if (empty($screen_name)) {
                    continue;
                }
                $info['alias'] = $one_area_order['alias'];
                $info['wastes_price'] = $all_price;
                $info['waste'] = $screen_name;

                $new_query[] = $info;
            }


            $middle_result = array();

            foreach ($new_query as $record) {
                $key = $record['alias'].'+'.$record['waste'];
                $middle_result[$key][] = $record;
            }


            $s_amount = 0;
            $s_price = 0;
            $final_result = array();
            foreach ($middle_result as $n_key => $af_record) {
                $info = array();
                $info['phone_name'] = $af_record[0]['alias'];
                $info['waste'] = $af_record[0]['waste'];
                $info['amount'] = count($af_record);
                $s_amount += $info['amount'];
                $w_price = 0;
                foreach ($af_record as $one) {
                    $w_price += $one['wastes_price'];
                }
                $s_price += $w_price;
                $info['wastes_price'] = $w_price;
                $final_result[$n_key] = $info;
            }

            $specific_total[$key_a]['amount'] = $s_amount;
            $specific_total[$key_a]['price'] = $s_price;
            $all_data[$key_a] = $final_result;
        }

        $sql = "select p.id, p.alias, wal.wastes from `order` as o
                left join order_phone_malfunction opm on o.id = opm.order_id
                left join phone_malfunction pm on opm.phone_malfunction_id = pm.id
                left join malfunction m on pm.malfunction_id = m.id
                left join waste_apply_log wal on o.id = wal.order_id
                left join phone p on o.phone_id = p.id
                where o.category = 2 
                and o.clearing_time >= {$startTime} 
                and o.clearing_time <= {$endTime} 
                and o.status <> -1 
                and m.name = '外屏碎(显示正常)' 
                order by o.phone_name";

        $query = M('order')->query($sql);

        $new_query = array();
        foreach ($query as $one_area_order) {
            $info = array();

            $all_price = 0;
            $wastes = json_decode($one_area_order['wastes'], true);

            $screen_name = '';
            foreach ($wastes as $waste) {
                if (strstr($waste['name'], '屏幕')) {
                    $all_price += $waste_list[$waste['id']];
                    $screen_name = $waste['name'];
                } else {
                    continue;
                }
            }
            if (empty($screen_name)) {
                continue;
            }
            $info['alias'] = $one_area_order['alias'];
            $info['wastes_price'] = $all_price;
            $info['waste'] = $screen_name;

            $new_query[] = $info;
        }


        $middle_result = array();

        foreach ($new_query as $record) {
            $key = $record['alias'].'+'.$record['waste'];
            $middle_result[$key][] = $record;
        }


        $s_amount = 0;
        $s_price = 0;
        $final_result = array();
        foreach ($middle_result as $n_key => $af_record) {
            $info = array();
            $info['phone_name'] = $af_record[0]['alias'];
            $info['waste'] = $af_record[0]['waste'];
            $info['amount'] = count($af_record);
            $s_amount += $info['amount'];
            $w_price = 0;
            foreach ($af_record as $one) {
                $w_price += $one['wastes_price'];
            }
            $s_price += $w_price;
            $info['wastes_price'] = $w_price;
            $final_result[$n_key] = $info;
        }

        $specific_total['other']['amount'] = $s_amount;
        $specific_total['other']['price'] = $s_price;
        $all_data['other'] = $final_result;

        $this->assign('specific_total', $specific_total);
        $this->assign('total_amount', $total_amount);
        $this->assign('total_price', $total_price);
        $this->assign('final_fittings', $final_fittings);
        $this->assign('all_data', $all_data);
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $this->display();
    }
    
    /**
     * 工程师日单量
     *
     * @return void
     */
    public function engineerOrders()
    {
        if (IS_POST) {
            $address = array();
            $map = array();
            $startDec = $this->startDec;
            $post = I('post.');
            
            $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
            $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
            $startTime = $startTime < $startDec ? $startDec : $startTime;
            $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
            
            $exorders = array();
            $exorders[] = array('工程师', '地区', '单量');
            
            foreach (session('addresses') as $city) {
                $address[$city['city']] = $city['cityname'];
            }
            
            if (!isset($address['9999'])) {
                $map['o.city'] = array('in', array_keys($address));
            }
            
            $map['o.clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
            $map['o.status'] = array('eq', 6);
            $map['o.engineer_id'] = array('gt', 0);
            
            $list = M('order')->where($map)->join('o left join engineer e on e.id = o.engineer_id')
                    ->join('left join organization org on e.organization_id = org.id')
                    ->field('e.name, org.alias, count(*) as count')->group('e.id')->order('count desc')->select();
            
            foreach ($list as $item) {
                $exorders[] = array(
                    '工程师' => $item['name'],
                    '地区'  => $item['alias'],
                    '单量'  => $item['count'],
                );
            }
    
            $filename = '工程师完成单量('.date('Y-m-d', $startTime).'至'.date('Y-m-d', $endTime).')';
            $this->exportData($filename, $exorders);
        }
        
        $this->display();
    }

    /**
     * 统计工程师一段时间内的用料
     *
     * @return void
     */
    public function engineerFittings()
    {
        $address = array();
        $map = array();
        $data = array();
        $area = array();
        $startDec = $this->startDec;
        $post = I('post.');
        
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;

        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            $area[$city['city']] = 0;
        }
        
        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }
        
        $map['create_time'] = array('egt', $startDec);
        $map['clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['ei.fittings_id'] = array('gt', 0);
        $map['status'] = array('neq', -1);
        
        $list = M('order')->join('o left join engineer_inout ei on ei.order_id = o.id')
                ->join('left join fitting f on f.id = ei.fittings_id')
                ->where($map)
                ->field('o.city, ei.fittings_id, f.title, f.number, sum(ei.amount) as amount')
                ->group('o.city, ei.fittings_id')
                ->order('field(o.city, ' . implode(',', array_keys($address)) . '), ei.fittings_id asc')
                ->select();

        foreach ($list as $item) {
            
            if (!isset($data[$item['fittings_id']])) {
                $data[$item['fittings_id']] = array(
                    'fitting_title' => $item['title'] . '('.$item['number'].')',
                    'area' => $area,
                );
            }
            
            //全国
            if (isset($address['9999'])) {
                $data[$item['fittings_id']]['area']['9999'] += $item['amount'];
            }
            
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $data[$item['fittings_id']]['area'][$item['city']] += $item['amount'];
        }

        $this->assign('data', $data);
        $this->assign('address', $address);
        $this->assign('startTime', date('Y-m-d', $startTime));
        $this->assign('endTime',  date('Y-m-d', $endTime));
        $this->display();
    }
    
    /**
     * 统计工程师一段时间内的用料
     *
     * @return void
     */
    public function exportEngineerFittings()
    {
        $address = array();
        $map = array();
        $data = array();
        $startDec = $this->startDec;
        $post = I('post.');
        $exorders = array();
    
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
    
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            $exorders[$city['cityname']][] = array('地区', '物料名称', '物料编号', '消耗数量');
        }
    
        if (!isset($address['9999'])) {
            $map['o.city'] = array('in', array_keys($address));
        }
    
        $map['o.create_time'] = array('egt', $startDec);
        $map['o.clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['ei.fittings_id'] = array('gt', 0);
        $map['o.status'] = array('neq', -1);
    
        $list = M('order')->join('o left join engineer_inout ei on ei.order_id = o.id')
                ->join('left join fitting f on f.id = ei.fittings_id')
                ->where($map)
                ->field('o.city, ei.fittings_id, f.title, f.number, sum(ei.amount) as amount')
                ->group('o.city, ei.fittings_id')
                ->order('field(o.city, ' . implode(',', array_keys($address)) . '), ei.fittings_id asc')
                ->select();
    
        foreach ($list as $item) {
            
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $exorders[$address[$item['city']]][$item['fittings_id']] = array($address[$item['city']], $item['title'], $item['number'], strval($item['amount']));
        }
    
        $this->exportData('工程师用料物料情况('.date('Y-m-d', $startTime) . '至' . date('Y-m-d', $endTime) .')', $exorders, true);
    }
    
    /**
     * 工程师领料
     */
    public function engineerReceive()
    {
        $address = array();
        $map = array();
        $data = array();
        $area = array();
        $startDec = $this->startDec;
        $post = I('post.');
        
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
        
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            $area[$city['city']] = 0;
        }
        
        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }
        
        $map['ei.type'] = 2; //物料申请
        $map['ei.inout'] = 1;//入库
        $map['ei.time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['f.id'] = array('gt', 0);
        
        $list = M('engineer_inout')->join('ei left join fitting f on f.id = ei.fittings_id')
                ->join('left join engineer e on e.id = ei.engineer_id')
                ->join('left join organization org on org.id = e.organization_id')
                ->where($map)
                ->field('org.city, ei.fittings_id, f.title, f.number, sum(amount) as amount')
                ->group('org.city, ei.fittings_id')
                ->order('field(org.city, ' . implode(',', array_keys($address)) . '), ei.fittings_id asc')
                ->select();
        
        foreach ($list as $item) {
            
            if (!isset($data[$item['fittings_id']])) {
                $data[$item['fittings_id']] = array(
                    'fitting_title' => $item['title'] . '('.$item['number'].')',
                    'area' => $area,
                );
            }
            
            //全国
            if (isset($address['9999'])) {
                $data[$item['fittings_id']]['area']['9999'] += $item['amount'];
            }
        
            if (!isset($address[$item['city']])) {
                continue;
            }
        
            $data[$item['fittings_id']]['area'][$item['city']] += $item['amount'];
        }
        
        $map['ei.type'] = 2; //物料申请
        $map['ei.inout'] = 2;//出库
        $map['ei.time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['f.id'] = array('gt', 0);
        
        $list = M('engineer_inout')->join('ei left join fitting f on f.id = ei.fittings_id')
                ->join('left join engineer e on e.id = ei.engineer_id')
                ->join('left join organization org on org.id = e.organization_id')
                ->where($map)
                ->field('org.city, ei.fittings_id, sum(amount) as amount')
                ->group('org.city, ei.fittings_id')
                ->select();
        
        foreach ($list as $item) {
            
            //全国
            if (isset($address['9999'])) {
                $data[$item['fittings_id']]['area']['9999'] -= $item['amount'];
            }
            
            if (!isset($address[$item['city']]) || !isset($data[$item['fittings_id']]['area'][$item['city']])) {
                continue;
            }
        
            $data[$item['fittings_id']]['area'][$item['city']] -= $item['amount'];
        }
        
        $this->assign('data', $data);
        $this->assign('address', $address);
        $this->assign('startTime', date('Y-m-d', $startTime));
        $this->assign('endTime',  date('Y-m-d', $endTime));
        $this->display('engineerreceive');
    }
    
    /**
     * 导出指定时间工程师领料情况
     */
    public function exportEngineerReceiveFittings()
    {
        $address = array();
        $map = array();
        $data = array();
        $startDec = $this->startDec;
        $post = I('post.');
        
        $exorders = array();
        
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
        
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            $exorders[$city['cityname']][] = array('地区', '物料名称', '物料编号', '领取数量', '退还数量', '总数量');
        }
        
        if (!isset($address['9999'])) {
            $map['org.city'] = array('in', array_keys($address));
        }
        
        $map['ei.type'] = 2; //物料申请
        $map['ei.inout'] = 1;//入库
        $map['ei.time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['f.id'] = array('gt', 0);
        
        $list = M('engineer_inout')->join('ei left join fitting f on f.id = ei.fittings_id')
                ->join('left join engineer e on e.id = ei.engineer_id')
                ->join('left join organization org on org.id = e.organization_id')
                ->where($map)
                ->field('org.city, ei.fittings_id, f.title, f.number, sum(ei.amount) as amount')
                ->group('org.city, ei.fittings_id')
                ->order('field(org.city, ' . implode(',', array_keys($address)) . '), ei.fittings_id asc')
                ->select();
        
        foreach ($list as $item) {
            
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $exorders[$address[$item['city']]][$item['fittings_id']] = array($address[$item['city']], $item['title'], $item['number'], strval($item['amount']), '0', strval($item['amount']));
        }
        
        $map['ei.type'] = 2; //物料申请
        $map['ei.inout'] = 2;//入库
        $map['ei.time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['f.id'] = array('gt', 0);
        
        $list = M('engineer_inout')->join('ei left join fitting f on f.id = ei.fittings_id')
                ->join('left join engineer e on e.id = ei.engineer_id')
                ->join('left join organization org on org.id = e.organization_id')
                ->where($map)
                ->field('org.city, ei.fittings_id, sum(ei.amount) as amount')
                ->group('org.city, ei.fittings_id')
                ->select();
        
        foreach ($list as $item) {
        
            if (!isset($address[$item['city']]) || !isset($exorders[$address[$item['city']]][$item['fittings_id']])) {
                continue;
            }
        
            $exorders[$address[$item['city']]][$item['fittings_id']][4] = strval($item['amount']);
            $exorders[$address[$item['city']]][$item['fittings_id']][5] = strval($exorders[$address[$item['city']]][$item['fittings_id']][5] - $item['amount']);
        }
        
        $this->exportData('工程师领取物料情况('.date('Y-m-d', $startTime) . '至' . date('Y-m-d', $endTime) .')', $exorders, true);
    }

    /**
     * 进销存报表
     *
     * @return void
     */
    public function stock()
    {
        $address = array();
        $map = array();
        $startDec = $this->startDec;
        $data = array();
        $post = I('post.');
        
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
        
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            
            $data[$city['city']] = array(
                'name' => $city['cityname'], 
                'beginningBalance' => 0, 
                'currentStock' => 0,
                'currentSales' => 0,
                'endingBalance' => 0,
            );
        }
        
        $map['org.city'] = array('in', array_keys($address));
        
        //指定时间段的期初余额
        $map['i.time'] = array('lt', $startTime);
        $map['i.`inout`'] = 1;
        $map['i.type'] = 1;
        $map['i.amount'] = array('gt', 0);
        
        $list = M('organization')->join('org left join `inout` i on i.organization_id = org.id')
                ->field('org.city, sum(i.amount * i.price) as pirce')
                ->where($map)->group('org.city')->select();
        
        foreach ($list as $item) {
            
            if (!isset($address[$item['city']])) {
                continue;
            }
            
            $data[$item['city']]['beginningBalance'] += $item['pirce'];
        }
        
        //指定时间段的期末余额
        $map['i.time'] = array('elt', $endTime);
        $map['i.`inout`'] = 1;
        $map['i.type'] = 1;
        $map['i.amount'] = array('gt', 0);
        
        $list = M('organization')->join('org left join `inout` i on i.organization_id = org.id')
                ->field('org.city, sum(i.amount * i.price) as pirce')
                ->where($map)->group('org.city')->select();
        
        foreach ($list as $item) {
            
            if (!isset($address[$item['city']])) {
                continue;
            }
        
            $data[$item['city']]['endingBalance'] += $item['pirce'];
        }

        //指定时间段的进货和销货
        unset($map['i.inout']);
        $map['i.time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['i.type'] = 1;
        $map['i.amount'] = array('gt', 0);
        
        $list = M('organization')->join('org left join `inout` i on i.organization_id = org.id')
                ->field('org.city, i.inout, sum(i.amount * i.price) as pirce')
                ->where($map)->group('org.city, i.inout')->select();
        
        foreach ($list as $item) {
        
            if (!isset($address[$item['city']])) {
                continue;
            }
        
            if ($item['inout'] == 1) {
                $data[$item['city']]['currentStock'] += $item['pirce'];
            } else {
                $data[$item['city']]['currentSales'] += $item['pirce'];
            }
        }

        $this->assign('startTime', date('Y-m-d', $startTime));
        $this->assign('endTime', date('Y-m-d', $endTime));
        $this->assign('address', $address);
        $this->assign('data', $data);
        $this->display();
    }
    
    /**
     * 良品库存明细表
     *
     * @return void
     */
    public function fittingsDetail()
    {
        if (IS_POST) {
    
            $address = array();
            $map = array();
            $data = array();
    
            $post = I('post.');
            $time = empty($post['end_time']) ? strtotime(date('Y-m-d')) : strtotime($post['end_time']);
            $post_address = explode(',', $post['addresses']);
            $post_address && sort($post_address);
    
            $map['org.id'] = array('in', $post_address);
            $map['i.time'] = array('lt', $time);
            $map['i.`inout`'] = 1;
            $map['i.type'] = 1;
            $map['i.amount'] = array('gt', 0);
            
            //汇总导出
            if ($post_address == array_keys(session('organizations'))) {
    
                $exorders = array();
                $exorders[] = array(
                    '手机型号'  => '手机型号',
                    '物料编码'  => '物料编码',
                    '物料名称'  => '物料名称',
                    '库存数量'  => '库存数量',
                    '分配数量'  => '分配数量',
                    '可用数量'  => '可用数量',
                    '单价'  => '单价',
                    '总价'  => '总价',
                );
    
                $list = M('organization')->join('org left join `inout` i on i.organization_id = org.id')
                        ->join('left join fitting f on f.id = i.fitting_id')
                        ->join('left join phone_fitting pf on pf.fitting_id = f.id')
                        ->join('left join phone p on p.id = pf.phone_id')
                        ->field('p.alias, f.number, f.title, sum(i.amount) as amount, i.price')
                        ->where($map)->group('p.id, f.id')->select();
    
                foreach ($list as $item) {
                    $exorders[] = array(
                        '手机型号' => $item['alias'],
                        '物料编码' => $item['number'],
                        '物料名称' => $item['title'],
                        '库存数量' => $item['amount'],
                        '分配数量' => '',
                        '可用数量' => $item['amount'],
                        '单价' => $item['price'],
                        '总价' => $item['price'] > 0 ? $item['price'] * $item['amount'] : '0',
                    );
                }
                
                $filename = 'weadoc_purchase' . date('Y-m-d H:i:s');
                $this->exportData($filename, $exorders);
            } else { //分城市统计
    
                $exorders = array();
                $title = array(
                    '城市'  => '城市',
                    '手机型号'  => '手机型号',
                    '物料编码'  => '物料编码',
                    '物料名称'  => '物料名称',
                    '库存数量'  => '库存数量',
                    '分配数量'  => '分配数量',
                    '可用数量'  => '可用数量',
                    '单价'  => '单价',
                    '总价'  => '总价',
                );
    
                $list = M('organization')->join('org left join `inout` i on i.organization_id = org.id')
                        ->join('left join fitting f on f.id = i.fitting_id')
                        ->join('left join phone_fitting pf on pf.fitting_id = f.id')
                        ->join('left join phone p on p.id = pf.phone_id')
                        ->field('org.alias as org_name, p.alias, f.number, f.title, sum(i.amount) as amount, i.price')
                        ->where($map)->group('org.id, p.id, f.id')->order('org.id asc')->select();
    
                foreach ($list as $item) {
                    $exorders[$item['org_name']][0] = $title;
                    $exorders[$item['org_name']][] = array(
                        '城市'  => $item['org_name'],
                        '手机型号' => $item['alias'],
                        '物料编码' => $item['number'],
                        '物料名称' => $item['title'],
                        '库存数量' => $item['amount'],
                        '分配数量' => '',
                        '可用数量' => $item['amount'],
                        '单价' => $item['price'],
                        '总价' => $item['price'] > 0 ? $item['price'] * $item['amount'] : '0',
                    );
                }
                
                $filename = 'weadoc_purchase' . date('Y-m-d H:i:s');
                $this->exportData($filename, $exorders, 1);
            }
        }
    
        $this->display();
    }

    /**
     * 采购入库
     *
     * @return void
     */
    public function purchase()
    {
        
        if (IS_POST) {
            $exorders = array();
            $exorders[] = array(
                '城市代码' => '城市代码',
                '批次'    => '批次',
                '操作人'  => '操作人',
                '类型'   => '类型',
                '出入库'  => '出入库',
                '目标仓库' => '目标仓库',
                '手机型号' => '手机型号',
                '物料编码' => '物料编码',
                '物料名称' => '物料名称',
                '成本'    => '成本',
                '交易数量' => '交易数量',
                '供应商' => '供应商',
                '总金额' => '总金额',
            );
            
            $address = array();
            $map = array();
            //$map['i.`inout`'] = 1;
            $map['i.type'] = array('in', array(1, 2));
            $map['i.amount'] = array('gt', 0);
            
            $post = I('post.');
            
            if (!empty($post['start_time'])) {
                $map['pr.create_time'] = array('egt', strtotime($post['start_time']));
            }
            
            if (!empty($post['end_time'])) {
                $map['pr.create_time '] = array('elt', strtotime($post['end_time']) + 86399);
            }
            
            if (!empty($post['org'])) {
                $map['org.id'] = $post['org'];
            } else {
                $map['org.id'] = array('in', array_keys(session('organizations')));
            }
            
            if (!empty($post['type']) && $post['type'] != 'all') {
                $map['i.type'] = $post['type'];
            }
            
            if (!empty($post['inout']) && $post['inout'] != 'all') {
                $map['i.inout'] = $post['inout'];
            }
            
            if (!empty($post['user_id']) && $post['user_id'] != 'all') {
                $map['i.user_id '] = $post['user_id'];
            }
            
            if (!empty($post['batch'])) {
                $map['i.batch'] = trim($post['batch']);
            }

            $list = M('organization')->join('org left join `inout` i on i.organization_id = org.id')
                    ->join('left join organization org2 on i.target_orgid = org2.id')
                    ->join('left join provider p on p.id = i.provider_id')
                    ->join('left join purchase_receipt pr on p.id=pr.provider_id')
                    ->join('left join fitting f on f.id = i.fitting_id')
                    ->join('left join user on user.id = i.user_id')
                    ->where($map)
                    ->field('i.type, i.inout, i.fitting_id, org.alias as org_name, i.batch, f.title, f.number,
                            sum(i.amount) as amount, i.price, p.title as provider, user.username, org2.alias as org_name2')
                    ->group('org.city, i.batch, f.id')
                    ->select();

            $type = array(
                1 => '出入库',
                2 =>'调拨',
                3 => '工程师申请',
                4 => '报损'
            );
            $inout = array(1 => '入库', 2 => '出库');
            
            foreach ($list as $item) {
                $item['phones'] = M('phone_fitting')->join('pf left join phone pho on pho.id = pf.phone_id')
                                ->where(array('pf.fitting_id' => $item['fitting_id']))
                                ->field('group_concat(pho.alias) as phones')->group('pf.fitting_id')->find();
                
                $exorders[] = array(
                    '城市代码' => $item['org_name'],
                    '批次'    => $item['batch'],
                    '操作人'  => $item['username'],
                    '类型'  => $type[$item['type']],
                    '出入库' => $inout[$item['inout']],
                    '目标仓库' => $item['org_name2'],
                    '手机型号' => $item['phones']['phones'],
                    '物料编码' => $item['number'],
                    '物料名称' => $item['title'],
                    '成本'    => $item['price'],
                    '交易数量' => $item['amount'],
                    '供应商'  => $item['provider'],
                    '总金额'  => $item['price'] > 0 ? $item['amount'] * $item['price'] : '0',
                );
            }

            $filename = 'weadoc_purchase' . date('Y-m-d H:i:s');
            $this->exportData($filename, $exorders);
        }
        
        $this->display();
    }
    
    /**
     * 操作人
     */
    public function user()
    {
        $list = M('user')->field('id, username')->select();
        array_unshift($list,array('username'=>'全部','id'=>false));
        $this->ajaxReturn($list);
    }

    public function ordergrossbranch() {
        $address = array();
        $map = array();
        $data = array();
        $startDec = $this->startDec;
        $row = array(
            'revenue' => array('amount' => 0, 'price' => 0),
            'fittings' => array('price' => 0),
            'divided' => array('price' => 0),
            'waste' => array('price' => 0),
            'gross' => array('price' => 0),
            'rate' => 0,
        );

        $data[0] = array('name' => '总计', 'data' => $row);

        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            $data[$city['city']] = array('name' => $city['cityname'], 'data' => $row);
        }
        $data[1] = array('name' => '邮寄', 'data' => array('revenue' => array('amount' => 0, 'price' => 0)));

        $post = I('post.');
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;

        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }

        $map['clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['status'] = array('neq', -1);

        $list = M('order')->where($map)->field('city, category, count(*) as count, sum(actual_price) as price')->group('city, category')->select();

        foreach ($list as $item) {
            //邮寄
            if ($item['category'] == 2) {
                $data[1]['data']['revenue']['amount'] += $item['count'];
                $data[1]['data']['revenue']['price'] += $item['price'];
            }

            //全国
            if (isset($data['9999'])) {
                $data['9999']['data']['revenue']['amount'] += $item['count'];
                $data['9999']['data']['revenue']['price'] += $item['price'];
            }

            if (!isset($address[$item['city']])) {
                continue;
            }

            $data[0]['data']['revenue']['amount'] += $item['count'];
            $data[0]['data']['revenue']['price'] += $item['price'];
            $data[$item['city']]['data']['revenue']['amount'] += $item['count'];
            $data[$item['city']]['data']['revenue']['price'] += $item['price'];
        }

        //当日废料收入
        unset($map['clearing_time']);
        unset($map['status']);
        $map['wr.status'] = array('gt', -2);
        $map['time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $list = M('waste_refund')->join('wr left join organization org on wr.organization_id = org.id')
            ->where($map)->field('org.city, wr.wastes')->select();

        foreach ($list as $item) {
            $wastes = json_decode($item['wastes'], true);

            if (!$wastes) {
                continue;
            }

            $price = 0;
            $waste_id = array();
            $waste_amount = array();

            foreach ($wastes as $val) {
                $waste_amount[$val['waste_id']] = $val['amount'];
                $waste_id[] = $val['waste_id'];
            }

            $waste_prices = M('waste')->where(array('id' => array('in', $waste_id)))->getField('id, price');

            foreach ($waste_prices as $id => $waste_price) {
                $price += $waste_price * $waste_amount[$id];
            }

            //全国
            if (isset($data['9999'])) {
                $data['9999']['data']['waste']['price'] += $price;
            }

            if (!isset($address[$item['city']])) {
                continue;
            }

            $data[0]['data']['waste']['price'] += $price;
            $data[$item['city']]['data']['waste']['price'] += $price;
        }

        $this->assign('startTime', date('Y-m-d', $startTime));
        $this->assign('endTime', date('Y-m-d', $endTime));
        $this->assign('address', $address);
        $this->assign('data', $data);
        $this->display();
    }

    public function newOrderGrossDetailExport()
    {
        $this->orderGrossDetailExport();
    }

    public function newOrderGrossExport()
    {
        $address = array();
        $map = array();
        $startDec = $this->startDec;
        $exorders = array();
        $exorders[0] = array('', '当日营业收入', '', '当日废料收入');
        $exorders[1] = array('', '单量', '金额', '金额');
        $exorders[2] = array('总计', '0', '0', '0');

        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];

            $exorders[$city['city']] = array($city['cityname'], '0', '0', '0');
        }

        $post = I('post.');
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;

        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }

        $map['clearing_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['status'] = array('neq', -1);

        $list = M('order')->where($map)->field('city, count(*) as count, sum(actual_price) as price')->group('city')->select();

        foreach ($list as $item) {
            //全国
            if (isset($exorders['9999'])) {
                $exorders['9999'][1] += $item['count'];
                $exorders['9999'][2] += $item['price'];
            }

            if (!isset($address[$item['city']])) {
                continue;
            }

            $exorders[2][1] += $item['count'];
            $exorders[2][2] += $item['price'];
            $exorders[$item['city']][1] += $item['count'];
            $exorders[$item['city']][2] += $item['price'];
        }

        //当日配件成本
        $map['ei.type'] = 1;
        $list = M('order')->join('o left join engineer_inout ei on ei.order_id = o.id')
            ->join('left join fitting f on f.id = ei.fittings_id')
            ->where($map)->field('o.city, sum(f.price * ei.amount) as price')->group('o.city')->select();

        foreach ($list as $item) {
            //全国
            if (isset($exorders['9999'])) {
                $exorders['9999'][3] += $item['price'];
            }

            if (!isset($address[$item['city']])) {
                continue;
            }

            $exorders[2][3] += $item['price'];
            $exorders[$item['city']][3] += $item['price'];
        }

        //当日工程师预计收益
        unset($map['ei.type']);
        $list = M('order')->join('o left join engineer_divide ed on ed.order_id = o.id')
            ->where($map)->field('o.city, sum(ed.earning) as price')->group('o.city')->select();

        foreach ($list as $item) {
            //全国
            if (isset($exorders['9999'])) {
                $exorders['9999'][4] += $item['price'];
            }

            if (!isset($address[$item['city']])) {
                continue;
            }

            $exorders[2][4] += $item['price'];
            $exorders[$item['city']][4] += $item['price'];
        }

        //当日废料收入
        unset($map['clearing_time']);
        unset($map['status']);
        $map['wr.status'] = array('gt', -2);
        $map['time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $list = M('waste_refund')->join('wr left join organization org on wr.organization_id = org.id')
            ->where($map)->field('org.city, wr.wastes')->select();

        foreach ($list as $item) {
            $wastes = json_decode($item['wastes'], true);

            if (!$wastes) {
                continue;
            }

            $price = 0;
            $waste_id = array();
            $waste_amount = array();

            foreach ($wastes as $val) {
                $waste_amount[$val['waste_id']] = $val['amount'];
                $waste_id[] = $val['waste_id'];
            }

            $waste_prices = M('waste')->where(array('id' => array('in', $waste_id)))->getField('id, price');

            foreach ($waste_prices as $id => $waste_price) {
                $price += $waste_price * $waste_amount[$id];
            }

            //全国
            if (isset($address['9999'])) {
                $exorders['9999'][5] += $price;
            }

            if (!isset($address[$item['city']])) {
                continue;
            }

            $exorders[2][5] += $price;
            $exorders[$item['city']][5] += $price;
        }

        foreach ($exorders as $k => &$v) {

            if ($k <= 1) continue;

            $v[1] = $v[1] ? $v[1] : '0';
            $v[2] = $v[2] ? $v[2] : '0';
            unset($v[3]);
            unset($v[4]);
            $v[5] = $v[5] ? $v[5] : '0';
        }

        $filename = '收款统计日报表(汇总)-('.date('Y-m-d', $startTime) . '至' . date('Y-m-d', $endTime) .')';
        $this->exportData($filename, $exorders);
    }
    
    /**
     * 保险订单统计
     *
     * @return void
     */
    public function insuranceOrder()
    {
        $address = array();
        $count = array();
        $list = array();
        $day = I('get.day/d');
        $map = array();
        $data = array();
    
        $address['9999'] = '全国';
    
        $addresses = M('organization')->join('o left join address adr on o.city = adr.id')->where(array('type' => 1))->field('o.city, adr.name')->select();
    
        foreach ($addresses as $city) {
            $address[$city['city']] = $city['name'];
        }
    
        $address['0'] = '其他';
    
        if (!isset($address['9999'])) {
            $map['o.city'] = array('in', array_keys($address));
        }
    
        if (IS_POST) {
    
            if (!empty(I('post.startTime'))) {
                $startTime = strtotime(I('post.startTime'));
            } else {
                $startTime = strtotime('today');
            }
    
            if (!empty(I('post.endTime'))) {
                $endTime = strtotime(I('post.endTime'));
            } else {
                $endTime = time();
            }
    
            $map['pio.create_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else if ($day == 2) { /** 昨日 */
            $map['pio.create_time'] = array(array('egt', strtotime('yesterday')), array('lt', strtotime('today')), 'AND');
        } else { /** 今日 */
            $map['pio.create_time'] = array('egt', strtotime('today'));
        }
    
        //下单量
        $order_count = M('phomal_insurance_order')->join('pio left join `order` o on o.id = pio.old_order_id')
                        ->where($map)->field('IF(o.city > 0, o.city, 0) as city, count(*) as tp_count')
                        ->group('o.city')->select();
    
        foreach ($order_count as $item) {
            $list[$item['city']] = $item['tp_count'];
        }
        unset($order_count);
    
        foreach ($address as $key => $value) {
            $tp_count = $list[$key];
    
            if ($key == 9999) {
                $tp_count = array_sum($list);
            }
            $count[$key] = (int)$tp_count;
        }
    
        $data[0]['name'] = '下单量';
        $data[0]['data'] = array_values($count);
        $orders = $count;
    
        //出险订单量
        $map = array();
        
        if (IS_POST) {
    
            if (!empty(I('post.startTime'))) {
                $startTime = strtotime(I('post.startTime'));
            } else {
                $startTime = strtotime('today');
            }
    
            if (!empty(I('post.endTime'))) {
                $endTime = strtotime(I('post.endTime'));
            } else {
                $endTime = time();
            }
    
            $map['pio.broken_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else if ($day == 2) { /** 昨日 */
            $map['pio.broken_time'] = array(array('egt', strtotime('yesterday')), array('lt', strtotime('today')), 'AND');
        } else { /** 今日 */
            $map['pio.broken_time'] = array('egt', strtotime('today'));
        }
    
        $order_count = M('phomal_insurance_order')->join('pio left join `order` o on o.id = pio.old_order_id')
                        ->where($map)->field('IF(o.city > 0, o.city, 0) as city, count(*) as tp_count')
                        ->group('o.city')->select();
    
        $list = array();
    
        foreach ($order_count as $item) {
            $list[$item['city']] = $item['tp_count'];
        }
        unset($order_count);
    
        foreach ($address as $key => $value) {
            $tp_count = $list[$key];
    
            if ($key == 9999) {
                $tp_count = array_sum($list);
            }
            $count[$key] = (int)$tp_count;
        }
    
        $data[1]['name'] = '出险量';
        $data[1]['data'] = array_values($count);
    
        //今日订单取消量
        $map = array('pio.status' => -1);
    
        if (IS_POST) {
    
            if (!empty(I('post.startTime'))) {
                $startTime = strtotime(I('post.startTime'));
            } else {
                $startTime = strtotime('today');
            }
    
            if (!empty(I('post.endTime'))) {
                $endTime = strtotime(I('post.endTime'));
            } else {
                $endTime = time();
            }
    
            $map['pio.create_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else if ($day == 2) { /** 昨日 */
            $map['pio.create_time'] = array(array('egt', strtotime('yesterday')), array('lt', strtotime('today')), 'AND');
        } else { /** 今日 */
            $map['pio.create_time'] = array('egt', strtotime('today'));
        }
    
        $order_count = M('phomal_insurance_order')->join('pio left join `order` o on o.id = pio.old_order_id')
                        ->where($map)->field('IF(o.city > 0, o.city, 0) as city, count(*) as tp_count')
                        ->group('o.city')->select();
        $list = array();
    
        foreach ($order_count as $item) {
            $list[$item['city']] = $item['tp_count'];
        }
        unset($order_count);
    
        foreach ($address as $key => $value) {
            $tp_count = $list[$key];
    
            if ($key == 9999) {
                $tp_count = array_sum($list);
            }
            $count[$key] = (int)$tp_count;
        }
    
        $data[2]['name'] = '取消量';
        $data[2]['data'] = array_values($count);
    
        //取消率
        $cancelcount = array();
    
        foreach ($count as $k => $val) {
    
            if (isset($orders[$k]) && $orders[$k] > 0) {
                $cancelcount[$k] = round($val/$orders[$k]*100);
            } else {
                $cancelcount[$k] = 0;
            }
        }
        unset($orders);
    
        $data[3]['name'] = '取消率(%)';
        $data[3]['data'] = array_values($cancelcount);
    
        $this->assign('count', $data);
        $this->assign('address', array_values($address));
        $this->display();
    }
    
    /**
     * 保险出险订单统计
     *
     * @return void
     */
    public function insuranceBroken()
    {
        $address = array();
        $count = array();
        $list = array();
        $day = I('get.day/d');
        $data = array();
    
        $address['9999'] = '全国';
    
        $addresses = M('organization')->join('o left join address adr on o.city = adr.id')->where(array('type' => 1))->field('o.city, adr.name')->select();
    
        foreach ($addresses as $city) {
            $address[$city['city']] = $city['name'];
        }
    
        $address['0'] = '其他';
    
        if (!isset($address['9999'])) {
            $map['o.city'] = array('in', array_keys($address));
        }
    
        //出险订单量
        $map = array();
    
        if (IS_POST) {
    
            if (!empty(I('post.startTime'))) {
                $startTime = strtotime(I('post.startTime'));
            } else {
                $startTime = strtotime('today');
            }
    
            if (!empty(I('post.endTime'))) {
                $endTime = strtotime(I('post.endTime'));
            } else {
                $endTime = time();
            }
    
            $map['pio.broken_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else if ($day == 2) { /** 昨日 */
            $map['pio.broken_time'] = array(array('egt', strtotime('yesterday')), array('lt', strtotime('today')), 'AND');
        } else { /** 今日 */
            $map['pio.broken_time'] = array('egt', strtotime('today'));
        }
    
        $order_count = M('phomal_insurance_order')->join('pio left join `order` o on o.id = pio.old_order_id')
                        ->where($map)->field('IF(o.city > 0, o.city, 0) as city, count(*) as tp_count')
                        ->group('o.city')->select();
    
        $list = array();
    
        foreach ($order_count as $item) {
            $list[$item['city']] = $item['tp_count'];
        }
        unset($order_count);
    
        foreach ($address as $key => $value) {
            $tp_count = $list[$key];
    
            if ($key == 9999) {
                $tp_count = array_sum($list);
            }
            $count[$key] = (int)$tp_count;
        }
    
        $data[0]['name'] = '出险量';
        $data[0]['data'] = array_values($count);
    
        $this->assign('count', $data);
        $this->assign('address', array_values($address));
        $this->display();
    }
    
    /**
     * 收款统计日报表
     *
     * @return void
     */
    public function insuranceCash()
    {
        $address = array();
        $map = array();
        $data = array();
        $startDec = $this->startDec;
    
        $row = array(
            'revenue' => array('amount' => 0, 'price' => 0),
            'noPay' => array('amount' => 0, 'price' => ''),
        );
    
        $data[0] = array('name' => '总计', 'data' => $row);
    
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            $data[$city['city']] = array('name' => $city['cityname'], 'data' => $row);
        }
    
        $post = I('post.');
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
    
        if (!isset($address['9999'])) {
            $map['city'] = array('in', array_keys($address));
        }
    
        $map['pio.pay_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['pio.create_time'] = array('egt', $startDec);
        $map['pio.status'] = array('gt', -1);
    
        $list = M('phomal_insurance_order')->join('pio left join `order` o on o.id = pio.old_order_id')
                ->where($map)->field('o.city, count(*) as count, sum(price) as price, pio.status')
                ->group('o.city')->select();
    
        foreach ($list as $item) {
            
            //全国
            if (isset($data['9999'])) {
                $data['9999']['data']['revenue']['amount'] += $item['count'];
                $data['9999']['data']['revenue']['price'] += $item['price'];
                
                if ($item['status'] == 0) {//未付款
                    $data['9999']['data']['noPay']['amount'] += $item['count'];
                    $data['9999']['data']['noPay']['price'] += $item['price'];
                }
            }
    
            if (!isset($address[$item['city']])) {
                continue;
            }
    
            $data[0]['data']['revenue']['amount'] += $item['count'];
            $data[0]['data']['revenue']['price'] += $item['price'];
    
            $data[$item['city']]['data']['revenue']['amount'] += $item['count'];
            $data[$item['city']]['data']['revenue']['price'] += $item['price'];
            
            if ($item['status'] == 0) {//未付款
                $data[$item['city']]['data']['noPay']['amount'] += $item['count'];
                $data[$item['city']]['data']['noPay']['price'] += $item['price'];
            }
        }
    
        $this->assign('startTime', date('Y-m-d', $startTime));
        $this->assign('endTime', date('Y-m-d', $endTime));
        $this->assign('data', $data);
        $this->assign('address', $address);
        $this->display();
    }
    
    /**
     * 保险毛利统计日报表
     *
     * @return void
     */
    public function insuranceGross()
    {
        $address = array();
        $map = array();
        $data = array();
        $startDec = $this->startDec;
        $row = array(
            'revenue' => array('amount' => 0, 'price' => 0),
            'fittings' => array('price' => 0),
            'divided' => array('price' => 0),
            'waste' => array('price' => 0),
            'gross' => array('price' => 0),
            'rate' => 0,
        );
    
        $data[0] = array('name' => '总计', 'data' => $row);
    
        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
            $data[$city['city']] = array('name' => $city['cityname'], 'data' => $row);
        }
    
        $post = I('post.');
        $startTime = empty($post['start_time']) ? strtotime('today') : strtotime($post['start_time']);
        $endTime = empty($post['end_time']) ? $startTime + 86399 : strtotime($post['end_time'] . ' 23:59.59.999');
        $startTime = $startTime < $startDec ? $startDec : $startTime;
        $endTime = $endTime < $startTime ? $startTime + 86399 : $endTime;
    
        if (!isset($address['9999'])) {
            $map['o.city'] = array('in', array_keys($address));
        }
    
        $map['pio.create_time'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        $map['pio.status'] = array('gt', 0);
        
        $order_ids = array();
    
        $list = M('phomal_insurance_order')->join('pio left join `order` o on o.id = pio.old_order_id')
                ->where($map)
                ->field('o.city, count(*) as count, sum(price) as price, group_concat(pio.id) as insurance_id, group_concat(order_id) as order_id')
                ->group('o.city')->select();
        
        foreach ($list as $item) {
            //全国
            if (isset($data['9999'])) {
                $data['9999']['data']['revenue']['amount'] += $item['count'];
                $data['9999']['data']['revenue']['price'] += $item['price'];
            }
            
            $order_ids[$item['city']]['order_id'] = $item['order_id'];
            $order_ids[$item['city']]['insurance_id'] = $item['insurance_id'];
    
            if (!isset($address[$item['city']])) {
                continue;
            }
    
            $data[0]['data']['revenue']['amount'] += $item['count'];
            $data[0]['data']['revenue']['price'] += $item['price'];
            $data[$item['city']]['data']['revenue']['amount'] += $item['count'];
            $data[$item['city']]['data']['revenue']['price'] += $item['price'];
        }
        
        foreach ($order_ids as $city => $item) {
            
            $order_id = array_filter(explode(',', $item['order_id']));
            $insurance_id = array_filter(explode(',', $item['insurance_id']));
            
            if (!$order_id || !$insurance_id) {
                continue;
            }
            
            //当日配件成本
            $map = array();
            $map['order_id'] = array('in', $order_id);
            $price = M('stock')->where($map)->sum('price');
            
            //全国
            if (isset($data['9999'])) {
                $data['9999']['data']['fittings']['price'] += $price;
            }
            
            $data[0]['data']['fittings']['price'] += $price;
            $data[$city]['data']['fittings']['price'] += $price;
            
            //当日工程师预计收益
            //维修分成
            $map = array();
            $map['order_id'] = array('in', $order_id);
            $price = M('engineer_divide')->where($map)->sum('earning');
            //保险分成
            $map = array();
            $map['insurance_order_id'] = array('in', $insurance_id);
            $price += round(M('engineer_insurance_divide')->where($map)->sum('earning'), 2);
            
            //全国
            if (isset($data['9999'])) {
                $data['9999']['data']['divided']['price'] += $price;
            }
            
            $data[0]['data']['divided']['price'] += $price;
            $data[$city]['data']['divided']['price'] += $price;
            
            //当日废料收入
            $map = array();
            $map['status'] = array('gt', -2);
            $map['order_id'] = array('in', $order_id);
            $list = M('waste_refund')->where($map)->field('wastes')->select();
            
            foreach ($list as $item) {
                $wastes = json_decode($item['wastes'], true);
            
                if (!$wastes) {
                    continue;
                }
            
                $price = 0;
                $waste_id = array();
                $waste_amount = array();
            
                foreach ($wastes as $val) {
                    $waste_amount[$val['waste_id']] = $val['amount'];
                    $waste_id[] = $val['waste_id'];
                }
            
                $waste_prices = M('waste')->where(array('id' => array('in', $waste_id)))->getField('id, price');
            
                foreach ($waste_prices as $id => $waste_price) {
                    $price += $waste_price * $waste_amount[$id];
                }
            
                //全国
                if (isset($data['9999'])) {
                    $data['9999']['data']['waste']['price'] += $price;
                }
            
                $data[0]['data']['waste']['price'] += $price;
                $data[$city]['data']['waste']['price'] += $price;
            }
        }
    
        $this->assign('startTime', date('Y-m-d', $startTime));
        $this->assign('endTime', date('Y-m-d', $endTime));
        $this->assign('address', $address);
        $this->assign('data', $data);
        $this->display();
    }

    //废料统计
    public function waste()
    {
        $address = array();
        $data = array();
        $post = I('post.');
        $rst = array();
        $map = array();

        foreach (session('addresses') as $city) {
            $address[$city['city']] = $city['cityname'];
        }

        $map['title'] = array('like', '%屏幕(显示正常)%');

        $waste = M('waste')
            ->join('left join `phone_waste` on waste.id = phone_waste.waste_id')
            ->join('left join `phone` on phone.id = phone_waste.phone_id')
            ->where($map)->getField('waste.id, number, title, price, alias');

        $map = array();

        if (!empty($post['start_time']) && empty($post['end_time'])) {
            $map['time'] = array('egt', strtotime($post['start_time']));
        }

        if (!empty($post['end_time']) && empty($post['start_time'])) {
            $map['time '] = array('elt', strtotime($post['end_time'])+24*60*60-1);
        }

        if ($post['start_time'] && $post['end_time']) {
            $map['time '] = array(array('gt',strtotime($post['start_time'])),array('lt',strtotime($post['end_time']) +24*60*60-1),'and');
        }

        if (empty($post['start_time']) && empty($post['end_time'])) {
            $map['time '] = array(array('gt',strtotime(date('Y-m-d'))),array('lt',time()),'and');
        }

        $waste_refund = M('waste_refund')->join('left join `organization` on organization.id = waste_refund.organization_id')->where($map)->select();

        $org_id = array();

        foreach ($waste_refund as $key => $value) {
            $values = current(json_decode($value['wastes'] ,true));

            $waste[$values['waste_id']]['data'][$value['alias']]['amount'] += $values['amount'];
            $waste[$values['waste_id']]['data'][$value['alias']]['number_price'] += $values['amount'] * $waste[$values['waste_id']]['price'];
        }

        foreach ($waste as $k => &$v) {

            if (empty($v['data']) || empty($v['id'])) {
                unset($waste[$k]);
            } else {

                foreach ($address as &$h) {
                    $h = str_replace('市','', $h);

                    foreach ($v['data'] as $z => &$j) {

                        if ($h != $z && empty($v['data'][$h])) {
                            $v['data'][$h]['amount'] = 0;
                            $v['data'][$h]['number_price'] = 0;
                        }
                    }
                }
            }
        }

        foreach ($waste as $kl => &$vl) {

            foreach ($address as $ars) {

                foreach ($vl['data'] as $i => $item) {

                    if ($i == $ars) {
                        $data[$kl][$ars] = $item;
                    }
                }

            }
        }

        $this->assign('data', $data);
        $this->assign('rst', $waste);
        $this->assign('address', $address);
        $this->display();
    }
    
    /**
     * 物料堆积
     */
    public function fittingStore()
    {
        $this->display();
    }
    
    public function fittingStoreRows()
    {
        $post = I('post.');
        $map = array('s.organization_id' => array('gt', 0), 's.status' => 1);
        
        if (!empty($post['start_time']) && empty($post['end_time'])) {
            $map['s.create_time'] = array('egt', strtotime($post['start_time']));
        } else if (!empty($post['end_time']) && empty($post['start_time'])) {
            $map['s.create_time'] = array('elt', strtotime($post['end_time'])+24*60*60-1);
        } else if ($post['start_time'] && $post['end_time']) {
            $map['s.create_time'] = array(array('gt',strtotime($post['start_time'])),array('lt',strtotime($post['end_time']) +24*60*60-1),'and');
        } else if (empty($post['start_time']) && empty($post['end_time'])) {
            $map['s.create_time'] = array(array('gt',strtotime(date('Y-m-d'))),array('lt',time()),'and');
        }
        
        if (!empty($post['organization_id'])) {
            $map['s.organization_id'] = $post['organization_id'];
        }
        
        if (!empty($post['fitting_id'])) {
            $map['s.fitting_id'] = $post['fitting_id'];
        }
        
        if (!empty($post['phone_id'])) {
            $map['pf.phone_id'] = $post['phone_id'];
        }
        
        if (!empty($post['keyword'])) {
            $where = array();
            $where['f.number'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['f.title'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['p.alias'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['pr.batch'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        
        $join = "s left join fitting f on f.id = s.fitting_id
                left join organization o on o.id = s.organization_id
                left join phone_fitting pf on f.id = pf.fitting_id
                left join phone p on p.id = pf.phone_id";
        
        $list['total'] = M('stock')->join($join)->where($map)->count('distinct(s.id)');
        $list['rows'] = array();
        
        if ($list['total'] > 0) {
            $list['rows'] = M('stock')->join($join)->where($map)
                            ->field("s.id, s.number, s.create_time, concat(f.title, '(', f.number , ')') as fitting, 
                                    group_concat(distinct(p.alias)) as phone, o.alias as org_name")
                            ->group('s.id')
                            ->order('s.organization_id asc')
                            ->limit($this->page())
                            ->select();
        }
        
        $this->ajaxReturn($list);
    }
    
    /**
     * 导出物料堆积
     */
    public function fittingStoreExport()
    {
        $post = I('post.');
        $map = array('s.organization_id' => array('gt', 0), 's.status' => 1);
        
        if (!empty($post['start_time']) && empty($post['end_time'])) {
            $map['s.create_time'] = array('egt', strtotime($post['start_time']));
        } else if (!empty($post['end_time']) && empty($post['start_time'])) {
            $map['s.create_time'] = array('elt', strtotime($post['end_time'])+24*60*60-1);
        } else if ($post['start_time'] && $post['end_time']) {
            $map['s.create_time'] = array(array('gt',strtotime($post['start_time'])),array('lt',strtotime($post['end_time']) +24*60*60-1),'and');
        } else if (empty($post['start_time']) && empty($post['end_time'])) {
            $map['s.create_time'] = array(array('gt',strtotime(date('Y-m-d'))),array('lt',time()),'and');
        }
        
        if (!empty($post['organization_id'])) {
            $map['s.organization_id'] = $post['organization_id'];
        }
        
        if (!empty($post['fitting_id'])) {
            $map['s.fitting_id'] = $post['fitting_id'];
        }
        
        if (!empty($post['phone_id'])) {
            $map['pf.phone_id'] = $post['phone_id'];
        }
        
        if (!empty($post['keyword'])) {
            $where = array();
            $where['f.number'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['f.title'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['p.alias'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['pr.batch'] = array('like', '%' . trim($post['keyword']) . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        
        $join = "s left join fitting f on f.id = s.fitting_id
                left join organization o on o.id = s.organization_id
                left join phone_fitting pf on f.id = pf.fitting_id
                left join phone p on p.id = pf.phone_id";
        
        $list = M('stock')->join($join)->where($map)
                        ->field("s.id, s.number, s.create_time, f.title,  f.number as f_number,
                                group_concat(p.alias) as phone, o.alias as org_name")
                        ->group('s.id')
                        ->order('s.organization_id asc')
                        ->select();
        
        $exorders = array();
        $exorders[] = array('城市', '机型', '物料编码', '物料名称', '库存编号', '入库时间');
        
        foreach ($list as $item) {
            $exorders[] = array(
                $item['org_name'],
                implode(',', array_unique(explode(',', $item['phone']))),
                $item['f_number'],
                $item['title'],
                $item['number'],
                date('Y-m-d H:i:s', $item['create_time'])
            );
        }
        
        $filename = '各城市物料堆积情况-' . date('Y_m_d_H_i_s');
        $this->exportData($filename, $exorders);
    }
    
    /**
     * 机型
     *
     * @return void
     */
    public function phones()
    {
        $map = array();
        $list = M('phone')->where($map)->field('id, alias')->order('alias asc')->select();
        array_unshift($list,array('alias'=>'全部','id'=>''));
        $this->ajaxReturn($list);
    }
    
    /**
     * 配件
     *
     * @return void
     */
    public function fittings()
    {
        $phoneId = I('get.id/d', 0);
        $sql = "select f.id, concat(f.title, '(', f.number, ')') as name from phone_fitting pf
                left join fitting f on pf.fitting_id=f.id
                where pf.phone_id={$phoneId} and f.id > 0";
        $fittings = M()->query($sql);
        array_unshift($fittings,array('name'=>'全部','id'=>''));
        $this->ajaxReturn($fittings);
    }
    
    /**
     * 仓库（组织）
     *
     * @return void
     */
    public function organization()
    {
        $list = M('organization')->field('id, alias, city')->select();
        array_unshift($list,array('alias'=>'全部', 'id'=>'', 'city' => ''));
        $this->ajaxReturn($list);
    }

    /**
     * 所有未结单订单预计消耗物料
     */
    public function orderExceptConsume()
    {
        $post = I('post.');
        $map = array();
        $query = array();
        $map['o.status'] = array('in', '1, 2, 3, 4');
        
        if (!empty($post['city'])) {
            $map['o.city'] = $post['city'];
        }
        
        if (!empty($post['phone_id'])) {
            $map['o.phone_id'] = $post['phone_id'];
        }
        
        $organization = M('organization')->where(array('type' => 1))->order('city asc')->getField('city, alias');
        
        if ($organization) {
            $map['o.city '] = array('in', array_keys($organization));
        }
        
        $order = M('order')->join('o left join order_phomal op on op.order_id = o.id')
                ->join('phone_malfunction pm on pm.id = op.phomal_id')
                ->field('o.city, o.color_id, pm.is_color, pm.fitting')
                ->where($map)
                ->order('o.city asc')
                ->select();
        
        $list = array();
        $fitting_ids = array();
        $fittings = array();
        $address = array();
        
        foreach ($order as $item) {
            
            //故障区分颜色但订单缺少颜色
            if (!$item['color_id'] && $item['is_color']) {
                continue;
            }
            
            $fitting = json_decode($item['fitting'], true);
            
            if ($item['is_color']) {
                $fitting = current($fitting[$item['color_id']]['items']);
                
            } else {
                $fitting = current($fitting);
            }
            
            if (!$fitting['id']) {
                continue;
            }
            
            $fitting_ids[$fitting['id']] = $fitting['id'];
            $list[$fitting['id']][$item['city']] += $fitting['amount'];
            
            $address[$item['city']] = $organization[$item['city']];
        }
        
        if ($fitting_ids) {
            $fittings = M('fitting')->join('f left join phone_fitting pf on pf.fitting_id = f.id')
                        ->join('left join phone p on p.id = pf.phone_id')
                        ->where(array('f.id' => array('in', $fitting_ids)))
                        ->group('f.id')
                        ->getField("f.id, concat(f.title, '(', f.number, ')') as fitting, group_concat(p.alias) as phone");
        }
        
        
        $this->assign('data', $list);
        $this->assign('address', $address);
        $this->assign('fittings', $fittings);
        $this->assign('query', $post);
        $this->display();
    }

    /**
     * 入库页面
     */
    public function orderStorage()
    {
        $this->display();
    }

    /**
     * 入库统计表
     */
    public function orderStorageRow()
    {
        $post = I('post.');
        $map['purchase_receipt.status'] = 2;

        if ($post['organization_id']) {
            $map['pr.organization_id'] = array('in', $post['organization_id']);
        }

        if ($post['provider']) {
            $map['provider.id'] = $post['provider'];
        }

        if (!empty($post['clearing_time_start']) && empty($post['clearing_time_end'])) {
            $map['update_time'] = array('egt', strtotime($post['clearing_time_start']));
        }

        if (!empty($post['clearing_time_end']) && empty($post['clearing_time_start'])) {
            $map['update_time '] = array('elt', strtotime($post['clearing_time_end'])+24*60*60-1);
        }

        if ($post['clearing_time_start'] && $post['clearing_time_end']) {
            $map['update_time '] = array(array('gt',strtotime($post['clearing_time_start'])),array('lt',strtotime($post['clearing_time_end']) +24*60*60-1),'and');
        }

        $join = 'left join `purchase_receipt_fitting` pr on pr.purchase_receipt_id=purchase_receipt.id
                 left join `fitting` f on f.id=pr.fitting_id
                 left join `organization` o on o.id=pr.organization_id
                 left join `phone_fitting` pf on pf.fitting_id=f.id
                 left join `phone` p on p.id=pf.phone_id
                 left join `provider` on provider.id=purchase_receipt.provider_id
                  ';
        $field = 'provider.title, p.alias, f.number, f.title as fitting_name, pr.amount,pr.price as price, o.name';

        $rst['total'] = M('purchase_receipt')->join($join)->where($map)->count();
        $rst['rows'] = M('purchase_receipt')->join($join)->where($map)->limit($this->page())->field($field)->select();

        foreach ($rst['rows'] as &$value) {
            $value['total'] = $value['amount'] * $value['price'];
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 预付统计页面
     */
    public function advance()
    {
        $this->display();
    }

    /**
     * 预付统计数据
     */
    public function advanceRow()
    {
        $rst = array();
        $moder = M('order');
        $post = I('post.');

        $join = 'left join `organization` on `order`.city =  organization.city';

        if (empty($post['clearing_time_start']) && empty($post['clearing_time_end'])) {
            $rst['success'] = false;
            $rst['errorMsg'] = '开始时间和结束时间必填';
        }

        if ($post['organization_id']) {
            $where['shangqiyushou']['organization.id'] = array('in', $post['organization_id']);
            $where['shangqiruku']['organization.id'] = array('in', $post['organization_id']);
            $where['benqiyushou']['organization.id'] = array('in', $post['organization_id']);
            $where['benqiruku']['organization.id'] = array('in', $post['organization_id']);
            $where['ruku']['organization.id'] = array('in', $post['organization_id']);
            $where['yufu']['organization.id'] = array('in', $post['organization_id']);
        }

        /**上期结余**/
        $where['shangqiyushou']['pay_type'] = 2;
        $where['shangqiyushou']['create_time'] = array('lt', strtotime($post['clearing_time_start']));

        $where['shangqiruku']['pay_type'] = 2;
        $where['shangqiruku']['`order`.status'] = 6;
        $where['shangqiruku']['paid_time'] = array('lt', strtotime($post['clearing_time_start']));

        $rst['rows'][0]['shangqijieyue'] =  $moder->join($join)->where($where['shangqiyushou'])->sum('paid_amount') - $moder->join($join)->where($where['shangqiruku'])->sum('paid_amount') ;

        /**本期预付收款**/
        $where['benqiyushou']['pay_type'] = 2;
        $where['benqiyushou']['create_time'] = array(array('gt', strtotime($post['clearing_time_start'])),array('lt',strtotime($post['clearing_time_end']) +24*60*60-1),'and');

        $rst['rows'][0]['benqiyushou'] = $moder->join($join)->where($where['benqiyushou'])->sum('paid_amount');

        /**本期入库**/
        $where['benqiruku']['pay_type'] = 2;
        $where['benqiruku']['paid_time'] = array(array('gt',strtotime($post['clearing_time_start'])),array('lt',strtotime($post['clearing_time_end']) +24*60*60-1),'and');
        $where['benqiruku']['`order`.status'] = 6;

        $rst['rows'][0]['benqiruku'] = $moder->join($join)->where($where['benqiruku'])->sum('paid_amount');

        /**本期结余**/
//        $where['zongyufu']['pay_type'] = 2;
//        $where['zongyufu']['create_time'] = array(array('gt', strtotime($post['clearing_time_start'])),array('lt',strtotime($post['clearing_time_end']) +24*60*60-1),'and');
//
//        $where['zongruku']['paid_time'] = array(array('gt', strtotime($post['clearing_time_start'])),array('lt',strtotime($post['clearing_time_end']) +24*60*60-1),'and');
//        $where['zongruku']['pay_type'] = 2;
//        $where['zongruku']['`order`.status'] = 6;

        //$rst['rows'][0]['benqijieyu'] = $moder->join($join)->where($where['zongruku'])->sum('paid_amount') - $moder->join($join)->where($where['zongruku'])->sum('paid_amount');
        $rst['rows'][0]['benqijieyu'] = $rst['rows'][0]['benqiyushou'] - $rst['rows'][0]['benqiruku'];
        $rst['total'] = 4;
        $this->ajaxReturn($rst);
    }

    /**
     * 预付统计导出
     */
    public function advanceExport()
    {
        $param = I('post.');
        $columns = array(
            '订单编号'  => 'number',
            '客户名'   => 'customer',
            '手机号码'  => 'cellphone',
            '手机型号'  => 'phone_name',
            '状态'     => 'status',
            '工程师'    => 'engineer_name',
            '下单时间'    => 'create_time',
            '结单时间'    => 'end_time',
            '付款类型'    => 'pay_type',
            '预计价格'    => 'reference_price',
            '实际价格'    => 'actual_price',
            '付款方式'    => 'payment_method',
            '付款时间'    => 'paid_time',
            '结算时间'  => 'clearing_time',
            '是否结算'    => 'is_clearing',
            '已付金额'    => 'paid_amount',
            '第三方订单号' => 'third_party_number',
            '买家账号'    => 'buyer_email',
            '备注'      => 'remark',
            '工程师备注'  => 'engineer_remark',
            '用户备注'   => 'user_remark',
            '付款'  => 'fukuan',
            '取消时间' => 'close_time',
            '地区'    => 'city',
        );

        set_time_limit(0);

        $exorders = array();
        $exorders[] = array_keys($columns);

        if ($param['clearing_time_start'] && $param['clearing_time_end']) {
            $map['o.create_time '] = array(array('egt',strtotime($param['clearing_time_start'])), array('elt',strtotime($param['clearing_time_end']) +24*60*60-1),'and');

            if ($param['organization_id']) {
                $map['e.organization_id'] = array('in', $param['organization_id']);
            }

        } else {
            return false;
        }

        $list = M('order')->join('o left join customer c on c.id = o.customer_id')
            ->join('left join phone p on p.id = o.phone_id')
            ->join('left join engineer e on o.engineer_id = e.id')
            ->where($map)->field('o.*,p.alias as phone_name, c.email as cemail, c.address as caddress, e.name as engineer_name')
            ->order('id desc')->select();

        $category = C('ORDER_CATEGORY');
        $payment = C('ORDER_PAYMENT');
        $status  = C('ORDER_STATUS');

        foreach ($list as &$order) {

            $fittings = array();
            $wastes = array();
            $order['malfunction'] = array();

            $malfunction_list = M('order_phomal')->join('op left join phone_malfunction pm on op.phomal_id = pm.id')
                ->field('pm.malfunction, pm.fitting, pm.waste, pm.is_color')
                ->where(array('op.order_id' => $order['id']))->select();

            foreach ($malfunction_list as $malfunction) {

                $order['malfunction'][] = $malfunction['malfunction'];

                if (empty($malfunction['is_color'])) {

                    $malfunction_fittings = json_decode($malfunction['fitting'], true);

                    foreach ($malfunction_fittings as $fitting){
                        $fittings[] = $fitting['name'];
                    }
                } else { //有颜色，读取订单的color_id去fitting里面去取相应的颜色值
                    $mal_list = json_decode($malfunction['fitting'], true);

                    $malfunction_fittings = $mal_list[$order['color_id']]['items'];

                    foreach ($malfunction_fittings as $fitting){
                        $fittings[] = $fitting['name'];
                    }
                }

                $malfunction_wastes = json_decode($malfunction['waste'], true);

                foreach ($malfunction_wastes as $waste){
                    $wastes[] = $waste['name'];
                }
            }

            $order['malfunction'] = implode(',', $order['malfunction']);
            $order['status'] = $status[$order['status']];
            $order['category'] = $category[$order['category']];
            $order['isinvoice'] = $order['is_invoice'] ? '是' : '否';
            $type = array(1 => '新单', 2 => '返修', 5 => '保险', 3 => '活动', 4 => '第三方');
            $order['type'] = $type[$order['type']];
            $order['time'] = gmstrftime('%H:%M:%S', ($order['end_time'] - $order['maintain_start_time']));
            $order['payment_method'] = $payment[$order['payment_method']];
            $order['third_party_number'] = ' '. $order['third_party_number'];
            $order['create_time'] = date('Y-m-d H:i:s', $order['create_time']);
            $order['end_time'] = date('Y-m-d H:i:s', $order['end_time']);
            $order['paid_time'] = $order['paid_time'] ? date('Y-m-d H:i:s', $order['paid_time']) : '';
            $order['clearing_time'] = $order['clearing_time'] ? date('Y-m-d H:i:s', $order['clearing_time']) : '';
            $order['clone_time'] = $order['clone_time'] ? date('Y-m-d H:i:s', $order['clone_time']) : '';
            $order['is_clearing'] = $order['is_clearing'] ? '是' : '否';
            $order['malfunction_description'] = str_replace( array('='), '', $order['malfunction_description']);
            $order['fittings'] = implode(',', $fittings);
            $order['wastes'] = implode(',', $wastes);
            $order['fukuan'] = $order['clearing_time'] != 0 ? '是' : '否';
            $order['pay_type'] = $order['pay_type'] == 2 ? '预付' : '修付';
            $order['close_time'] = $order['close_time']? date('Y-m-d H:i:s', $order['close_time']) : '';
            $order['city'] = M('address')->find($order['city'])['name'];
            $row = array();

            foreach ($columns as $v) {
                $row[] = $order[$v];
            }

            $exorders[] = $row;
        }

        unset($list);
        unset($order);
        unset($row);

        $this->exportData('weadoc_order_' . date('Y_m_d_H_i_s'), $exorders);
    }

    /**
     * 进销存管理
     */
    public function invoicing()
    {
        $tmp = array();
        $fields = array('qichukucun', 'benqiruku', 'benqichuku', 'benqihuishou', 'benqisunyi', 'benqidiaobo' );
        $post = I('post.');

        $time_strat = strtotime($post['start_time']);
        $time_end = strtotime($post['end_time'])+ 24*60*60-1;
        $map = array('organization_id' => array('type' => 1));

        if (empty($time_strat) || empty($time_end)) {

            $this->display();
            return false;
        }

        if (!empty($post['organization_id'])  && empty($post['fitting_id'])) {
            $map['organization_id'] = array('id' => $post['organization_id'], 'type' => 1);
        }

        if (empty($post['organization_id'])  && !empty($post['fitting_id'])) {
            $map['fitting_id'] = array('f.id' => $post['fitting_id']);
        }

        if ($post['organization_id'] && $post['fitting_id']) {
            $map['fitting_id'] = array('f.id' => $post['fitting_id']);
            $map['organization_id'] = array('id' => $post['organization_id'], 'type' => 1);
        }

        if ($post['provider']) {
            $provider = 'AND `provider_id`= '.$post['provider'];
        }

        /**城市公司**/
        $organization = M('organization')->where($map['organization_id'])->field('id, name')->select();

        /**配件统计**/
        $join = 'f left join `phone_fitting` pf on pf.fitting_id=f.id
                left join `phone` p on pf.phone_id=p.id';

        if ($post['fitting_id']) {
            $fittings['rows'] = M('fitting')->join($join)->where($map['fitting_id'])->field('number, f.id, title, group_concat(p.alias) as alias')->select();
        } else {
            $fittings['rows'] = M('fitting')->join($join)->where($map['fitting_id'])->field('number, f.id, title, alias')->select();
        }



        /**本期入库**/
        $sql = "SELECT  prf.fitting_id, prf.price as price,sum(prf.amount) as amount, prf.organization_id, number FROM `purchase_receipt_fitting` prf
                left join fitting f on f.id = prf.fitting_id
                left join purchase_receipt pr on pr.id=prf.purchase_receipt_id
                WHERE ( `receipt_time` > $time_strat AND `receipt_time` < $time_end +24*60*60-1 $provider)
                GROUP  by prf.id, organization_id
                ";
        $tmp['benqiruku'] = M()->query($sql);

        /**本期出库**/

        $sql = "SELECT organization.id as organization_id, ei.fittings_id as fitting_id, f.price, sum(ei.amount) AS amount FROM `order` o
                LEFT JOIN engineer_inout ei ON ei.order_id = o.id
                LEFT JOIN stock on o.id=stock.order_id
                LEFT JOIN fitting f ON f.id = ei.fittings_id
                LEFT JOIN organization ON o.city = organization.city
                WHERE o.`create_time` >= $this->startDec AND ( `clearing_time` >= $time_strat AND `clearing_time` <= $time_end ) AND ei.fittings_id > 0 AND o.`status` <> - 1 AND organization.type = 1  AND stock.fitting_id=ei.fittings_id $provider
                GROUP BY organization.id, ei.fittings_id, stock.fitting_id
                ";
        $tmp['benqichuku'] = M()->query($sql);

        /**本期回收**/
        $sql = "SELECT organization.id as organization_id, ei.fittings_id as fitting_id, f.price, sum(ei.amount) AS amount FROM `order` o
                LEFT JOIN engineer_inout ei ON ei.order_id = o.id
                LEFT JOIN stock on o.id=stock.order_id
                LEFT JOIN fitting f ON f.id = ei.fittings_id
                LEFT JOIN organization ON o.city = organization.city
                WHERE o.`create_time` >= $this->startDec AND ( `clearing_time` >= $time_strat AND `clearing_time` <= $time_end ) AND ei.fittings_id > 0 AND o.type=2 AND o.`status` <> - 1 AND stock.fitting_id=ei.fittings_id $provider
                GROUP BY organization.id, ei.fittings_id ,stock.fitting_id
                ";

        $tmp['benqihuishou'] = M()->query($sql);

        /**本期损益**/
        $benqisunyi = array();
        $sql = "select price, count(fitting_id) as amount, fitting_id, organization_id from `inout`
                where time < $time_end and time > $time_strat and type=1 $provider
                group by fitting_id, organization_id
                ";
        $tmp['benqisunyi'] = M()->query($sql);

        /**本期调拨**/
        $benqidiaobo = array();
        $sql = "select sum(price) as price, count(fitting_id) as amount, fitting_id, organization_id from `inout`
                where time < $time_end and time > $time_strat and type=2 $provider
                group by fitting_id, organization_id
                ";
        $tmp['benqidiaobo'] = M()->query($sql);

        foreach ($organization as $o => $org) {

            foreach ($fittings['rows'] as $key => &$value) {
                $value['name'] = $value['alias'].$value['title'];

                foreach ($fields as $i => $item) {

                    foreach ($tmp[$item] as $k => $v) {

                        if ($org['id'] == $v['organization_id'] && $value['id'] == $v['fitting_id']) {
                            $rst['rows'][$o][$key][$item.'_amount'] = $v['amount'];
                            $rst['rows'][$o][$key][$item.'_totalPrice'] = $v['amount'] * $v['price'];
                            $rst['rows'][$o][$key]['organization'] = $org['name'];
                            $rst['rows'][$o][$key]['name'] = $value['name'];
                            //$rst['rows'][$o][$key]['id'] = $v['fitting_id'];

                        }
                    }
                }
            }
        }

        /**期末结余**/
        foreach ($organization as $org) {

            foreach ($rst['rows'] as &$v) {

                foreach ($v as &$value) {
                    $value['qimojiecun_amount'] =  $value['chuqikucun_amount'] + $value['benqiruku_amount'] - $value['benqituihuo_amount'] - $value['benqichuku_amount'] + $value['benqisunyi_amount'] + $value['benqidiaobo_amount'];
                    $value['qimojiecun_totalPrice'] =  $value['chuqikucun_totalPrice'] + $value['benqiruku_totalPrice'] - $value['benqituihuo_totalPrice'] - $value['benqichuku_totalPrice'] + $value['benqisunyi_totalPrice'] + $value['benqidiaobo_totalPrice'];
                }
            }
        }

        $this->assign('rst', $rst);
        $this->display();
    }

    /**
     *  供应商
     * */
    public function provider()
    {
        $list = M('provider')->field('title, id')->select();
        array_unshift($list,array('title'=>'全部', 'id'=>''));
        $this->ajaxReturn($list);
    }



}