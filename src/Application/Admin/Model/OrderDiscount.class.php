<?php

namespace Admin\Model;

class OrderDiscount extends OrderStatisticsModel
{
    // 折扣
    protected $discount;
    protected function getDiscount()
    {
        return $this->discount;
    }

    protected function setDiscount($discount)
    {
        $this->discount = floatval($discount * 1.0);
    }

    function __construct($beginDate, $endDate, $statisticsUnitType, $discount)
    {
        parent::__construct($beginDate, $endDate, $statisticsUnitType);
        $this->setDiscount($discount);
    }

    protected function getTitleText()
    {
        if (!$this->titleText) {
            $this->titleText = ($this->getDiscount() * 10) . '折内率(%)';
        }

        return $this->titleText;
    }

    protected function getSelectFields()
    {

        if (!$this->selectFields) {
            $this->selectFields = array(
                'reference_price', // 预计价格
                'actual_price' // 实际价格
            );
        }

        return $this->selectFields;
    }

    protected function fitNumberEngineerOrder($engineerOrder, $xAxis, &$fitNumber, &$allFitNumber)
    {
        $createTime = intval($engineerOrder['create_time']);
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

            $discount = $this->getDiscount();
            $reference_price = floatval($engineerOrder['reference_price'] * 1.0);
            $actual_price = floatval($engineerOrder['actual_price'] * 1.0);
            if ($reference_price && $actual_price && (($actual_price / $reference_price) < $discount)) {
                $fitNumber++;
            }
        }
    }
}