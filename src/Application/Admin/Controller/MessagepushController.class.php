<?php

namespace Admin\Controller;
use Admin\Model\MessagePushModel;
use Admin\Model\MessageEngineerModel;
use Think\Model;

/**
 * 工程师消息推送
 */
class MessagepushController extends BaseController
{

    public function index()
    {
        // 消息类型
        $types = MessagePushModel::getTypes();
        $this->assign('types', $types);

        // 组织
        $organizations = M('organization')->field('id, name')->select();
        $this->assign('organizations', $organizations);

        $this->display();
    }

    public function rows()
    {
        $map = array();

        if ($keyword = trim(I('request.keyword'))) {
            $map['`m`.`title`'] = array('LIKE', '%'.$keyword.'%');
            $map['`m`.`content`'] = array('LIKE', '%'.$keyword.'%');
            $map['_logic'] = 'OR';
        }

        $model = M('MessagePush');
        $count = $model->count();

        $list = $model->join('AS `m` LEFT JOIN `message_engineer` AS `me` ON `m`.`id` = `me`.`message_id`')
            ->join('LEFT JOIN `engineer` AS `e` ON `e`.`id` = `me`.`engineer_id`')
            ->field('`m`.`id`, `m`.`title`, `m`.`content`, `m`.`type`, `m`.`time`, GROUP_CONCAT(`e`.`name`) AS `engineers`, GROUP_CONCAT(`me`.`status`) AS `statuses`')
            ->limit($this->page())
            ->where($map)
            ->order('`id` DESC')
            ->group('`m`.`id`')
            ->select();

        // 消息推送类型
        $types = MessagePushModel::getTypes();

        foreach ($list as &$value) {
            $value['pushed'] = MessageEngineerModel::checkMessageIsPushed($value['statuses']); // 消息是否已推送
            unset($value['statuses']);
            $value['type'] = $types[$value['type']];
            $value['time'] = date("Y-m-d H:i:s");
        }

        $rst['total'] = $count;
        $rst['rows'] = $list;
        $this->ajaxReturn($rst);
    }

    public function edit()
    {
        $id = intval(trim(I('request.id')));
        $rst = array();
        if ($id > 0) {
            $model = M('MessagePush');
            $rst = $model->join('AS `m` LEFT JOIN `message_engineer` AS `me` ON `m`.`id` = `me`.`message_id`')
                ->join('LEFT JOIN `engineer` AS `e` ON `e`.`id` = `me`.`engineer_id`')
                ->field('`m`.`id`, `m`.`title`, `m`.`content`, `m`.`type`, `m`.`time`, `e`.`organization_id`, GROUP_CONCAT(`e`.`id`) AS `engineer_ids`, GROUP_CONCAT(`e`.`name`) AS `engineer_names`')
                ->where(array('`m`.`id`' => $id))
                ->group('`m`.`id`')
                ->find();
        }

        $this->ajaxReturn($rst);
    }

    /**
     * 消息详情
     *
     * @return void
     */
    public function detail()
    {
        $id = intval(trim(I('request.id')));
        $model = D('MessageEngineer');
        $rst = $model->getListBy($id, $this->page());

        $this->ajaxReturn($rst);
    }

    /**
     * 根据组织id得到工程师列表
     *
     * @return void
     */
    public function engineerList()
    {
        $model = M('Engineer');
        $post = I('request.');
        $map['status'] = 1;

        if ($oid = intval(trim($post['organization_id']))) {
            $map['organization_id'] = $oid;
        }

        $list = $model->field('`id`, `name`, `organization_id`')->where($map)->select();
        $total = $model->where($map)->count();

        $rst['total'] = $total;
        $rst['rows'] = $list;
        $this->ajaxReturn($rst);
    }

    /**
     * 添加新消息
     *
     * @return json {'res':bool, 'msg':string}
     */
    public function add()
    {
        $post = I('request.');
        $data = array(
            'id' => intval(trim($post['id'])),
            'title' => trim($post['title']),
            'content' => trim($post['content']),
            'type' => intval(trim($post['type'])),
            'eids' => $post['eids'] // 工程师id数组
        );

        $messageModel = D('MessagePush');

        if ($data['id'] > 0) { // 修改
            $res = $messageModel->updateMessage($data);
        } else {    // 添加
            $res = $messageModel->addMessage($data);
        }

        $this->ajaxReturn($res);
    }

    /**
     * 推送
     *
     * @return void
     */
    public function push()
    {
        $id = intval(trim(I('request.id'))); // 消息ID

        if ($id > 0) {

            $model = D('MessageEngineer');
            $res = $model->pushMessageBy($id);

            $this->ajaxReturn($res);
        }
    }
}