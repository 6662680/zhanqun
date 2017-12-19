<?php

namespace Admin\Model;

abstract class OrderStatisticsModel
{
    function __construct($beginDate, $endDate, $statisticsUnitType)
    {
        $this->setBeginDate($beginDate);
        $this->setEndDate($endDate);
        $this->setStatisticsUnitType($statisticsUnitType);
    }

    // 统计开始日期
    protected $beginDate;

    /**
     * 得到统计的开始日期
     *
     * @return mixed
     */
    protected function getBeginDate()
    {
        return $this->beginDate;
    }

    /**
     * 设置统计开始日期
     *
     * @param $date 开始日期
     * @return void
     */
    protected function setBeginDate($date)
    {
        $this->beginDate = trim($date);
    }

    // 统计结束日期
    protected $endDate;

    /**
     * 得到统计的结束日期
     *
     * @return mixed
     */
    protected function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * 设置统计结束日期
     *
     * @param $date 结束日期
     * @return void
     */
    protected function setEndDate($date)
    {
        $this->endDate = trim($date);
    }

    /**
     * 根据用户的组织权限得到组织列表
     *
     * @return array 组织列表
     */
    static public function getOrganizationArray()
    {
        $organizationList = M('Organization')
            ->field(array('id', '`alias` as `name`', 'city'))
            ->where(array('status' => 1, 'type' => 1))
            ->select();

        $res = array();
        foreach ($organizationList as $value) {
            foreach ($_SESSION['addresses'] as $address) {
                if ($value['city'] == $address['city']) {
                    $res[] = $value;
                    break;
                }
            }
        }

        return $res;
    }

    // 用户可统计数据的城市
    protected $organizationList;

    /**
     * 得到用户可查看的组织列表
     *
     * @return array
     */
    protected function getOrganizationList()
    {

        if (!$this->organizationList) {
            $this->organizationList = self::getOrganizationArray();
        }

        return $this->organizationList;
    }

    // 工程师列表
    protected $engineerList;

    /**
     * 根据用户的组织权限得到工程师列表
     *
     * @return mixed|void
     */
    protected function getEngineerList()
    {

        if (!$this->engineerList) {

            $organization = $this->getOrganizationList();

            if (count($organization) < 1) {
                $this->setErrorInfo('您没有可以查看的组织');
                return ;
            }

            $city_ids = array();
            foreach ($organization as $value) {
                $city_ids[] = $value['id'];
            }
            $map['organization_id'] = (count($city_ids) == 1) ? $city_ids : array('IN', $city_ids);

            $model = M('Engineer');
            $engineerList = $model
                ->field(array('id', 'name', 'organization_id'))
                ->where($map)
                ->select();

            if (count($engineerList) < 1) {
                $this->setErrorInfo('没有工程师');

                return;
            }

            $this->engineerList = $engineerList;
        }

        return $this->engineerList;
    }

    /**
     * @var array 不在总单量范围内的取消原因
     */
    static private $notCountReasones = array('重复订单', '测试订单');

    /**
     * 不统计的
     *
     * @return array
     */
    protected static function getNotCountReasones()
    {
        return self::$notCountReasones;
    }

    /**
     * @var order表中要select 的字段名数组
     */
    protected $selectFields;

    /**
     * 得到 order表中要select 的字段名数组
     *
     * @return mixed
     */
    abstract protected function getSelectFields();

    // 订单列表
    protected $orderList;

    /**
     * 根据用户的组织权限得到订单列表
     *
     * @return mixed|void
     */
    protected function getOrderList()
    {

        if (!$this->orderList) {
            $beginTime = strtotime($this->getBeginDate());
            $endTime = strtotime($this->getEndDate()) + 86400;

            if ($beginTime < 0 || $endTime < 0 || $beginTime >= $endTime) {
                $this->setErrorInfo('开始时间与结束时间出错');
                return;
            }

            // 组织
            $organizationList = $this->getOrganizationList();

            if (count($organizationList) < 1) {
                $this->setErrorInfo('您没有可以查看的组织');
                return;
            }

            $map['create_time'] = array(
                array('EGT', $beginTime),
                array('LT', $endTime)
            );

            // 这些原因取消的订单不计入总单量
            $notCount = self::getNotCountReasones();
            $map['close_reason'] = array('NOT IN', $notCount);

            // 订单列表
            $cities = array();
            foreach ($organizationList as $value) {
                $cities[] = $value['city'];
            }

            $fields = array('id', 'engineer_id', 'city', 'address', 'create_time');

            $addFields = $this->getSelectFields();

            if ($addFields) {
                $fields = array_unique(array_merge($fields, $addFields));
            }

            $orderModel = M('Order');
            $orderList = $orderModel->field($fields)->where($map)->select();

            $this->orderList = $orderList;
        }

        return $this->orderList;
    }

    /**
     * 根据x轴数组信息、组织与组织中的工程师得到订单信息
     *
     * @param $organizationOrders 属于组织的订单
     * @param $engineer 工程师（保证是组织内的工程师）
     * @return array|void
     */
    protected function orderNumberBy($organizationOrders, $engineer)
    {

        // x 轴
        $xAxis = $this->getXAxisArray();

        if (count($xAxis) < 1) {
            $this->setErrorInfo('x轴出错');
            return;
        }

        $res = array();
        $tempX = array();

        foreach ($xAxis as $x) {
            $tempX['name'] = $x;
            $fitXAxisNumber = 0;
            $allXAxisNumber = 0;

            foreach($organizationOrders as $order) {

                if ($order['engineer_id'] == $engineer['id']) {
                    $this->fitNumberEngineerOrder($order, $x, $fitXAxisNumber, $allXAxisNumber);
                }
            }

            $tempX['fitOrderNumber'] = $fitXAxisNumber;
            $tempX['allfitOrderNumber'] = $allXAxisNumber;
            $res[] = $tempX;
        }

        return $res;
    }

    /**
     * 根据工程师订单与x轴得到工程师符合条件的订单数与所有的订单数
     *
     * @param $engineerOrder 工程师订单
     * @param $xAxis x轴
     * @param $fitNumber 符合条件的订单数
     * @param $allFitNumber 所有的订单数
     * @return mixed
     */
    abstract protected function fitNumberEngineerOrder($engineerOrder, $xAxis, &$fitNumber, &$allFitNumber);

    // 数据内容
    protected $data;

    /**
     * 得到返回的 data 字段
     *
     * @return array|void
     */
    protected function getData()
    {

        if (!$this->data) {

            $organizationList = $this->getOrganizationList();

            if (count($organizationList) < 1) {
                $this->setErrorInfo('您没有可以查看的组织');
                return;
            }

            // 工程师
            $engineerList = $this->getEngineerList();

            $orderList = $this->getOrderList();

            if (count($engineerList) < 1) {
                $this->setErrorInfo('没有工程师');
                return;
            }

            // 组织过滤
            foreach ($organizationList as &$ogz) {
                $tempEngineerList = array();

                // 根据组织分组工程师
                foreach ($engineerList as $eng) {
                    if ($ogz['id'] == $eng['organization_id']) {
                        $tempEngineerList[] = $eng;
                    }
                }
                $tempEngineerList[] = array('id' => '0', 'name' => '未派单', 'organization_id' => $ogz['id']);

                // 根据组织分组订单
                $tempOgzOrder = array();
                foreach ($orderList as $order) {
                    if ($order['city'] == $ogz['city']) {
                        $tempOgzOrder[] = $order;
                    }
                }

                foreach ($tempEngineerList as &$tempEng) {
                    $tempEng['xAxis'] = $this->orderNumberBy($tempOgzOrder, $tempEng);
                }
                unset($tempEng);
                $ogz['engineerList'] = $tempEngineerList;
            }
            unset($ogz);

            $this->data = $organizationList;
        }

        return $this->data;
    }

    /**
     * @var 统计单元(static private $statisticsUnitTypeArray value 中的一个)
     */
    protected $statisticsUnitType;

    /**
     * 设置统计单元
     *
     * @param $type
     * @return void
     */
    protected function setStatisticsUnitType($type)
    {
        $this->statisticsUnitType = $type;
    }

    /**
     * 得到统计单元
     *
     * @return mixed
     */
    protected function getStatisticsUnitType()
    {
        return $this->statisticsUnitType;
    }

    /**
     * @var array 所有的统计单元
     */
    static private $statisticsUnitTypeArray = array(
        array('value' => 'day', 'text' => '每日', 'checked' => true),
        array('value' => 'week', 'text' => '每周', 'checked' => false),
        array('value' => 'month', 'text' => '每月', 'checked' => false)
    );

    public static function getStatisticsUnitTypeArray()
    {
        return self::$statisticsUnitTypeArray;
    }

    /**
     * @var 根据统计开始日期、结束日期与统计单元得到 X 轴数组
     */
    protected $xAxisArray;

    protected function getXAxisArray()
    {
        if (!$this->xAxisArray) {
            $beginTime = strtotime($this->getBeginDate());
            $endTime = strtotime($this->getEndDate());
            $statisticsUnitType = $this->getStatisticsUnitType();

            $tempArray = array();
            switch ($statisticsUnitType) {
                case 'day':
                    while($beginTime < $endTime) {
                        $tempArray[] = date("Y-m-d", $beginTime);
                        $beginTime += 86400;
                    }
                    $tempArray[] = date("Y-m-d", $endTime);

                    break;

                case 'week':
                    while ($beginTime <= $endTime) {
                        $tempArray[] = date("Y年第W周", $beginTime);
                        $beginTime = strtotime("+7 day", $beginTime);
                    }
                    break;

                case 'month':

                    while ($beginTime <= $endTime) {
                        $tempArray[] = date("Y-m", $beginTime);
                        $beginTime = strtotime("first day of next month", $beginTime);
                    }
                    break;

                default:
                    break;
            }

            $this->xAxisArray = $tempArray;
        }

        return $this->xAxisArray;
    }

    /**
     * 设置错误信息
     *
     * @param $msg 错误信息
     * @return void
     */
    protected function setErrorInfo($msg)
    {
        $this->setCode(-1);
        $this->setMsg($msg);
    }

    /**
     * @var 返回的代码
     */
    protected $code;

    protected function getCode()
    {

        if (!$this->code) {
            $this->setCode(0);
        }

        return $this->code;
    }

    protected function setCode($code)
    {
        $this->code = intval(trim($code));
    }
    /**
     * @var 返回结果提示信息
     */
    protected $msg;

    protected function getMsg()
    {

        if (!$this->msg) {
            $this->msg = '成功';
        }

        return $this->msg;
    }

    protected function setMsg($msg)
    {
        $this->msg = trim($msg);
    }

    /**
     * @var 图表的标题
     */
    protected $titleText;

    abstract protected function getTitleText();

    /**
     * @var 返回结果
     */
    protected $result;

    public function getResult()
    {

        if (!$this->result) {

            $code = $this->getCode();
            $msg = $this->getMsg();

            $result = array(
                'code' => $code,
                'msg' => $msg,
                'title' => $this->getTitleText(),
                'xAxis' => $this->getXAxisArray(),
                'data' => $this->getData()
            );

            $this->result = $result;
        }

        return $this->result;
    }
}