<?php

namespace Admin\Model;

class OrderHourFinish extends OrderStatisticsModel
{
    // 小时
    protected $hour;
    protected function getHour()
    {
        return $this->hour;
    }

    protected function setHour($hour)
    {
        $this->hour = floatval($hour * 1.0);
    }

    function __construct($beginDate, $endDate, $statisticsUnitType, $hour)
    {
        parent::__construct($beginDate, $endDate, $statisticsUnitType);
        $this->setHour($hour);
    }

    protected function getTitleText()
    {
        if (!$this->titleText) {
            $this->titleText = $this->getHour() .'小时完成率(%)';
        }

        return $this->titleText;
    }

    protected function getSelectFields()
    {

        if (!$this->selectFields) {
            $this->selectFields = array(
                'maintain_end_time' //结束维修时间
            );
        }

        return $this->selectFields;
    }

    protected function fitNumberEngineerOrder($engineerOrder, $xAxis, &$fitNumber, &$allFitNumber)
    {
        $createTime = intval($engineerOrder['create_time']);
        $finishTime = intval($engineerOrder['maintain_end_time']);
        switch ($this->getStatisticsUnitType()) {
            case 'day':
                $createDate = $createTime > 0 ? date("Y-m-d", $createTime) : 0;
                break;
            case 'week':
                $createDate = $createTime > 0 ? date("Y年第W周", $createTime) : 0;
                break;
            case 'month':
                $createDate = $createTime > 0 ? date("Y-m", $createTime) : 0;
                break;
            default:
                break;
        }

        if ($createDate == $xAxis) {
            $allFitNumber++;

            $hour = $this->getHour();
            if (($finishTime > $createTime) && (($finishTime - $createTime) < $hour * 3600)) {
                $fitNumber++;
            }
        }
    }
}