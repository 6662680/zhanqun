<?php

namespace Admin\Model;

class OrderFinishModel extends OrderStatisticsModel
{
    protected function getTitleText()
    {
        if (!$this->titleText) {
            $this->titleText = '完成率(%)';
        }

        return $this->titleText;
    }

    protected function getSelectFields()
    {

        if (!$this->selectFields) {
            $this->selectFields = array(
                'clearing_time'
            );
        }

        return $this->selectFields;
    }

    protected function fitNumberEngineerOrder($engineerOrder, $xAxis, &$fitNumber, &$allFitNumber)
    {
        $clearTime = intval($engineerOrder['clearing_time']);
        $createTime = intval($engineerOrder['create_time']);

        switch ($this->getStatisticsUnitType()) {
            case 'day':
                $clearDate = $clearTime > 0 ? date("Y-m-d", $clearTime) : 0;
                $createDate = $createTime > 0 ? date("Y-m-d", $createTime) : 0;
                break;
            case 'week':
                $clearDate = $clearTime > 0 ? date("Y年第W周", $clearTime) : 0;
                $createDate = $createTime > 0 ? date("Y年第W周", $createTime) : 0;
                break;
            case 'month':
                $clearDate = $clearTime > 0 ? date("Y-m", $clearTime) : 0;
                $createDate = $createTime > 0 ? date("Y-m", $createTime) : 0;
                break;
            default:
                break;
        }

        if ($clearDate == $xAxis) {
            $fitNumber++;
        }

        if ($createDate == $xAxis) {
            $allFitNumber++;
        }
    }
}