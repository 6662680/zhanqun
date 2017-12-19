<?php

namespace Admin\Model;

class OrderRepairModel extends OrderStatisticsModel
{
    protected function getTitleText()
    {
        if (!$this->titleText) {
            $this->titleText = '返修率(%)';
        }

        return $this->titleText;
    }

    protected function getSelectFields()
    {

        if (!$this->selectFields) {
            $this->selectFields = array(
                'type'
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

            if ($engineerOrder['type'] == 2) {
                $fitNumber++;
            }
        }
    }
}