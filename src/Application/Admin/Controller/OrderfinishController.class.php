<?php

namespace Admin\Controller;

use Admin\Model\OrderStatisticsModel;
use Admin\Model\OrderFinishModel;
use Admin\Model\OrderRepairModel;
use Admin\Model\OrderHourFinish;
use Admin\Model\OrderDiscount;

class OrderfinishController extends BaseController
{
    public function index()
    {
        $dateQuantumArray = OrderStatisticsModel::getStatisticsUnitTypeArray();
        $this->assign('dateQuantum', $dateQuantumArray);

        $selectType = OrderFactory::getSelectType();
        $this->assign('selectType', $selectType);

        $organizationList = OrderStatisticsModel::getOrganizationArray();

        $this->assign('organizationList', $organizationList);

        $this->display();
    }

    public function search()
    {
        $res = array(
            'code' => -1,
            'msg' => '非法访问'
        );

//        if (!IS_POST) {
//            $this->ajaxReturn($res);
//        }

        $params = I('request.');
        $type = intval($params['type']);
        $beginDate = trim($params['beginDateValue']);
        $endDate = trim($params['endDateValue']);
        $statisticsUnitType = trim($params['dateQuantumValue']);
        $hour = trim($params['hourValue']);
        $discount = trim($params['discountValue']);

        $order = OrderFactory::createOrder($type, $beginDate, $endDate, $statisticsUnitType, $hour, $discount);
        $res = $order->getResult();

        $this->ajaxReturn($res);
    }
}

class OrderFactory
{
    /**
     * 统计类型
     * @var array
     */
    static private $selectType = array(
        array('value' => 0, 'text' => '完成率'),
        array('value' => 1, 'text' => '返修率'),
        array('value' => 2, 'text' => '小时完成率'),
        array('value' => 3, 'text' => '折扣率'),
    );

    public static function getSelectType()
    {
        return self::$selectType;
    }

    static public function createOrder($type, $beginDate, $endDate, $statisticsUnitType, $hour, $discount)
    {
        switch ($type) {
            case 0:
                return new OrderFinishModel($beginDate, $endDate, $statisticsUnitType);
                break;

            case 1:
                return new OrderRepairModel($beginDate, $endDate, $statisticsUnitType);
                break;

            case 2:
                return new OrderHourFinish($beginDate, $endDate, $statisticsUnitType, $hour);
                break;

            case 3:
                return new OrderDiscount($beginDate, $endDate, $statisticsUnitType, $discount);
                break;

            default:
                # code...
                break;
        }
    }
}