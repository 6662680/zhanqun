<?php

namespace Admin\Model;

use Think\Model;

class MessageEngineerModel extends Model
{
    /**
     * 消息类型数组
     * @var array
     */
    private static $status = array('0' => '未推送', '1' => '未读', '2' => '已读', '3' => '已确认');

    /**
     * 得到所有的状态
     *
     * @return array
     */
    static public function getStatuses()
    {
        return self::$status;
    }

    /**
     * 根据状态 id 得到状态
     *
     * @param $statusId 状态 id
     * @return mixed
     */
    static public function getStatusBy($statusId)
    {
        $statuses = self::getStatuses();
        return $statuses[$statusId];
    }

    /**
     * 消息详情列表
     *
     * @param $id 消息 ID
     * @param $page 分布
     * @return mixed
     */
    public function getListBy($id, $page)
    {
        $rst['total'] = 0;
        $rst['rows'] = array();
        if ($id > 0) {

            $map['`me`.`message_id`'] = $id;

            $total = $this->join('AS `me` LEFT JOIN `engineer` AS `e` ON `e`.`id` = `me`.`engineer_id`')
                ->where($map)
                ->count();

            $list = $this->join('AS `me` LEFT JOIN `engineer` AS `e` ON `e`.`id` = `me`.`engineer_id`')
                ->field('`e`.`id`, `e`.`name`, `me`.`status`, `me`.`push_time`, `me`.`read_time`, `me`.`check_time`')
                ->where($map)
                ->limit($page)
                ->select();

            foreach ($list as &$value) {
                $value['status'] = self::getStatusBy($value['status']);
                $value['push_time'] = intval($value['push_time']) > 0 ? date("Y-m-d H:i:s", $value['push_time']) : '';
                $value['read_time'] = intval($value['read_time']) > 0 ? date("Y-m-d H:i:s", $value['read_time']) : '';
                $value['check_time'] = intval($value['check_time']) > 0 ? date("Y-m-d H:i:s", $value['check_time']) : '';
            }

            $rst['total'] = $total;
            $rst['rows'] = $list;
        }

        return $rst;
    }

    public function getListByEngineerId($id, $pageNum, $pageTotal = 10)
    {
        $result = array();

        if ($id > 0) {
            $map['`e`.`id`'] = $id;
            $map['`me`.`status`'] = array('GT', 0);
            $result = $this->join('AS `me` LEFT JOIN `engineer` AS `e` ON `e`.`id` = `me`.`engineer_id`')
                ->join('LEFT JOIN `message_push` AS `m` ON `m`.`id` = `me`.`message_id`')
                ->field('`m`.`id` AS `message_id`, `m`.`title`, `m`.`content`, `e`.`id` AS `engineer_id`, `e`.`name`, `me`.`status`, `me`.`push_time`, `me`.`read_time`, `me`.`check_time`')
                ->where($map)
                ->limit($pageTotal*$pageNum, $pageTotal)
                ->order('`m`.`id` DESC')
                ->select();

            foreach ($result as &$value) {
                $value['status_msg'] = self::getStatusBy($value['status']);
                $value['push_time'] = intval($value['push_time']) > 0 ? date("Y-m-d H:i:s", $value['push_time']) : '';
                $value['read_time'] = intval($value['read_time']) > 0 ? date("Y-m-d H:i:s", $value['read_time']) : '';
                $value['check_time'] = intval($value['check_time']) > 0 ? date("Y-m-d H:i:s", $value['check_time']) : '';
            }
        }
        return $result;
    }

    /**
     * 设置消息已读
     *
     * @param $engineerId 工程师id
     * @param $messageId 消息id
     * @return bool
     */
    public function setMessageRead($engineerId, $messageId)
    {
        $map = array(
            'engineer_id' => $engineerId,
            'message_id' => $messageId
        );

        $info = $this->where($map)->find();
        $status = 2;

        if ($info['status'] < $status) {
            $data = array(
                'status' => $status,
                'read_time' => time()
            );

            return $this->where($map)->data($data)->save();
        }

        return true;
    }

    /**
     * 设置消息已确认
     *
     * @param $engineerId 工程师id
     * @param $messageId 消息id
     * @return bool
     */
    public function setMessageCheck($engineerId, $messageId)
    {
        $map = array(
            'engineer_id' => $engineerId,
            'message_id' => $messageId
        );

        $info = $this->where($map)->find();
        $status = 3;

        if ($info['status'] < $status) {
            $data = array(
                'status' => $status,
                'check_time' => time()
            );

            return $this->where($map)->data($data)->save();
        }

        return true;
    }

    /**
     * @var array 要推送的消息相关信息
     */
    private $messageInfo = array();

    /**
     * 根据消息ID推送消息
     *
     * @param $messageId 消息ID
     * @return array();
     */
    public function pushMessageBy($messageId)
    {
        $this->messageInfoBy($messageId);

        return $this->pushMessage();
    }

    /**
     * 根据消息ID更新要推送的消息信息
     *
     * @param $messageId 消息ID
     * @return void
     */
    private function messageInfoBy($messageId)
    {
        $map['`m`.`id`'] = $messageId; // 消息 id
        $map['`e`.`status`'] = 1; // 工程师状态启用
        $map['`me`.`status`'] = 0;

        $info = $this->join('AS `me` LEFT JOIN `message_push` AS `m` ON `me`.`message_id` = `m`.`id`')
            ->join('LEFT JOIN `engineer` AS `e` ON `me`.`engineer_id` = `e`.`id`')
            ->field('`m`.`id`, `m`.`title`, `m`.`content`, `m`.`type`, GROUP_CONCAT(`me`.`engineer_id`) AS `engineer_ids`, GROUP_CONCAT(`e`.`cellphone`) AS `cellphones`, GROUP_CONCAT(`e`.`registration_id`) AS `registration_ids`')
            ->where($map)
            ->group('`m`.`id`')
            ->find();

        $this->messageInfo = is_array($info) ? $info : null;
    }

    static private $pushMessageTip = array(
        array('code' => 0, 'msg' => '推送成功'),
        array('code' => 1, 'msg' => '推送失败'),
        array('code' => 2, 'msg' => '请先添加工程师'),
        array('code' => 3, 'msg' => '消息标题与内容不能同时为空，至少有一个接收消息的工程师'),
        array('code' => 4, 'msg' => '消息推送成功，但工程师消息状态更新失败')
    );

    static public function getPushMessageTip()
    {
        return self::$pushMessageTip;
    }

    /**
     * 推送消息
     *
     * @return array('code' => 代码, 'msg' => '代码信息')
     */
    private function pushMessage()
    {
        // 所有的消息推送提示
        $infos = self::getPushMessageTip();
        $result = 1; // 对应self::$pushMessageTip下标

        if ($info = $this->messageInfo) {

            $type = intval(trim($info['type']));

            switch ($type) {
                case 0:
                    break;

                case 1:
                    $result = $this->pushMessageToSms();

                    break;

                case 2:
                    $result = $this->pushMessageToAPP();
                    break;

                default:
                    break;
            }
        } else {
            $result = 2;
        }

        return $infos[$result];
    }

    private function pushMessageToSms()
    {
        $result = 1; // 对应self::$pushMessageTip下标

        if ($info = $this->messageInfo) {

            $content = $info['content'];
            $title = $info['title'];
            $cellphones = $info['cellphones'];

            if (($title || $content) && $cellphones) {
                $ids = array();

                // 除去空的手机号
                if (is_string($cellphones)) {
                    $tempIds = explode(',', $cellphones);
                    foreach ($tempIds as $value) {
                        if (!empty($value)) $ids[] = $value;
                    }
                } elseif (is_array($cellphones)) {
                    foreach ($cellphones as $value) {
                        if (!empty($value)) $ids[] = $value;
                    }
                }

                if ($ids) {

                    // 真正的消息推送

                    $total = 200; // 一次最多两百个手机号
                    $cellArray = array_chunk($ids, $total, true);

                    $sms = new \Vendor\aliNote\aliNote();

                    $res = false;
                    foreach ($cellArray as $value) {
                        $res = $sms->send(implode(',', $value), array('yuanyin' => $title, 'neirong' => $content),'SMS_34855166');
                    }

                    if ($res) {
                        // 更新消息列表状态为已推送
                        $result = $this->setStatusPushed() ? 0 : 4;
                    }
                }
            } else {
                $result = 3;
            }
        } else {
            $result = 2;
        }

        return $result;
    }

    /**
     * 推送消息到 APP
     *
     * @return bool
     */
    private function pushMessageToAPP()
    {
        $result = 1; // 对应self::$pushMessageTip下标

        if ($info = $this->messageInfo) {

            $content = $info['content'];
            $title = $info['title'];
            $registration_ids = $info['registration_ids'];

            if (($title || $content) && $registration_ids) {
                $ids = array();

                // 除去空的 设备注册号
                if (is_string($registration_ids)) {
                    $tempIds = explode(',', $registration_ids);
                    foreach ($tempIds as $value) {
                        if (!empty($value)) $ids[] = $value;
                    }
                } elseif (is_array($registration_ids)) {
                    foreach ($registration_ids as $value) {
                        if (!empty($value)) $ids[] = $value;
                    }
                }

                if ($ids) {

                    // 真正的消息推送
                    $jpush = new \Vendor\Jpush\Jpush();

                    $msgId = $info['id']; // 信息ID

                    $res = $jpush->pushMessage($title, $content, $msgId, $ids);

                    if ($res) {
                        // 更新消息列表状态为已推送
                        $result = $this->setStatusPushed() ? 0 : 4;
                    }
                }
            } else {
                $result = 3;
            }
        } else {
            $result = 2;
        }

        return $result;
    }

    /**
     * 更新状态为已推送
     *
     * @return bool
     */
    private function setStatusPushed()
    {

        $result = false;

        if (($info = $this->messageInfo) && ($messageId = $info['id']) && ($engineerIds = $info['engineer_ids'])) {

            $where['message_id'] = $messageId;
            $where['engineer_id'] = array('IN', $engineerIds);

            $data['status'] = 1;
            $data['push_time'] = time();

            $result = ($this->where($where)->data($data)->save()) ? true : false;
        }

        return $result;
    }

    /**
     * 根据状态id检查消息是否已推送
     *
     * @param $status 状态id字符串 像"0,0,0,0,0,0,1"
     * @return bool
     */
    static public function checkMessageIsPushed($status)
    {
        $pushed = false;

        $statuses = self::getStatuses();
        $statusKey = array_keys($statuses); // 消息所有的状态 id
        $count = count($statusKey);

        for ($i = 1; $i < $count; $i++) { // 除状态0以外，都是已推送状态
            if (strstr(strval($statusKey[$i]), $status)) {
                $pushed = true;
                break;
            }
        }

        return $pushed;
    }

}